# Schedulr — Course Pre-Selection and Auto-Registration System

> **Live Demo:** [https://schedulr-production.up.railway.app/](https://schedulr-production.up.railway.app/)
> **Repository:** [https://github.com/YenmaAbambireBawa/Schedulr](https://github.com/YenmaAbambireBawa/Schedulr)

---

## Table of Contents

1. [Overview](#overview)
2. [Features](#features)
3. [System Architecture](#system-architecture)
4. [Technology Stack](#technology-stack)
5. [Prerequisites](#prerequisites)
6. [Installation & Local Setup](#installation--local-setup)
7. [Database Setup](#database-setup)
8. [Environment Variables](#environment-variables)
9. [Running the Application](#running-the-application)
10. [Deployment (Railway)](#deployment-railway)
11. [Project Structure](#project-structure)
12. [API Endpoints](#api-endpoints)
13. [Algorithm Overview](#algorithm-overview)
14. [Known Limitations](#known-limitations)
15. [Author](#author)

---

## Overview

**Schedulr** is a PHP and MariaDB web application designed for Ashesi University that reimagines the course registration experience. Instead of competing in a stressful real-time rush when the registration window opens, students pre-select and rank up to **three full timetable combinations** in advance. When registration begins, Schedulr automatically checks each option through a gate-based algorithm and registers the best feasible option on the student's behalf.

This project was designed and evaluated as an undergraduate Computer Science thesis at Ashesi University (2026), supervised by Dr. Sampson Dankyi Asare.

**The problem it solves:**
- Server crashes caused by simultaneous logins
- Timetable conflicts only caught after submission
- Seat allocation based on internet speed rather than academic need
- No backup mechanism when a first-choice course is full

---

## Features

| Feature | Description |
|---|---|
| **Pre-selection dashboard** | Students build up to 3 ranked timetable options before the window opens |
| **Gate-based algorithm** | Prerequisite check → Clash detection → Seat availability, in sequence |
| **Auto-registration** | Highest-ranked feasible option is committed automatically |
| **Email verification** | Secure token-based email confirmation before submission is processed |
| **CAMU simulator** | Five-step bot pipeline simulating myCAMU login and enrolment |
| **Live status polling** | Dashboard polls registration status every 10 seconds |
| **Admin panel** | Full CRUD across users, courses, departments, prerequisites, registrations |
| **Role-based access** | Student and admin roles enforced server-side on every endpoint |
| **Security** | bcrypt passwords, AES-256-CBC credential encryption, PDO prepared statements |
| **Waitlist fallback** | Students placed on waitlist automatically if all three options fail |

---

## System Architecture

```
Browser (Student / Admin)
        │
        ▼
┌─────────────────────────────────────────────┐
│              PHP Page Layer                  │
│  index.html · login · questionnaire ·        │
│  dashboard · reg-pending · reg-success ·     │
│  camu-simulator · admin dashboard            │
└─────────────────────────────────────────────┘
        │
        ▼
┌─────────────────────────────────────────────┐
│         Auth Middleware (Auth.php)           │
│  bcrypt · RBAC · sessions · login_attempts  │
└─────────────────────────────────────────────┘
        │
        ▼
┌─────────────────────────────────────────────┐
│              API / Core Logic                │
│  submit-registration · verify-email ·        │
│  registration-status · admin CRUD           │
│                                             │
│  ┌─────────────────────────────────────┐   │
│  │    Auto-Registration Engine          │   │
│  │  Gate 1: Prerequisite Check O(c·p)  │   │
│  │  Gate 2: Clash Detection  O(c²)     │   │
│  │  Gate 3: Seat Check       O(c)      │   │
│  │  Commit enrolment (transaction)     │   │
│  └─────────────────────────────────────┘   │
└─────────────────────────────────────────────┘
        │
        ▼
┌─────────────────────────────────────────────┐
│         MariaDB 10.4 (schedulr_db)           │
│  11 tables · 2 views · FK constraints        │
└─────────────────────────────────────────────┘
        │
        ▼
┌─────────────────────────────────────────────┐
│     Railway PaaS · Apache 2.4 · PHP 8.0      │
│     GitHub auto-deploy · HTTPS · SMTP TLS    │
└─────────────────────────────────────────────┘
```

---

## Technology Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.0 |
| Database | MariaDB 10.4 |
| Frontend | HTML5, CSS3, JavaScript (vanilla) |
| Email | PHPMailer (Gmail SMTP, STARTTLS port 587) |
| Password hashing | bcrypt (`password_hash` / `password_verify`) |
| Credential encryption | AES-256-CBC with random 16-byte IV per record |
| Local dev server | XAMPP (Apache 2.4 + PHP 8.0 + MariaDB 10.4) |
| Production hosting | Railway (PaaS) |
| Version control | Git / GitHub |

---

## Prerequisites

### Local Development

- [XAMPP](https://www.apachefriends.org/) — includes Apache 2.4, PHP 8.0, MariaDB 10.4, phpMyAdmin
- Git
- A Gmail account with an [App Password](https://myaccount.google.com/apppasswords) enabled (for SMTP) [currently disabled for live production]

### Production

- A [Railway](https://railway.app/) account
- A GitHub account (Railway deploys directly from your repository)

---

## Installation & Local Setup

### Step 1 — Clone the repository

```bash
git clone https://github.com/YenmaAbambireBawa/Schedulr.git
cd Schedulr
```

### Step 2 — Start XAMPP

Open the XAMPP Control Panel and start both **Apache** and **MySQL (MariaDB)**.

### Step 3 — Place files in htdocs

Copy the entire `Schedulr` folder into your XAMPP `htdocs` directory:

- **Windows:** `C:\xampp\htdocs\Schedulr`
- **macOS:** `/Applications/XAMPP/htdocs/Schedulr`
- **Linux:** `/opt/lampp/htdocs/Schedulr`

### Step 4 — Configure environment variables

Create a `.env` file in the project root (or set the values directly in `config/database.php` and `config/email.php` for local dev):

```env
DB_HOST=localhost
DB_NAME=schedulr_db
DB_USER=root
DB_PASS=

ENCRYPTION_KEY=your-32-character-random-key-here

MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-gmail@gmail.com
MAIL_PASSWORD=your-gmail-app-password
MAIL_FROM=your-gmail@gmail.com
MAIL_FROM_NAME=Schedulr
```

> **Important:** `ENCRYPTION_KEY` must be exactly 32 characters. Generate one with:
> ```bash
> openssl rand -hex 16
> ```

---

## Database Setup

### Option A — Using phpMyAdmin (recommended for local dev)

1. Open [http://localhost/phpmyadmin](http://localhost/phpmyadmin) in your browser
2. Click **New** and create a database named `schedulr_db`
3. Select `schedulr_db`, click the **Import** tab
4. Upload `database/setup.sql` from the project root
5. Click **Go**

### Option B — Using the command line

```bash
mysql -u root -p
```

```sql
CREATE DATABASE schedulr_db;
USE schedulr_db;
SOURCE /path/to/Schedulr/database/setup.sql;
```

### What setup.sql creates

- 11 tables: `users`, `courses`, `departments`, `course_sections`, `time_slots`, `course_prerequisites`, `course_registrations`, `student_questionnaires`, `sessions`, `login_attempts`, `password_resets`
- 2 views: `v_courses_with_dept`, `v_course_prerequisites`
- Seed data: 64 courses, 7 departments, 109 sections, 6 time slots, 52 prerequisite relationships

### Creating an admin account

After database setup, insert an admin user directly:

```sql
INSERT INTO users (full_name, email, password_hash, role, is_active)
VALUES (
  'Admin Name',
  'admin@ashesi.edu.gh',
  '$2y$10$...', -- generate with: password_hash('yourpassword', PASSWORD_BCRYPT)
  'admin',
  1
);
```

Or use the registration page and manually update the `role` column to `'admin'` via phpMyAdmin.

---

## Environment Variables

| Variable | Description | Example |
|---|---|---|
| `DB_HOST` | Database host | `localhost` |
| `DB_NAME` | Database name | `schedulr_db` |
| `DB_USER` | Database username | `root` |
| `DB_PASS` | Database password | *(empty for local XAMPP)* |
| `ENCRYPTION_KEY` | 32-char AES key for myCAMU credential encryption | `a1b2c3d4e5f6...` |
| `MAIL_HOST` | SMTP host | `smtp.gmail.com` |
| `MAIL_PORT` | SMTP port | `587` |
| `MAIL_USERNAME` | Gmail address | `yourapp@gmail.com` |
| `MAIL_PASSWORD` | Gmail App Password | `xxxx xxxx xxxx xxxx` |
| `MAIL_FROM` | From address in emails | `yourapp@gmail.com` |
| `MAIL_FROM_NAME` | Display name in emails | `Schedulr` |

---

## Running the Application

### Local

1. Ensure XAMPP Apache and MySQL are running
2. Navigate to [http://localhost/Schedulr](http://localhost/Schedulr) in your browser
3. Register a student account, complete the questionnaire, and use the dashboard

### Admin panel

Navigate to [http://localhost/Schedulr/admin](http://localhost/Schedulr/admin) and log in with an admin-role account.

### CAMU Simulator

The simulator at `student/camu-simulator.php` runs the five-step bot pipeline (connect → authenticate → navigate → select → confirm). In **dummy mode** (default), it reads from `user_data/registrations.json`. In production mode, it would interact with the live myCAMU portal.

---

## Deployment (Railway)

### Step 1 — Push your code to GitHub

```bash
git add .
git commit -m "Initial commit"
git push origin main
```

### Step 2 — Create a Railway project

1. Go to [https://railway.app/](https://railway.app/) and sign in
2. Click **New Project** → **Deploy from GitHub repo**
3. Select `YenmaAbambireBawa/Schedulr`
4. Railway will detect PHP and configure Apache automatically

### Step 3 — Add a MariaDB database

1. Inside your Railway project, click **+ New** → **Database** → **MySQL** (compatible with MariaDB)
2. Railway will inject `MYSQL_URL`, `MYSQLHOST`, `MYSQLPORT`, `MYSQLUSER`, `MYSQLPASSWORD`, `MYSQLDATABASE` automatically

### Step 4 — Set environment variables

In Railway → your service → **Variables**, add:

```
ENCRYPTION_KEY=your-32-char-key
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-gmail@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_FROM=your-gmail@gmail.com
MAIL_FROM_NAME=Schedulr
```

### Step 5 — Import the database schema

Connect to your Railway database using the provided connection string and run `setup.sql`:

```bash
mysql -h <RAILWAY_HOST> -P <PORT> -u <USER> -p <DATABASE> < database/setup.sql
```

### Step 6 — Deploy

Railway auto-deploys on every push to `main`. Visit the Railway-generated URL (or your custom domain) to access the live app.

**Live instance:** [https://schedulr-production.up.railway.app/](https://schedulr-production.up.railway.app/)

---

## Project Structure

```
Schedulr/
├── index.html                  # Public landing page
├── about.html                  # About page
├── schedulr-login.html         # Login / registration UI
│
├── student/
│   ├── questionnaire.php       # Onboarding form
│   ├── dashboard.php           # Pre-selection dashboard (core UI)
│   ├── registration-pending.php # Status page (polls every 10s)
│   ├── registration-success.php # Confirmation page
│   └── camu-simulator.php      # 5-step bot pipeline
│
├── api/
│   ├── login.php               # POST: authenticate
│   ├── register.php            # POST: create account
│   ├── submit-registration.php # POST: save ranked options, encrypt, email
│   ├── verify-email.php        # GET: validate token
│   ├── registration-status.php # GET: return JSON status
│   ├── resend-verification.php # POST: resend with rate limit
│   ├── save-questionnaire.php  # POST: save onboarding data
│   └── admin.php               # Multi-action CRUD admin endpoint
│
├── middleware/
│   └── Auth.php                # RBAC, session management, bcrypt
│
├── config/
│   ├── database.php            # PDO connection
│   └── email.php               # PHPMailer SMTP config
│
├── models/
│   └── User.php                # User model (create, verify, token)
│
├── helpers/
│   └── Mailer.php              # Mailer helper wrapping PHPMailer
│
├── database/
│   └── setup.sql               # Full schema + seed data
│
├── user_data/                  # Flat file cache (gitignored in production)
│   └── registrations.json      # Dummy-mode registration store
│
└── vendor/                     # Composer dependencies (PHPMailer)
```

---

## API Endpoints

| Endpoint | Method | Auth | Description |
|---|---|---|---|
| `api/login.php` | POST | None | Authenticate and create session |
| `api/register.php` | POST | None | Create student account |
| `api/submit-registration.php` | POST | Student | Save ranked options, encrypt credentials, send verification email |
| `api/verify-email.php` | GET | None | Validate email token, update status |
| `api/registration-status.php` | GET | Student | Return current registration status as JSON |
| `api/resend-verification.php` | POST | Student | Resend verification email (2-min rate limit) |
| `api/save-questionnaire.php` | POST | Student | Save onboarding profile |
| `api/admin.php?action=*` | Multi | Admin | CRUD for all entities (9 actions) |

---

## Algorithm Overview

The gate-based registration algorithm processes each student's three ranked options in order. For each option it applies three sequential gates:

```
FOR each ranked option (1 → 2 → 3):
  Gate 1: Check all prerequisites          O(c · p)
  Gate 2: Check all course pair clashes    O(c²)
  Gate 3: Check all section capacities     O(c)
  
  If all gates pass:
    BEGIN TRANSACTION
      Decrement section capacities
      Insert enrolment records
      Update status → 'completed'
    COMMIT
    Send confirmation email
    RETURN success
  ELSE:
    Try next option

If all three options fail:
  Insert waitlist record
  Update status → 'waitlisted'
  Send waitlist notification
```

**Complexity:** O(n) overall for n students — linear and optimal. Per-student work is O(c·p + c²), effectively constant at Ashesi's scale (c ≤ 6, p ≤ 4).

---

## Known Limitations

- **No live CAMU integration** — the CAMU simulator runs in dummy mode and does not execute real enrolments
- **Partial course catalogue** — the database covers CS and Africana Studies courses only; official data for other departments was not available during development
- **Simulated time slots** — section times are based on simulated data, not the official Ashesi timetable
- **Fairness algorithm not implemented** — the need-based seat allocation model is fully specified in the thesis (Chapter 4) but requires institutional data not available during development
- **Flat file / database inconsistency** — questionnaire data is cached in both `user_data/` and the `student_questionnaires` table; these should be unified in production

---

## Author

**Yenma Abambire Bawa**
B.Sc. Computer Science, Ashesi University, 2026
Supervisor: Dr. Sampson Dankyi Asare

- **Live app:** [https://schedulr-production.up.railway.app/](https://schedulr-production.up.railway.app/)
- **Repository:** [https://github.com/YenmaAbambireBawa/Schedulr](https://github.com/YenmaAbambireBawa/Schedulr)
