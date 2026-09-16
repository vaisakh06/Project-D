# INITIAL-D

INITIAL-D is a PHP + MySQL race track booking website for an MCA Mini Project.

All core features are implemented and working.

## Planned Modules

- User registration and login
- Race track search and filtering
- Race track details
- Race track booking
- User booking history
- Vendor registration and login
- Vendor race track management
- Vendor booking management
- Admin dashboard
- User and vendor management
- Race track approval
- Booking monitoring

## Technology Stack

- Frontend: HTML, CSS, JavaScript
- Backend: PHP procedural style
- Database: MySQL with MySQLi
- Server: WAMP Server

## Folder Structure

```text
INITIAL-D/
├── index.php
├── about.php
├── contact.php
├── tracks.php
├── track.php
├── login.php
├── register.php
├── payment.php
├── payment_success.php
├── payment_failed.php
├── README.md
├── AGENTS.md
├── NOTES.md
├── admin/
│   ├── dashboard.php
│   ├── login.php
│   ├── logout.php
│   ├── manage_vendors.php
│   ├── manage_race_tracks.php
│   ├── manage_bookings.php
│   ├── manage_contacts.php
│   └── view_contact.php
├── user/
│   ├── dashboard.php
│   ├── login.php
│   ├── register.php
│   ├── bookings.php
│   ├── profile.php
│   └── logout.php
├── vendor/
│   ├── dashboard.php
│   ├── login.php
│   ├── register.php
│   ├── add_track.php
│   ├── tracks.php
│   ├── edit_track.php
│   ├── delete_track.php
│   ├── track_images.php
│   ├── availability.php
│   ├── manage_bookings.php
│   ├── profile.php
│   └── logout.php
├── config/
│   ├── constants.php
│   └── database.php
├── includes/
│   ├── functions.php
│   ├── header.php
│   ├── footer.php
│   └── navbar.php
├── assets/
│   └── css/
│       └── style.css
├── uploads/
│   ├── tracks/
│   ├── users/
│   └── vendors/
├── database/
│   ├── initial_d.sql
│   └── migrations/
└── docs/
    ├── project_documentation.md
    ├── er_diagram.md
    └── database_design.md
```

## Folder Purpose

`index.php` is the main landing page of the website.

`admin/` contains admin pages such as dashboard, vendor management, track approval, and booking monitoring.

`user/` contains user pages such as dashboard, login, registration, profile, bookings, and logout.

`vendor/` contains vendor pages such as dashboard, login, registration, profile, track management, availability management, booking management, and logout.

`config/` contains project configuration files such as the database connection and constants.

`includes/` contains reusable PHP files such as header, footer, navbar, and helper functions.

`assets/` contains frontend files used by the website.

`assets/css/` contains CSS stylesheet files.

`uploads/` contains files uploaded through the website.

`uploads/tracks/` contains race track images uploaded by vendors.

`uploads/users/` contains user profile images.

`uploads/vendors/` contains vendor profile images.

`database/` contains SQL files related to database design and setup.

`docs/` contains project planning notes, diagrams, and explanations.

## Development Rule

Each feature should follow this process:

1. Explain the feature.
2. Plan the implementation.
3. List the required files.
4. List the required database tables.
5. Wait for confirmation.
6. Implement only that feature.
7. Explain the created files and important code.
8. Suggest one practice task.

## Current Status

The following features are implemented:

- User registration and login (common page with role selector)
- User profile management with profile image upload
- Vendor registration and login (common page with role selector)
- Vendor profile management with profile image upload
- Vendor dashboard, track CRUD, availability management, track image management
- Vendor booking management (accept/reject)
- Admin dashboard with statistics
- Admin vendor management (approve/block/unblock)
- Admin race track management (approve/reject/return to pending/delete)
- Race track browsing, detail view, and booking creation
- User booking history with cancellation (transaction-safe)
- Mock payment flow (Card/UPI)
- Contact form with database storage and CSRF protection
- Admin contact message management (view, reply, mark read/replied, delete)
- Reply history with timestamp tracking
- About and Contact pages
- Role-based access control on all protected pages
- Session-based authentication with password hashing
- Prepared statements for all database queries
- CSRF protection on booking cancellation and contact form
