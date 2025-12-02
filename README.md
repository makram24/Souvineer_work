# Daily Work Management System

A PHP-based management system for tracking daily work entries including videos, images, worked hours, and notes.

## Features

- **Authentication System**: Secure login/logout functionality
- **Daily Entry Management**: Add/edit entries for each day
- **File Uploads**: Upload multiple images and videos per day
- **Work Hours Tracking**: Record worked hours with decimal precision
- **Notes**: Add detailed notes for each day
- **Calendar View**: Visual calendar showing days with entries
- **Date Navigation**: Easy navigation between different dates
- **SEO Protection**: All pages are noindexed
- **Responsive Design**: Works on desktop and mobile devices

## Installation

1. **Database Setup**:
   - Open phpMyAdmin
   - Import the `database.sql` file to create the database and tables
   
2. **Admin Account Setup**:
   - After importing the database, navigate to `setup.php` in your browser
   - This will create/update the admin user with the correct password hash
   - Default admin credentials:
     - Username: `admin`
     - Password: `admin123`
     - **IMPORTANT**: 
       - Delete `setup.php` after setup for security!
       - Change the password immediately after first login!
   
   **Alternative**: If you can't access setup.php, you can manually reset the password using `reset_password.php`

2. **Configuration**:
   - Edit `config.php` if your database credentials differ from the defaults
   - Default settings:
     - Host: `localhost`
     - User: `root`
     - Password: `` (empty)
     - Database: `work_management`

3. **File Permissions**:
   - Ensure the `uploads/` directory and subdirectories (`images/`, `videos/`) are writable
   - The system will create these directories automatically if they don't exist

4. **Web Server**:
   - Place all files in your web server directory (e.g., `htdocs` or `www`)
   - Ensure PHP 7.4+ is installed with MySQLi extension
   - Apache with mod_rewrite recommended (for .htaccess security)

## File Structure

```
├── config.php          # Database and configuration settings
├── database.sql        # Database schema
├── login.php           # Login page
├── logout.php          # Logout handler
├── index.php           # Main entry management page
├── view.php            # View entries by date
├── upload_handler.php  # File upload processing
├── styles.css          # CSS styling
├── script.js           # JavaScript functionality
├── .htaccess          # Security and server configuration
└── uploads/           # Upload directory (created automatically)
    ├── images/        # Image uploads
    └── videos/        # Video uploads
```

## Usage

1. **Login**: Navigate to `login.php` and enter your credentials
2. **Add Entry**: 
   - Select a date
   - Enter worked hours
   - Add notes
   - Upload images and/or videos
   - Click "Save Entry"
3. **View Entries**: 
   - Click "View Entries" in the header
   - Select a date from the calendar or date picker
   - View all content for that day
4. **Calendar Navigation**: 
   - Days with entries are highlighted in blue
   - Click any day to view its entry

## Security Features

- Password hashing using PHP's `password_hash()`
- Session-based authentication
- SQL injection protection with prepared statements
- File type validation
- File size limits (100MB per file)
- Noindex meta tags on all pages
- .htaccess protection for uploads directory

## Supported File Types

**Images**: JPEG, PNG, GIF, WebP
**Videos**: MP4, WebM, OGG, QuickTime

## Default Admin Account

**Username**: admin
**Password**: admin123

**⚠️ CHANGE THIS PASSWORD IMMEDIATELY AFTER FIRST LOGIN!**

To change the password, you can:
1. Use phpMyAdmin to update the `users` table
2. Or create a password change script

Example SQL to change password:
```sql
UPDATE users SET password = '$2y$10$...' WHERE username = 'admin';
```
(Generate hash using PHP: `password_hash('your_new_password', PASSWORD_DEFAULT)`)

## Requirements

- PHP 7.4 or higher
- MySQL 5.7+ or MariaDB 10.2+
- Apache with mod_rewrite (recommended)
- PHP extensions: mysqli, gd (for image handling)

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## License

This project is provided as-is for personal/internal use.

