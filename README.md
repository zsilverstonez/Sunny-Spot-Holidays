# Sunny Spot Holidays - Cabin Booking Website

A full-stack cabin booking system built with PHP and MySQL.

Live Demo: https://sunnyspotholidays.com.au

Note: This is a mock website created as a portfolio project.

---

## Features

- Secure user authentication with bcrypt password hashing
- Role-based access control (Manager vs. Staff)
- Admin dashboard for managing cabins, bookings, customer messages, and user accounts
- CSRF protection on all forms
- SQL injection prevention using prepared statements
- Activity logging for owner/manager oversight
- Automated email notifications via PHPMailer
- Password reset functionality with token-based verification
- Responsive design for desktop, tablet, and mobile
- Google Analytics and Search Console integration

---

## Technologies

- HTML, CSS, JavaScript
- PHP
- MySQL
- PHPMailer
- Composer (vlucas/phpdotenv)
- Git/GitHub

---

## Project Structure

```
/                           # Public pages (index, booking, contact, etc.)
/admin/                     # Admin-only pages
    login.php              # Staff/admin login
    logout.php             # Session logout handler
    email.php              # Password reset email sender
    reset_password.php     # Password reset form
/images/                    # Image assets
/vendor/                    # Composer dependencies (not in repo)
.env                        # Environment variables (not in repo)
database_connect.php        # Database connection (shared)
```

---

## Setup

1. Clone the repository
2. Import database schema into MySQL
3. Copy `.env.example` to `.env` and update credentials:
   ```
   DB_HOST=localhost
   DB_USER=your_db_user
   DB_PASS=your_db_password
   DB_NAME=your_db_name
   MAIL_HOST=your_smtp_host
   FROM_EMAIL=your_email@domain.com
   ```
4. Run `composer install` to install dependencies
5. Ensure `.env` and `/vendor` are in `.gitignore`
6. Deploy to a PHP-enabled web server

