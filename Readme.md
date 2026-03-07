# Schedulr - Automated Course Registration System

## 🚀 Complete Backend Authentication System

This is a complete, production-ready authentication system for the Schedulr application with secure password hashing, session management, and role-based access control.

## 📋 Table of Contents
- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [File Structure](#file-structure)
- [Database Setup](#database-setup)
- [Configuration](#configuration)
- [API Endpoints](#api-endpoints)
- [Usage Examples](#usage-examples)
- [Security Features](#security-features)
- [Testing](#testing)

## ✨ Features

### Authentication
- ✅ Secure user registration with password hashing (bcrypt)
- ✅ User login with session management
- ✅ "Remember Me" functionality with secure tokens
- ✅ Role-based access control (Student/Admin)
- ✅ Protected routes with authentication middleware
- ✅ Session timeout (30 minutes of inactivity)
- ✅ Secure logout with session destruction

### Password Security
- ✅ Password strength validation (8+ chars, uppercase, lowercase, numbers, special chars)
- ✅ Bcrypt hashing with cost factor 10
- ✅ Password confirmation validation

### User Management
- ✅ User profile with full name, email, student ID
- ✅ Email uniqueness validation
- ✅ Active/inactive account status
- ✅ Last login tracking
- ✅ Account creation timestamp

## 🛠️ Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher / MariaDB 10.2+
- Apache/Nginx web server
- PDO PHP extension
- OpenSSL PHP extension

## 📦 Installation

### Step 1: Clone/Download Files

Place all files in your web server directory:

```
/var/www/html/schedulr/  (Linux)
C:/xampp/htdocs/schedulr/  (Windows)
```

### Step 2: Set Up Database

1. Open phpMyAdmin or MySQL command line
2. Run the SQL script from `database/setup.sql`:

```bash
mysql -u root -p < database/setup.sql
```

Or import via phpMyAdmin:
- Go to phpMyAdmin
- Click "Import"
- Select `database/setup.sql`
- Click "Go"

### Step 3: Configure Database Connection

Edit `config/database.php` with your credentials:

```php
private $host = 'localhost';      // Your database host
private $db_name = 'schedulr_db';  // Database name
private $username = 'root';        // Your MySQL username
private $password = '';            // Your MySQL password
```

### Step 4: Set File Permissions (Linux/Mac)

```bash
chmod 755 api/
chmod 644 api/*.php
chmod 755 config/
chmod 644 config/*.php
chmod 755 models/
chmod 644 models/*.php
chmod 755 middleware/
chmod 644 middleware/*.php
```

### Step 5: Configure Web Server

#### Apache (.htaccess)

Create `.htaccess` in root directory:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /

    # Force HTTPS (production only)
    # RewriteCond %{HTTPS} off
    # RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

    # Prevent access to config and database directories
    RewriteRule ^(config|database)/ - [F,L]
</IfModule>

# Security headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
</IfModule>
```

#### Nginx Configuration

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/html/schedulr;
    index index.html index.php;

    location / {
        try_files $uri $uri/ /index.html;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    # Deny access to config and database directories
    location ~ ^/(config|database)/ {
        deny all;
        return 403;
    }

    # Security headers
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
}
```

## 📁 File Structure

```
schedulr/
├── index.html                 # Login/Registration page
├── api/
│   ├── login.php             # Login endpoint
│   ├── register.php          # Registration endpoint
│   └── logout.php            # Logout endpoint
├── config/
│   └── database.php          # Database configuration
├── models/
│   └── User.php              # User model with authentication logic
├── middleware/
│   └── Auth.php              # Authentication middleware
├── student/
│   └── dashboard.php         # Student dashboard (protected)
├── admin/
│   └── dashboard.php         # Admin dashboard (protected)
├── database/
│   └── setup.sql             # Database setup script
└── README.md                 # This file
```

## 💾 Database Setup

### Main Tables

#### users
```sql
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- full_name (VARCHAR 255)
- email (VARCHAR 255, UNIQUE)
- student_id (VARCHAR 50, NULL for admins)
- password_hash (VARCHAR 255)
- role (ENUM: 'student', 'admin')
- remember_token (VARCHAR 100)
- email_verified_at (TIMESTAMP)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
- last_login (TIMESTAMP)
- is_active (BOOLEAN)
```

#### Optional Tables
- `password_resets` - For password reset functionality
- `sessions` - For database session storage
- `login_attempts` - For tracking failed login attempts

### Sample Users

The setup script includes sample users:

**Student Account:**
- Email: `john.doe@university.edu`
- Password: `Student123!`
- Role: student

**Admin Account:**
- Email: `admin@university.edu`
- Password: `Admin123!`
- Role: admin

## ⚙️ Configuration

### Update API Base URL

In `index.html`, update the API base URL:

```javascript
const API_BASE_URL = '/api'; // Update this to your API path
// For example: 'http://localhost/schedulr/api'
```

### Session Configuration

Edit `php.ini` for production:

```ini
session.cookie_httponly = 1
session.cookie_secure = 1  ; Enable for HTTPS
session.cookie_samesite = "Strict"
session.gc_maxlifetime = 1800  ; 30 minutes
```

## 🔌 API Endpoints

### 1. Register User
**POST** `/api/register.php`

Request Body:
```json
{
  "full_name": "John Doe",
  "email": "john@university.edu",
  "student_id": "STU123",
  "password": "SecurePass123!",
  "confirm_password": "SecurePass123!",
  "role": "student",
  "terms_agreed": true
}
```

Success Response (201):
```json
{
  "success": true,
  "message": "Account created successfully",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@university.edu",
      "role": "student",
      "student_id": "STU123"
    },
    "redirect_url": "/student/dashboard.php",
    "session_id": "abc123..."
  }
}
```

### 2. Login User
**POST** `/api/login.php`

Request Body:
```json
{
  "email": "john@university.edu",
  "password": "SecurePass123!",
  "role": "student",
  "remember_me": true
}
```

Success Response (200):
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@university.edu",
      "role": "student",
      "student_id": "STU123"
    },
    "redirect_url": "/student/dashboard.php",
    "session_id": "abc123...",
    "remember_token": "xyz789..."
  }
}
```

### 3. Logout User
**GET/POST** `/api/logout.php`

Success Response (200):
```json
{
  "success": true,
  "message": "Logged out successfully",
  "data": {
    "redirect_url": "/index.html"
  }
}
```

## 📝 Usage Examples

### Protecting Pages with Authentication

```php
<?php
require_once __DIR__ . '/../middleware/Auth.php';

// Require any authenticated user
Auth::requireAuth();

// Require specific role
Auth::requireStudent();  // Only students
Auth::requireAdmin();    // Only admins

// Get current user data
$user = Auth::user();
echo "Welcome, " . $user['name'];

// Check authentication status
if (Auth::check()) {
    echo "User is logged in";
}

// Check specific role
if (Auth::isAdmin()) {
    echo "User is an admin";
}
?>
```

### JavaScript Fetch Examples

```javascript
// Login
async function login(email, password) {
  const response = await fetch('/api/login.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ email, password })
  });
  return await response.json();
}

// Register
async function register(userData) {
  const response = await fetch('/api/register.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(userData)
  });
  return await response.json();
}

// Logout
async function logout() {
  const response = await fetch('/api/logout.php');
  return await response.json();
}
```

## 🔒 Security Features

### Password Security
- ✅ Bcrypt hashing with cost factor 10
- ✅ Minimum 8 characters
- ✅ Requires uppercase, lowercase, numbers, special characters
- ✅ Password confirmation validation

### Session Security
- ✅ HttpOnly cookies
- ✅ Secure cookies (HTTPS)
- ✅ SameSite cookie attribute
- ✅ Session timeout (30 minutes)
- ✅ Session regeneration on login

### Input Validation
- ✅ Email format validation
- ✅ SQL injection prevention (prepared statements)
- ✅ XSS prevention (input sanitization)
- ✅ CSRF token support

### Access Control
- ✅ Role-based authorization
- ✅ Protected routes
- ✅ Account status checking (active/inactive)

## 🧪 Testing

### Test the Installation

1. **Access the login page:**
   - Navigate to `http://localhost/schedulr/index.html`

2. **Test student registration:**
   - Click "Sign Up"
   - Select "Student" role
   - Fill in the form with valid data
   - Submit

3. **Test admin registration:**
   - Select "Admin" role
   - Fill in the form (no student ID needed)
   - Submit

4. **Test login:**
   - Use sample credentials or newly created account
   - Try "Remember Me" option
   - Verify redirect to correct dashboard

5. **Test protected routes:**
   - Try accessing `/student/dashboard.php` without logging in
   - Should redirect to login page

6. **Test logout:**
   - Click logout button
   - Verify session is destroyed
   - Try accessing dashboard again

### Manual Database Testing

```sql
-- View all users
SELECT id, full_name, email, role, created_at FROM users;

-- Check password hash
SELECT email, password_hash FROM users WHERE email = 'test@university.edu';

-- View recent logins
SELECT email, last_login FROM users WHERE last_login IS NOT NULL ORDER BY last_login DESC;
```

## 🐛 Troubleshooting

### Common Issues

**1. Database connection failed**
- Check credentials in `config/database.php`
- Ensure MySQL service is running
- Verify database exists

**2. Headers already sent error**
- Check for whitespace before `<?php` tags
- Ensure no output before `session_start()`

**3. 404 errors on API calls**
- Verify `.htaccess` is working (Apache)
- Check Nginx configuration
- Ensure mod_rewrite is enabled

**4. Session not persisting**
- Check PHP session configuration
- Verify session directory permissions
- Check for session cookie settings

**5. Can't login with sample accounts**
- Verify sample data was inserted
- Check password hash generation
- Try resetting password

## 📞 Support

For issues or questions:
1. Check the troubleshooting section
2. Review error logs (`error_log()` in PHP files)
3. Check browser console for frontend errors
4. Verify database tables exist and have correct structure

## 📄 License

This project is for educational purposes.

## 🙏 Credits

Created with ❤️ for Schedulr - Automated Course Registration System

# Student Questionnaire - Modified Version

## Overview
This is a modified student questionnaire system based on the provided pseudocode requirements. It has been streamlined to focus on Computer Science students and implements a simple file-based data storage system.

## Key Modifications (As Per Requirements)

### 1. **Program Selection - Computer Science Only**
- The program dropdown now only shows "Computer Science" as an option
- This aligns with the requirement: `SET available_programs = ["Computer Science"]`

### 2. **Current Year Display**
- The system displays the current academic year using PHP's `date('Y')` function
- Shows as an info box: "📅 Academic Year: 2026"
- This fulfills: `SET current_year = SYSTEM.YEAR`

### 3. **Black Dropdown Font Color**
- All dropdown options now have black text color on white background
- CSS implemented: `.form-select option { background: #ffffff; color: #000000; }`
- This addresses: `SET dropdown_font_color = BLACK`

### 4. **Current GPA Field**
- GPA field is now **required** (marked with red asterisk)
- Validates between 0.00 and 4.00
- Step increment of 0.01 for precise entry
- This implements: `INPUT current_GPA`

### 5. **Completed Courses Selection**
- Comprehensive list of all Computer Science courses (26 courses total)
- Organized by year level (Years 1-4)
- Includes core CS courses, math requirements, and electives
- Displayed in a scrollable checkbox grid
- Courses include:
  - **Year 1**: CS101, CS102, MATH101, MATH102, ENG101
  - **Year 2**: CS201-204, MATH201-202
  - **Year 3**: CS301-306
  - **Year 4**: CS401-406
- This fulfills: `INPUT completed_courses // select from predefined CS course list`

### 6. **Data Saving & Dashboard Redirect**
- User data is saved to `/user_data/` folder
- Filename format: `{UserID}_{FullName}.txt`
- Example: `12345_John_Doe.txt`
- File contains:
  - Personal information (ID, name, email)
  - Academic information (program, year, GPA)
  - Complete list of completed courses with full names
  - Timestamp of profile creation
- After successful save, redirects to dashboard
- This implements the pseudocode:
  ```
  SAVE student_profile (name, GPA, completed_courses, year, program)
  REDIRECT to DASHBOARD
  ```

## File Structure

```
/schedulr/
├── student/
│   └── student-questionnaire.php    # Main questionnaire form
├── api/
│   └── save-questionnaire.php       # API endpoint for saving data
└── user_data/                       # Directory for user profile files
    ├── 12345_John_Doe.txt
    ├── 12346_Jane_Smith.txt
    └── ...
```

## User Data File Format

Each user's data is saved in a structured text file with the following format:

```
================================================================================
                     SCHEDULR - STUDENT PROFILE
================================================================================

PERSONAL INFORMATION
-------------------
Student ID      : 12345
Full Name       : John Doe
Email           : john.doe@example.com
Profile Created : 2026-02-04 14:30:00

ACADEMIC INFORMATION
-------------------
Program/Major   : Computer Science
Current Year    : Year 2
Academic Year   : 2026
Current GPA     : 3.75 / 4.0

COMPLETED COURSES
-------------------
Total Completed : 10 courses

1. CS101 - Introduction to Computer Science
2. CS102 - Programming Fundamentals
3. MATH101 - Calculus I
4. MATH102 - Discrete Mathematics
5. ENG101 - Technical Writing
6. CS201 - Data Structures
7. CS202 - Algorithms
8. CS203 - Computer Organization
9. CS204 - Object-Oriented Programming
10. MATH201 - Linear Algebra

================================================================================
                     END OF PROFILE
================================================================================
```

## Form Validation

The system validates:
1. **Program selection** - Must select Computer Science
2. **Current year** - Must select a year (1-4)
3. **GPA** - Required, must be between 0.00 and 4.00
4. **Completed courses** - Must select at least one course

Error message displayed: "Please complete all required fields"

## Installation & Setup

1. **Place Files**:
   - `student-questionnaire.php` → `/schedulr/student/`
   - `save-questionnaire.php` → `/schedulr/api/`

2. **Create Directory**:
   ```bash
   mkdir -p /schedulr/user_data
   chmod 755 /schedulr/user_data
   ```

3. **Update Paths** (if needed):
   - Ensure middleware path is correct: `__DIR__ . '/../middleware/Auth.php'`
   - Verify dashboard URL: `/schedulr/student/dashboard.php`

## Features

### User Interface
- ✅ Modern, clean design with glassmorphism effects
- ✅ Responsive layout (works on mobile, tablet, desktop)
- ✅ Animated background with floating shapes
- ✅ Real-time form validation
- ✅ Success/error message display
- ✅ Loading state during save operation

### Data Management
- ✅ File-based storage (no database required for this feature)
- ✅ Unique filename per user (prevents overwrites)
- ✅ Human-readable text format
- ✅ Comprehensive course information
- ✅ Timestamp tracking

### Security
- ✅ Authentication required (uses Auth middleware)
- ✅ Input sanitization
- ✅ File path validation
- ✅ JSON validation

## Complete Course List

### Year 1 Foundation Courses
- CS101 - Introduction to Computer Science
- CS102 - Programming Fundamentals
- MATH101 - Calculus I
- MATH102 - Discrete Mathematics
- ENG101 - Technical Writing

### Year 2 Core Courses
- CS201 - Data Structures
- CS202 - Algorithms
- CS203 - Computer Organization
- CS204 - Object-Oriented Programming
- MATH201 - Linear Algebra
- MATH202 - Calculus II

### Year 3 Advanced Courses
- CS301 - Database Systems
- CS302 - Operating Systems
- CS303 - Software Engineering
- CS304 - Web Development
- CS305 - Computer Networks
- CS306 - Theory of Computation

### Year 4 Specialization Courses
- CS401 - Artificial Intelligence
- CS402 - Machine Learning
- CS403 - Cybersecurity
- CS404 - Mobile App Development
- CS405 - Cloud Computing
- CS406 - Capstone Project

## API Response Format

### Success Response
```json
{
  "success": true,
  "message": "Profile saved successfully!",
  "data": {
    "redirect_url": "/schedulr/student/dashboard.php",
    "filename": "12345_John_Doe.txt",
    "filepath": "/path/to/user_data/12345_John_Doe.txt"
  }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Please complete all required fields"
}
```

## Browser Compatibility
- ✅ Chrome/Edge (latest)
- ✅ Firefox (latest)
- ✅ Safari (latest)
- ✅ Mobile browsers

## Notes
- The user_data folder will be created automatically if it doesn't exist
- Existing user files will be overwritten if the student completes the questionnaire again
- The system is designed for simplicity and doesn't require database modifications
- All course codes and names are hardcoded for consistency