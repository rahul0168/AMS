# Attendance Management System (AMS)

## Overview
The **Attendance Management System (AMS)** is a PHP-based web application that allows administrators and managers to manage attendance for users across various events. 
It features secure login, duplicate entry prevention, and responsive design using Bootstrap.

---

## Features
- User authentication and role-based access control
- Manage attendance for users and events
- Prevent duplicate entries for the same user and event
- Responsive UI using Bootstrap 5
- Secure database operations using PDO prepared statements
- CSRF token protection for form submissions

---

## Technology Stack
- **Backend:** PHP 8 (PDO for database operations)
- **Frontend:** HTML5, CSS3, JavaScript, Bootstrap 5
- **Database:** MySQL (InnoDB)
- **Server:** Apache 2 with mod_rewrite enabled

---

## Database Schema
Tables used in the system:
1. **users** (id, name, role)
2. **veranstaltung** (id, event_type, event_date)
3. **anwesenheits_kontrolle** (id, user_id, event_id, status, recorded_at)

Relationships:
- Each attendance record references a user and an event.
- Foreign keys ensure data integrity with ON DELETE CASCADE and ON UPDATE CASCADE.

---

## Installation Guide
1. Clone or extract the project files into your Apache `htdocs` or `public_html` directory.
2. Create a MySQL database (e.g., `ams_db`).
3. Import the provided SQL file into the database.
4. Open `db.php` and configure your database credentials.
5. Enable `mod_rewrite` in Apache.
6. Start Apache and MySQL servers.
7. Access the system at `http://localhost/login.php`.

---

## Usage Guide

### Administrator Access
- Create and manage users.
- Create and manage events.
- Record and update attendance.
- View attendance summaries and statistics.

### End User Access
- Login using provided credentials.
- View their attendance records.
- Receive confirmation after attendance submission.

---

## Security Features
- Authentication and session management
- CSRF protection using tokens
- SQL injection prevention with prepared statements
- Role-based access control (Admin, Manager, User)

---

## Error Handling
- User-friendly alerts for invalid inputs and duplicates.
- Try-catch blocks for PDO exceptions.
- Redirects for unauthorized access or invalid CSRF tokens.

---

## Future Enhancements
- Export attendance data to Excel/PDF.
- Real-time analytics dashboard.
- API integration for mobile apps.
- Email/SMS notifications for absentees.
- Audit log for system activity tracking.

---

## Author
Developed by **Rahul Naik Mule**  

---
