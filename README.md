SCHEDULR — COURSE PRE-SELECTION AND AUTO-REGISTRATION SYSTEM
Documentation File
================================================================================

Project Title  : Design and Evaluation of a Course Pre-Selection and
                 Auto-Registration System
System Name    : Schedulr
Author         : Yenma Abambire Bawa
Student ID     : 29692026
Programme      : B.Sc. Computer Science
Institution    : Ashesi University
Year           : 2026
Supervisor     : Dr. Sampson Dankyi Asare

--------------------------------------------------------------------------------
QUICK LINKS
--------------------------------------------------------------------------------

Live Application  : https://schedulr-production.up.railway.app/
GitHub Repository : https://github.com/YenmaAbambireBawa/Schedulr

--------------------------------------------------------------------------------
WHAT IS SCHEDULR?
--------------------------------------------------------------------------------

Schedulr is a PHP and MariaDB web application that redesigns the course
registration experience at Ashesi University. Instead of competing in a
real-time rush when the CAMU registration window opens, students pre-select
up to three ranked timetable combinations in advance. When registration
begins, the system automatically checks each option through a gate-based
algorithm (prerequisite check → clash detection → seat availability) and
registers the highest-ranked feasible option on the student's behalf.

The live prototype is deployed and accessible at:
  https://schedulr-production.up.railway.app/

All source code is available at:
  https://github.com/YenmaAbambireBawa/Schedulr

--------------------------------------------------------------------------------
TECHNOLOGY STACK
--------------------------------------------------------------------------------

  Backend        : PHP 8.0
  Database       : MariaDB 10.4
  Frontend       : HTML5, CSS3, Vanilla JavaScript
  Email          : PHPMailer (Gmail SMTP, STARTTLS port 587)
  Local server   : XAMPP (Apache 2.4 + PHP 8.0 + MariaDB 10.4)
  Production     : Railway PaaS (auto-deploy from GitHub)
  Security       : bcrypt (passwords), AES-256-CBC (credentials), PDO

--------------------------------------------------------------------------------
HOW TO COMPILE / INSTALL / DEPLOY
--------------------------------------------------------------------------------

There is no compilation step. PHP is an interpreted language. You run the
application by placing the files on a web server with PHP and MariaDB.

--- OPTION A: LOCAL DEVELOPMENT (XAMPP) ---

Step 1. Install XAMPP
  Download from https://www.apachefriends.org/
  This gives you Apache, PHP 8.0, and MariaDB in one package.

Step 2. Clone the repository
  git clone https://github.com/YenmaAbambireBawa/Schedulr.git

Step 3. Move files into htdocs
  Windows : C:\xampp\htdocs\Schedulr
  macOS   : /Applications/XAMPP/htdocs/Schedulr
  Linux   : /opt/lampp/htdocs/Schedulr

Step 4. Start XAMPP
  Open the XAMPP Control Panel and start Apache and MySQL.

Step 5. Create the database
  a. Open http://localhost/phpmyadmin in your browser
  b. Create a new database named: schedulr_db
  c. Select schedulr_db, click Import, upload database/setup.sql
  d. Click Go

  OR via command line:
    mysql -u root -p
    CREATE DATABASE schedulr_db;
    USE schedulr_db;
    SOURCE /path/to/Schedulr/database/setup.sql;

Step 6. Configure environment variables
  Create a file named .env in the project root with the following values:

    DB_HOST=localhost
    DB_NAME=schedulr_db
    DB_USER=root
    DB_PASS=

    ENCRYPTION_KEY=<32 random characters>

    MAIL_HOST=smtp.gmail.com
    MAIL_PORT=587
    MAIL_USERNAME=<your Gmail address>
    MAIL_PASSWORD=<your Gmail App Password>
    MAIL_FROM=<your Gmail address>
    MAIL_FROM_NAME=Schedulr

  To generate an ENCRYPTION_KEY:
    openssl rand -hex 16

  To generate a Gmail App Password:
    Go to https://myaccount.google.com/apppasswords
    Select "Mail" and your device, then copy the 16-character password.

Step 7. Run the application
  Open http://localhost/Schedulr in your browser.

--- OPTION B: PRODUCTION DEPLOYMENT (RAILWAY) ---

Step 1. Push code to GitHub
  git add .
  git commit -m "Deploy"
  git push origin main

Step 2. Create a Railway project
  Go to https://railway.app/
  Click New Project -> Deploy from GitHub repo
  Select YenmaAbambireBawa/Schedulr

Step 3. Add a database
  Inside your Railway project, click + New -> Database -> MySQL
  Railway will automatically inject database connection variables.

Step 4. Set environment variables in Railway
  Go to your service -> Variables and add:
    ENCRYPTION_KEY=<32 random characters>
    MAIL_HOST=smtp.gmail.com
    MAIL_PORT=587
    MAIL_USERNAME=<your Gmail>
    MAIL_PASSWORD=<your App Password>
    MAIL_FROM=<your Gmail>
    MAIL_FROM_NAME=Schedulr

Step 5. Import the database schema
  Connect to your Railway database using the provided connection string:
    mysql -h <HOST> -P <PORT> -u <USER> -p <DATABASE> < database/setup.sql

Step 6. Done
  Railway auto-deploys on every push to main.
  The live instance is at: https://schedulr-production.up.railway.app/

--------------------------------------------------------------------------------
PROJECT STRUCTURE
--------------------------------------------------------------------------------

Schedulr/
  index.html                    Public landing page
  about.html                    About page
  schedulr-login.html           Login and registration UI

  student/
    questionnaire.php           Onboarding form (programme, GPA, completed courses)
    dashboard.php               Pre-selection dashboard — core student UI
    registration-pending.php    Status page (polls server every 10 seconds)
    registration-success.php    Confirmation page
    camu-simulator.php          Five-step CAMU bot pipeline

  api/
    login.php                   POST: authenticate user
    register.php                POST: create account
    submit-registration.php     POST: save ranked options, encrypt, send email
    verify-email.php            GET: validate token from email link
    registration-status.php     GET: return registration status as JSON
    resend-verification.php     POST: resend verification email (rate limited)
    save-questionnaire.php      POST: save onboarding profile
    admin.php                   Multi-action CRUD endpoint (admin only)

  middleware/
    Auth.php                    Role-based access, sessions, bcrypt

  config/
    database.php                PDO database connection
    email.php                   PHPMailer SMTP configuration

  models/
    User.php                    User model

  helpers/
    Mailer.php                  PHPMailer wrapper

  database/
    setup.sql                   Full schema + seed data (run this first)

  user_data/
    registrations.json          Dummy-mode registration store (flat file)

  vendor/                       Composer dependencies (PHPMailer)

--------------------------------------------------------------------------------
DEFAULT ADMIN ACCESS
--------------------------------------------------------------------------------

After importing setup.sql, create an admin account by:

  1. Registering normally at /schedulr-login.html
  2. Opening phpMyAdmin (local) or connecting to the Railway database
  3. In the users table, find your row and change role from 'student' to 'admin'
  4. Log in at /admin and verify access

--------------------------------------------------------------------------------
KEY API ENDPOINTS
--------------------------------------------------------------------------------

  POST  api/login.php                Authenticate and create session
  POST  api/register.php             Create student account
  POST  api/submit-registration.php  Save ranked options, encrypt, send email
  GET   api/verify-email.php         Validate email verification token
  GET   api/registration-status.php  Poll registration status (JSON)
  POST  api/resend-verification.php  Resend verification (2-minute rate limit)
  POST  api/save-questionnaire.php   Save onboarding profile
  *     api/admin.php?action=*       Admin CRUD (9 actions, admin role required)

--------------------------------------------------------------------------------
DATABASE TABLES (schedulr_db)
--------------------------------------------------------------------------------

  users                     User accounts and roles
  courses                   64 courses across 7 departments
  departments               7 departments
  course_sections           109 course sections with time slots and capacity
  time_slots                6 daily time slots
  course_prerequisites      52 prerequisite relationships
  course_registrations      Student registrations with ranked JSON options
  student_questionnaires    Onboarding profiles
  sessions                  PHP session persistence
  login_attempts            Brute-force audit log
  password_resets           Password reset tokens

  Views:
    v_courses_with_dept           Courses joined with department names
    v_course_prerequisites        Human-readable prerequisite chains

--------------------------------------------------------------------------------
SECURITY NOTES
--------------------------------------------------------------------------------

  - Passwords: bcrypt via password_hash() / password_verify()
  - myCAMU credentials: AES-256-CBC with random IV per record
  - ENCRYPTION_KEY: must be stored as environment variable, never hardcoded
  - All SQL queries: PDO prepared statements (prevents SQL injection)
  - Sessions: regenerated on login (prevents session fixation)
  - Cookies: httponly and secure flags set on remember-me tokens
  - Admin routes: rejected server-side for student sessions
  - Transport: HTTPS (Railway), STARTTLS on SMTP port 587

--------------------------------------------------------------------------------
KNOWN LIMITATIONS
--------------------------------------------------------------------------------

  1. No live CAMU integration — the simulator runs in dummy mode only.
     Real integration would require access to CAMU's backend API.

  2. Partial course catalogue — covers CS and Africana Studies only.
     Other departments were not included due to data access constraints.

  3. Simulated time slots — section times are not the official Ashesi
     timetable, which was not available during development.

  4. Fairness algorithm not implemented — the need-based seat allocation
     model is fully specified in Chapter 4 of the thesis but requires
     three institutional datasets not available during development:
     graduation audit data, registration denial history, and credit
     load minimums by year.

  5. Flat file / database inconsistency — questionnaire data exists in
     both user_data/ (flat file cache) and the student_questionnaires
     table. These should be unified in a production deployment.

--------------------------------------------------------------------------------
FURTHER DOCUMENTATION
--------------------------------------------------------------------------------

  Full thesis:
    Design and Evaluation of a Course Pre-Selection and Auto-Registration
    System — Yenma Abambire Bawa, Ashesi University, 2026

  README.md (full technical documentation):
    https://github.com/YenmaAbambireBawa/Schedulr/blob/main/README.md

  Live application:
    https://schedulr-production.up.railway.app/

  Source code:
    https://github.com/YenmaAbambireBawa/Schedulr

--------------------------------------------------------------------------------
CONTACT
--------------------------------------------------------------------------------

  Author     : Yenma Abambire Bawa
  Institution: Ashesi University, Department of Computer Science
  Supervisor : Dr. Sampson Dankyi Asare
  Year       : 2026

END OF DOCUMENTATION
================================================================================
