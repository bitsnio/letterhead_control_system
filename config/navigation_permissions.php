<?php

return [
    // Use full class names or just class basename
    'LetterheadInventoryResource' => ['admin'],
    'LetterheadTemplatesResource' => ['admin', 'manager'],
    'PrintedLetterheadsResource' => ['admin', 'manager', 'user'],
    'TemplateApprovalsResource' => ['admin', 'sm'],
    'UserResource' => ['admin'],
    
    // Pages (including resource pages like Edit, Create, List)
    'PendingApprovals' => ['sm', 'manager']
];

