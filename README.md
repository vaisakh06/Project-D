# 🏁 INITIAL-D – Racetrack Booking Platform

**INITIAL-D** is a full-stack racetrack booking platform developed as an MCA mini project. It connects users looking to book racetracks with vendors who manage racing facilities through a centralized web application.

The platform provides separate interfaces for users, vendors, and administrators, making it easier to manage racetrack listings, availability, bookings, and platform operations.

### Key Highlights

- **User Management:** Registration, authentication, profile management, and booking history.
- **Vendor Management:** Racetrack creation, editing, availability management, and booking handling.
- **Admin Dashboard:** Vendor approvals, racetrack moderation, and booking monitoring.
- **Booking System:** Racetrack browsing, booking creation, and cancellation.
- **Payment Simulation:** Mock Card/UPI payment workflow.
- **Security:** Password hashing, prepared SQL statements, session-based authentication, and CSRF protection.

### Technology Stack

- **Frontend:** HTML5, CSS3, JavaScript
- **Backend:** PHP (Procedural)
- **Database:** MySQL
- **Database Connectivity:** MySQLi
- **Local Development:** WAMP Server

- ## 📌 Application Modules

### 1. User Module
- User registration and login.
- Profile management with profile image upload.
- Browse and search racetracks.
- View racetrack details.
- Create racetrack bookings.
- View booking history.
- Cancel bookings.
- Complete the mock payment workflow.

### 2. Vendor Module
- Vendor registration and authentication.
- Vendor profile management.
- Add, edit, and manage racetracks.
- Upload and manage racetrack images.
- Manage racetrack availability.
- View and manage customer bookings.
- Accept or reject booking requests.

### 3. Admin Module
- Admin dashboard with platform statistics.
- Manage vendor accounts.
- Approve, block, and unblock vendors.
- Review and approve or reject racetrack listings.
- Monitor booking activities.
- Manage contact messages.
- View and manage customer enquiries.

### 4. Booking and Payment Module
- Racetrack booking creation.
- Booking status management.
- User booking history.
- Transaction-safe booking cancellation.
- Mock Card and UPI payment workflow.
- Payment success and failure handling.

### 5. Security and Authentication
- Session-based authentication.
- Password hashing.
- Role-based access control.
- Prepared statements for database queries.
- CSRF protection for supported forms and actions.

- ## ⚙️ Installation and Setup

Follow these steps to run INITIAL-D locally.

### Prerequisites

Make sure you have installed:

- [WampServer](https://www.wampserver.com/)
- PHP 8.x
- MySQL
- phpMyAdmin
- Git

### 1. Clone the Repository

Open your terminal and run:

```bash
git clone https://github.com/vaisakh06/Project-D.git
```

### 2. Move the Project

Copy or move the cloned `Project-D` folder into your WAMP `www` directory.

Example:

```text
C:\wamp64\www\Project-D
```

### 3. Start WAMP Server

- Launch WampServer.
- Make sure Apache and MySQL services are running.
- Open phpMyAdmin in your browser.

### 4. Create the Database

1. Open phpMyAdmin.
2. Create a database named `initial_d`.
3. Select the newly created database.
4. Import the SQL file located at:

```text
database/initial_d.sql
```

### 5. Configure the Database Connection

Open:

```text
config/database.php
```

Update the database credentials according to your local MySQL configuration.

### 6. Run the Application

Open your browser and visit:

```text
http://localhost/Project-D/
```

The application should now be running locally.

### Note

This project is intended for local development and demonstration purposes. Payment processing uses a mock workflow and does not involve real financial transactions.


