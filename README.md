# INITIAL-D

INITIAL-D is a PHP + MySQL race track booking website for an MCA Mini Project.

The project will be built feature by feature for learning. No full application code should be generated at once.

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
├── README.md
├── AGENTS.md
├── NOTES.md
├── admin/
├── user/
├── vendor/
├── config/
├── includes/
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
├── uploads/
│   └── tracks/
├── database/
└── docs/
```

## Folder Purpose

`index.php` will become the main landing page of the website. It is currently only a placeholder.

`admin/` will contain admin pages, such as dashboard, user management, vendor management, track approval, and booking monitoring.

`user/` will contain user pages, such as registration, login, profile, race track search, booking, and booking history.

`vendor/` will contain vendor pages, such as registration, login, profile, track management, availability management, and booking requests.

`config/` will contain project configuration files, such as the database connection file.

`includes/` will contain reusable PHP files, such as header, footer, helper functions, and session checking files.

`assets/` contains frontend files used by the website.

`assets/css/` will contain CSS stylesheet files.

`assets/js/` will contain JavaScript files.

`assets/images/` will contain public images used in the website design.

`uploads/` will contain files uploaded through the website.

`uploads/tracks/` will contain race track images uploaded by vendors.

`database/` will contain SQL files related to database design and setup.

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

Simple project scaffold created. Application code, database tables, and feature files have not been added yet.
