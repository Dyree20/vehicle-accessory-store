# Vehicle Accessory Store

A PHP-based e-commerce website for vehicle accessories.

## XAMPP Setup Instructions

1. **Start XAMPP Services**
   - Open XAMPP Control Panel
   - Start Apache and MySQL services

2. **Create Database**
   - Open your web browser and go to: `http://localhost/phpmyadmin`
   - Click "New" to create a new database
   - Enter database name: `vehicle_accessory_store`
   - Click "Create"

3. **Import Database**
   - Select the `vehicle_accessory_store` database
   - Click "Import" tab
   - Choose the `database.sql` file from this project
   - Click "Go" to import

4. **Project Setup**
   - Place the project files in: `C:\xampp\htdocs\vehicle-accessory-store`
   - Make sure the `uploads` directory is writable:
     - Right-click on the `uploads` folder
     - Properties → Security → Edit
     - Add "Everyone" with "Full control" permissions

5. **Access the Website**
   - Open your web browser
   - Go to: `http://localhost/vehicle-accessory-store`

## Default Login
- Username: admin@example.com
- Password: admin123

## Features
- User registration and login
- Product management
- Shopping cart
- Order processing
- User dashboard
- Category management
- Search functionality

## Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache web server
- mod_rewrite enabled

## Security
- Password hashing
- SQL injection prevention
- XSS prevention
- File upload validation
- Secure headers 