# Letterhead Inventory Management System

A comprehensive letterhead inventory management system built with Laravel and Filament, designed to manage letterhead inventory, templates, approval workflows, and print tracking.

## Features

### 🏪 Inventory Management
- **Letterhead Inventory**: Track letterhead stock levels, minimum levels, and suppliers
- **Stock Monitoring**: Automatic low stock and out-of-stock alerts
- **Cost Tracking**: Monitor cost per unit and supplier information
- **Restock Management**: Track last restocked dates and quantities

### 📄 Template Management
- **Template Creation**: Create and manage letterhead templates with rich text editor
- **File Uploads**: Support for PDF and image template files
- **Version Control**: Track template versions and changes
- **Template Status**: Draft, pending approval, approved, and rejected states

### ✅ Approval Workflow
- **Template Approval**: Multi-level approval process for templates
- **Approval Tracking**: Track who approved/rejected templates and when
- **Comments System**: Add comments and feedback during approval process
- **Status Management**: Visual status indicators for approval states

### 🖨️ Print Management
- **Print Requests**: Create print requests with multiple templates
- **Serial Number Tracking**: Track letterhead serial number ranges
- **Print Results**: Record successful and wasted prints
- **Wastage Tracking**: Monitor and report print wastage

### 📸 Scan Management
- **Scan Upload**: Upload scans of successful and wasted letterheads
- **File Management**: Organize and categorize scan files
- **Review System**: Manager review of uploaded scans
- **Quality Control**: Track print quality through scan reviews

### 📊 Dashboard & Analytics
- **Inventory Statistics**: Real-time inventory levels and alerts
- **Print Statistics**: Track print volumes and success rates
- **Wastage Reports**: Monitor and analyze print wastage
- **Template Usage**: Track most used templates and patterns

## Installation

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js and NPM
- MySQL/PostgreSQL database

### Setup Instructions

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd letterhead-app
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database configuration**
   Update your `.env` file with database credentials:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=letterhead_app
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

5. **Run migrations and seeders**
   ```bash
   php artisan migrate
   php artisan db:seed --class=UserSeeder
   ```

6. **Build assets**
   ```bash
   npm run build
   ```

7. **Start the application**
   ```bash
   php artisan serve
   ```

## Default Users

The system comes with three default users:

- **Admin User**: admin@letterhead.com / password
- **Manager User**: manager@letterhead.com / password  
- **Staff User**: staff@letterhead.com / password

## Usage Guide

### 1. Inventory Management

#### Adding Letterhead Inventory
1. Navigate to "Letterhead Inventory" in the admin panel
2. Click "Create" to add new inventory items
3. Fill in the required information:
   - Name and description
   - Current quantity and minimum level
   - Cost per unit and supplier
   - Last restocked date

#### Monitoring Stock Levels
- The dashboard shows real-time inventory statistics
- Low stock items are highlighted with warning indicators
- Out-of-stock items are marked with danger indicators

### 2. Template Management

#### Creating Templates
1. Go to "Letterhead Templates"
2. Click "Create" to add a new template
3. Fill in template details:
   - Name and description
   - Rich text content
   - Upload template files (PDF/images)
   - Set initial status

#### Template Approval Process
1. Submit templates for approval
2. Designated approvers review templates
3. Approve or reject with comments
4. Approved templates become available for printing

### 3. Print Requests

#### Creating Print Requests
1. Use the dashboard widget to create print requests
2. Select approved templates
3. Specify quantities and serial number ranges
4. Add notes and submit the request

#### Managing Print Requests
1. View all print requests in the admin panel
2. Track request status (pending, approved, printing, completed)
3. Monitor print progress and results

### 4. Print Results & Wastage Tracking

#### Recording Print Results
1. After printing, record the results
2. Specify successful and wasted print counts
3. Upload scans of printed letterheads
4. Add wastage reasons and descriptions

#### Scan Management
1. Upload scans of successful and wasted prints
2. Categorize scans by type (successful/wasted)
3. Add descriptions and metadata
4. Submit for manager review

### 5. Review System

#### Manager Review Process
1. Managers review uploaded scans
2. Approve or reject scan quality
3. Add comments and feedback
4. Track review status and history

## Database Schema

### Core Tables
- `letterhead_inventory`: Inventory items and stock levels
- `letterhead_templates`: Template definitions and content
- `template_approvals`: Template approval workflow
- `print_requests`: Print request records
- `print_request_items`: Individual items in print requests
- `print_results`: Print execution results
- `letterhead_scans`: Uploaded scan files
- `scan_reviews`: Manager review records

### Key Relationships
- Templates belong to users (creators/approvers)
- Print requests contain multiple items
- Print results link to requests and templates
- Scans belong to print results
- Reviews belong to scans

## API Endpoints

The system provides RESTful API endpoints for:
- Inventory management
- Template operations
- Print request handling
- Scan upload and management
- Review workflows

## Security Features

- **User Authentication**: Secure login system
- **Role-based Access**: Different permission levels
- **File Upload Security**: Secure file handling
- **Data Validation**: Comprehensive input validation
- **CSRF Protection**: Cross-site request forgery protection

## Customization

### Adding New Fields
1. Create database migrations for new fields
2. Update model fillable arrays
3. Modify Filament form schemas
4. Update table columns

### Custom Workflows
1. Extend approval workflows
2. Add custom status transitions
3. Implement notification systems
4. Create custom reports

## Troubleshooting

### Common Issues

1. **File Upload Errors**
   - Check file permissions
   - Verify upload size limits
   - Ensure proper MIME type validation

2. **Database Connection Issues**
   - Verify database credentials
   - Check database server status
   - Run migration commands

3. **Permission Errors**
   - Check file system permissions
   - Verify user roles and permissions
   - Review access control settings

### Support

For technical support and questions:
- Check the Laravel documentation
- Review Filament documentation
- Contact the development team

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests for new functionality
5. Submit a pull request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Changelog

### Version 1.0.0
- Initial release
- Basic inventory management
- Template creation and approval
- Print request system
- Scan upload and review
- Dashboard with statistics