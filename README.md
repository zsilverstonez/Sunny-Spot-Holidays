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
- Responsive design for desktop, tablet, and mobile
- Google Analytics and Search Console integration

---

## Technologies

- HTML, CSS, JavaScript
- PHP
- MySQL
- PHPMailer
- Git/GitHub

---

## Setup

1. Clone the repository
2. Import database schema into MySQL
3. Copy .env.example to .env and update database and email credentials
4. Run composer install to install dependencies (PHPMailer, phpdotenv, etc.)
5. Ensure .env and /vendor are included in .gitignore
6. Upload required directories (images, staffPhoto, email)
7. Deploy the project to a PHP-enabled web server
