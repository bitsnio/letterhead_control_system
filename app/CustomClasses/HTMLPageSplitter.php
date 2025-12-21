<?php

namespace App\CustomClasses;

use DOMDocument;
use DOMNode;
use DOMXPath;

class HTMLPageSplitter
{
    protected $opts;
    protected $contentHeight;
    protected $contentWidth;
    protected $lineHeightMM;
    // ===============================
    // PAGINATION TUNING PARAMETERS
    // ===============================
    /*📐 How to tune (VERY IMPORTANT)
    Symptom	Fix
    Too much empty space	↓ rowPaddingFactor
    Rows split too early	↓ visualBreakWeight
    Text wraps too early	↓ charWidthFactor
    Content overflows	↑ rowPaddingFactor
    Excel rows clipped	↑ visualBreakWeight
    Merged cells broken	↑ rowspanMultiplier*/

    // Text measurement
    protected float $charWidthFactor      = 0.58; // ↓ smaller = more text per line
    protected float $usableWidthFactor    = 0.95; // % of column width considered usable

    // Line safety
    protected float $rowPaddingFactor     = 0.12; // extra line-height per row
    protected float $nestedTablePadding   = 2.0;  // in line-heights

    // Word/Excel behavior
    protected float $visualBreakWeight    = 0.6;  // how strong <br>/<p>/<div> count
    protected float $rowspanMultiplier    = 1.0;  // rowspan exaggeration


    public function splitContent($html, $options = [])
    {
        $this->opts = array_merge([
            'page_width' => 210,
            'page_height' => 297,
            'margin_top' => 20,
            'margin_bottom' => 20,
            'margin_left' => 20,
            'margin_right' => 20,
            'font_size' => 100,
            'font_size_unit' => '%',
            'line_height' => 1.3,
        ], $options);

        // Pre-clean Word/Excel Artifacts
        $html = $this->cleanOfficeHtml($html);

        $this->contentHeight = $this->opts['page_height'] - $this->opts['margin_top'] - $this->opts['margin_bottom'];
        $this->contentWidth = $this->opts['page_width'] - $this->opts['margin_left'] - $this->opts['margin_right'];

        $fontSizeMM = $this->convertFontSizeToMM($this->opts['font_size'], $this->opts['font_size_unit']);
        $this->lineHeightMM = $fontSizeMM * $this->opts['line_height'];

        if (empty(trim($html))) return [''];

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $wrappedHtml = '<div id="root">' . mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8') . '</div>';
        $dom->loadHTML($wrappedHtml, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $root = $dom->getElementById('root');
        $pages = [];
        $currentPageHtml = "";
        $currentHeight = 0;

        $nodes = iterator_to_array($root->childNodes);

        while (count($nodes) > 0) {
            $node = array_shift($nodes);
            if ($node->nodeName === '#text' && empty(trim($node->textContent))) continue;

            $nodeHtml = $dom->saveHTML($node);
            $nodeHeight = $this->estimateNodeHeight($node);

            if (($currentHeight + $nodeHeight) <= $this->contentHeight) {
                $currentPageHtml .= $nodeHtml;
                $currentHeight += $nodeHeight;
            } else {
                if ($node->nodeName === 'table') {
                    $parts = $this->splitTableNode($node, $dom, $this->contentHeight - $currentHeight);

                    $currentPageHtml .= array_shift($parts);
                    $pages[] = $currentPageHtml;

                    $currentPageHtml = "";
                    $currentHeight = 0;

                    foreach (array_reverse($parts) as $part) {
                        $fragment = $dom->createDocumentFragment();
                        @$fragment->appendXML($part);
                        array_unshift($nodes, $fragment);
                    }
                } elseif ($this->hasTableInside($node)) {
                    // Extract children from container (P or DIV) to process separately
                    $innerNodes = iterator_to_array($node->childNodes);
                    foreach (array_reverse($innerNodes) as $inner) {
                        array_unshift($nodes, $inner);
                    }
                } else {
                    if (!empty(trim($currentPageHtml))) $pages[] = $currentPageHtml;
                    $currentPageHtml = $nodeHtml;
                    $currentHeight = $nodeHeight;
                }
            }
        }

        if (!empty(trim($currentPageHtml))) $pages[] = $currentPageHtml;
        return $pages;
    }

    /**
     * CLEAN OFFICE HTML: Removes MS Word/Excel specific tags and styles
     */
    private function cleanOfficeHtml($html)
    {
        // Remove MS Office comments & tags
        $html = preg_replace('/<!--\[if.*?\]-->/is', '', $html);
        $html = preg_replace('/<!--\[endif\]-->/is', '', $html);

        // Remove MSO specific attributes
        $html = preg_replace('/\s*mso-[^:]+:[^;"]+;?/i', '', $html);

        // Remove Word classes
        $html = preg_replace('/class="Mso[^"]*"/i', '', $html);

        // Normalize nbsp
        $html = str_replace(["\u{A0}", "&nbsp;"], ' ', $html);

        // Remove fixed widths/heights (Excel killer)
        $html = preg_replace('/(width|height):\s*\d+(\.\d+)?(pt|px|cm|mm)/i', '', $html);

        // Normalize paragraphs (Word adds margins)
        $html = preg_replace('/<p[^>]*>/', '<p style="margin:0;">', $html);

        // Force table sanity
        $html = preg_replace('/<table/i', '<table style="width:100%;border-collapse:collapse;"', $html);

        return $html;
    }

    private function countVisualLines($html)
    {
        return substr_count(strtolower($html), '<br') +
            substr_count(strtolower($html), '<p') +
            substr_count(strtolower($html), '<div');
    }


    private function hasTableInside($node)
    {
        if ($node->nodeType !== XML_ELEMENT_NODE) return false;
        if ($node->nodeName === 'table') return true;
        foreach ($node->childNodes as $child) {
            if ($this->hasTableInside($child)) return true;
        }
        return false;
    }

    private function splitTableNode($tableNode, $dom, $availableHeight)
    {
        $xpath = new DOMXPath($dom);
        $thead = $xpath->query('.//thead', $tableNode)->item(0);
        $rows = iterator_to_array($xpath->query('.//tr', $tableNode));

        $tableAttr = " style='width:100%; border-collapse: collapse;' border='1'";
        $headerHtml = $thead ? $dom->saveHTML($thead) : "";

        $parts = [];
        $currentRowsHtml = "";
        $currentPartHeight = $thead ? ($this->lineHeightMM * 2) : 0;
        $targetHeight = $availableHeight;

        foreach ($rows as $row) {
            if ($thead && $thead->contains($row)) continue;

            // EXCEL SAFETY: Check for Rowspans. 
            // If a row starts a rowspan that we can't fit, we must move the whole row.
            $rowHeight = $this->estimateRowHeight($row);

            // SAFETY: Row taller than page → isolate it
            if ($rowHeight >= ($this->contentHeight * 0.98)) {
                $parts[] = "<table{$tableAttr}>{$headerHtml}<tbody>" .
                    $dom->saveHTML($row) .
                    "</tbody></table>";
                continue;
            }

            if (($currentPartHeight + $rowHeight) <= $targetHeight) {
                $currentRowsHtml .= $dom->saveHTML($row);
                $currentPartHeight += $rowHeight;
            } else {
                if (!empty($currentRowsHtml)) {
                    $parts[] = "<table{$tableAttr}>{$headerHtml}<tbody>{$currentRowsHtml}</tbody></table>";
                }
                $currentRowsHtml = $dom->saveHTML($row);
                $currentPartHeight = ($thead ? ($this->lineHeightMM * 2) : 0) + $rowHeight;
                $targetHeight = $this->contentHeight;
            }
        }

        if (!empty($currentRowsHtml)) {
            $parts[] = "<table{$tableAttr}>{$headerHtml}<tbody>{$currentRowsHtml}</tbody></table>";
        }

        return $parts;
    }

    private function estimateNodeHeight($node)
    {
        if ($node->nodeType === XML_TEXT_NODE) {
            return $this->estimateTextHeight($node->textContent);
        }

        if ($node->nodeName === 'br') {
            return $this->lineHeightMM;
        }

        if ($node->nodeName === 'table') {
            $h = 0;
            foreach ($node->getElementsByTagName('tr') as $row) {
                $h += $this->estimateRowHeight($row);
            }
            return $h + ($this->lineHeightMM * $this->nestedTablePadding);
        }

        $total = 0;
        foreach ($node->childNodes as $child) {
            $total += $this->estimateNodeHeight($child);
        }

        return max($total, $this->lineHeightMM);
    }


    private function estimateRowHeight($row)
    {
        $maxLines = 1;
        $cells = $row->getElementsByTagName('td');
        $cellCount = max(1, $cells->length);

        foreach ($cells as $cell) {

            $html = $cell->ownerDocument->saveHTML($cell);
            $text = trim(strip_tags($html));
            if ($text === '') continue;

            $breakLines = $this->countVisualLines($html) * $this->visualBreakWeight;

            $fontSizeMM = $this->convertFontSizeToMM(
                $this->opts['font_size'],
                $this->opts['font_size_unit']
            );

            $charWidthMM = $fontSizeMM * $this->charWidthFactor;
            $usableWidth = ($this->contentWidth / $cellCount) * $this->usableWidthFactor;
            $charsPerLine = max(1, floor($usableWidth / $charWidthMM));

            $textLines = ceil(mb_strlen($text) / $charsPerLine);

            $rowspan = max(1, (int)$cell->getAttribute('rowspan'));
            $totalLines = max(1, ($textLines + $breakLines)) * ($rowspan * $this->rowspanMultiplier);

            $maxLines = max($maxLines, $totalLines);
        }

        return ($maxLines * $this->lineHeightMM)
            + ($this->lineHeightMM * $this->rowPaddingFactor);
    }



    private function estimateTextHeight($text)
    {
        $text = trim($text);
        if (empty($text)) return 0;
        $fontSizeMM = $this->convertFontSizeToMM($this->opts['font_size'], $this->opts['font_size_unit']);
        $charWidthMM = $fontSizeMM * 0.52;
        $charsPerLine = max(1, floor($this->contentWidth / $charWidthMM));
        return ceil(mb_strlen($text) / $charsPerLine) * $this->lineHeightMM;
    }

    private function convertFontSizeToMM($fontSize, $unit)
    {
        switch ($unit) {
            case 'pt':
                return $fontSize * 0.352778;
            case 'px':
                return $fontSize * 0.264583;
            case '%':
                return (16 * ($fontSize / 100)) * 0.264583;
            default:
                return $fontSize * 0.352778;
        }
    }
}
