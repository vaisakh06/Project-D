# INITIAL-D
## Race Track Booking Website
### Project Documentation

---

## Table of Contents

1. Introduction
2. Problem Statement
3. Scope and Relevance of the Project
4. Objectives
5. System Analysis
6. Existing System
7. Limitations of Existing System
8. Proposed System
9. Advantages of Proposed System
10. Feasibility Study
    - 10.1 Technical Feasibility
    - 10.2 Operational Feasibility
    - 10.3 Economic Feasibility
11. Software Engineering Paradigm Applied
12. System Environment
    - 12.1 Introduction
    - 12.2 Software Requirements Specification
    - 12.3 Hardware Requirements Specification
    - 12.4 Tools and Platforms
13. User Characteristics
14. Summary of System Features / Functional Requirements
    - 14.1 User Registration and Authentication
    - 14.2 User Profile Management
    - 14.3 Vendor Registration and Authentication
    - 14.4 Vendor Dashboard & Track Management
    - 14.5 Browse, Search and Filter Race Tracks
    - 14.6 Race Track Details & Gallery
    - 14.7 Booking Management
    - 14.8 Administrative Management
15. Development Roadmap

---

## 1. Introduction

The introduction section explains the basic idea of the project and gives the reader a clear understanding of what the system is about. It is important because it creates the first impression of the project and explains the purpose of building it.

INITIAL-D is a Race Track Booking Website designed to connect users with vendors who provide race tracks for rent. The system allows users to search for race tracks, view details, and make bookings online. Vendors can register on the platform, add their race track listings, manage track details, and respond to booking requests. Administrators can manage the complete platform by monitoring users, vendors, race tracks, and bookings.

This project is suitable for an MCA Mini Project because it covers important web development concepts such as user authentication, role-based access, database design, CRUD operations, file uploads, search, filtering, and booking management. The project will be developed using HTML, CSS, JavaScript, PHP in procedural style, and MySQL with MySQLi.

## 2. Problem Statement

The problem statement explains the issue that the project is trying to solve. It is important because every software project should be built to solve a real or practical problem.

In the current scenario, people who want to book race tracks may need to contact track owners directly through phone calls, social media, or offline visits. This process can be time-consuming and unclear because users may not easily find details such as price, location, opening time, images, or booking availability. Vendors may also find it difficult to manage customer enquiries and booking requests manually.

INITIAL-D solves this problem by providing a centralized online platform where race track details can be listed, searched, viewed, and booked. Users get a simple way to discover race tracks, while vendors get a structured way to manage their listings and bookings. Admin ensures that the platform remains controlled and organized.

## 3. Scope and Relevance of the Project

The scope of a project defines what the system will include and what it will not include in the first version. This section is important because it helps keep the project focused and prevents unnecessary complexity.

The scope of INITIAL-D includes user registration, vendor registration, admin login, race track listing, race track approval, image management, search and filtering, booking requests, booking status updates, and booking history. The system will focus on the core booking workflow rather than advanced features such as online payment, live maps, real-time chat, or mobile applications.

This project is relevant because online booking systems are widely used in many industries such as hotels, sports venues, events, and transport. A race track booking system follows similar principles and helps students understand how real-world booking platforms are designed. It is also relevant for learning database relationships, foreign keys, form handling, validation, and role-based access in PHP.

## 4. Objectives

The objectives section explains what the project aims to achieve. It is important because objectives provide direction for development and help measure whether the project is successful.

The main objective of INITIAL-D is to develop a simple and efficient Race Track Booking Website. The system should allow users to search and book race tracks, allow vendors to manage their race track listings, and allow admin to monitor the platform.

The project also aims to provide a beginner-friendly implementation using procedural PHP and MySQLi. It will help in understanding the complete software development process from requirement analysis and database design to implementation, testing, and deployment. Another objective is to maintain clean documentation so that the project can be explained clearly during viva.

## 5. System Analysis

System analysis means studying the requirements, users, data, and processes of the system before implementation. It is important because proper analysis helps avoid confusion during development.

For INITIAL-D, system analysis identifies three major roles: User, Vendor, and Administrator. Each role has different responsibilities. Users search and book tracks, vendors provide and manage tracks, and admin controls the system.

The analysis also shows that the system needs six main database tables: `users`, `vendors`, `admins`, `race_tracks`, `track_images`, and `bookings`. These tables support the main features of the website and keep the design simple enough for an MCA Mini Project. The system will use forms for data entry, MySQL for storage, and PHP for processing user requests.

## 6. Existing System

The existing system section explains how the process is currently handled without the proposed software. It is important because it helps justify why the new system is needed.

In the existing manual system, users may need to search for race tracks through word of mouth, social media pages, search engines, or direct phone contact. Track details may not be available in one place, and users may need to ask vendors separately about price, timing, location, and availability.

Vendors may maintain bookings manually using notebooks, spreadsheets, phone messages, or social media chats. This can lead to confusion, missed booking requests, duplicate bookings, or difficulty in tracking customer history. Admin-level control is usually absent in such manual systems.

## 7. Limitations of Existing System

This section explains the weaknesses of the existing system. It is important because it shows how the proposed system improves the current process.

The existing manual process is slow because users must contact vendors individually. Track information may be incomplete or outdated. Users may not be able to compare multiple race tracks easily. Vendors may find it difficult to manage bookings in an organized way.

Another limitation is the lack of proper record keeping. Booking history, customer details, track details, and vendor information may not be stored in a structured database. Without a centralized system, it becomes difficult to monitor all activities and maintain transparency.

## 8. Proposed System

The proposed system section explains the new software solution. It is important because it describes how the project will solve the identified problems.

INITIAL-D proposes an online Race Track Booking Website where users, vendors, and admin can interact through a structured platform. Users will be able to register, login, browse tracks, search and filter listings, view details, and make bookings. Vendors will be able to register, login, add race track details, upload images, and manage booking requests. Admin will be able to approve vendors, approve race tracks, manage users, and monitor bookings.

The proposed system will store all important information in a MySQL database. PHP will be used to process forms, validate data, connect to the database, and display dynamic content. The system will be developed step by step so that each module can be understood clearly.

## 9. Advantages of Proposed System

This section explains the benefits of the new system. It is important because it shows the value of building the project.

The proposed system saves time for users because they can view race track details online without contacting each vendor separately. Users can compare tracks based on location, price, type, and other details. The booking process becomes more organized because each booking is stored in the database.

For vendors, the system provides a better way to manage race track listings and booking requests. Vendors can update track information and respond to bookings from one place. For admin, the system provides control over users, vendors, race tracks, and bookings. Overall, the system improves organization, transparency, and accessibility.

## 10. Feasibility Study

A feasibility study checks whether the project can be developed successfully with available technology, resources, and time. It is important because it helps decide whether the project is practical.

For INITIAL-D, the project is feasible because it uses commonly available technologies and does not require expensive tools. The system can be developed and tested locally using WAMP Server.

### 10.1 Technical Feasibility

Technical feasibility checks whether the required technology is available and suitable. It is important because a project should be built using tools that can support its features.

INITIAL-D is technically feasible because it uses HTML5, CSS3, JavaScript, PHP, MySQL, Apache, and WAMP Server. These technologies are widely used for web development and are suitable for an MCA Mini Project. MySQL supports relational database design, and PHP with MySQLi can handle forms, authentication, and database operations.

### 10.2 Operational Feasibility

Operational feasibility checks whether users can understand and use the system easily. It is important because a system is successful only if users can operate it comfortably.

INITIAL-D is operationally feasible because the system will have simple interfaces for each role. Users will interact with forms, search pages, track details pages, and booking history pages. Vendors will use dashboard pages to manage tracks and bookings. Admin will use management pages to approve and monitor records. The project will keep navigation simple so that users can understand the system without special training.

### 10.3 Economic Feasibility

Economic feasibility checks whether the project can be developed within budget. It is important because a student project should not require costly software or infrastructure.

INITIAL-D is economically feasible because all required tools are free or commonly available. WAMP Server, MySQL, PHP, Visual Studio Code, Git, GitHub, and Google Chrome can be used without purchasing expensive licenses. The project can be developed on a normal Windows 11 laptop or desktop.

## 11. Software Engineering Paradigm Applied

The software engineering paradigm explains the development model followed during the project. It is important because it gives a structured approach to planning, designing, developing, and testing the system.

INITIAL-D will follow the Waterfall Model. The Waterfall Model is suitable for this MCA Mini Project because the requirements are clear and the project will be developed step by step. In Waterfall, each phase is completed before moving to the next phase.

### Requirement Analysis

In this phase, the project requirements are collected and understood. For INITIAL-D, this includes identifying user, vendor, and admin modules, deciding the database tables, and listing the main features.

### System Design

In this phase, the system structure is planned. For INITIAL-D, this includes folder structure design, database design, ER Diagram, and deciding how modules will interact with each other.

### Implementation

In this phase, the actual code is written. For INITIAL-D, PHP, HTML, CSS, JavaScript, and MySQLi will be used to implement each module feature by feature.

### Testing

In this phase, the system is checked for errors. For INITIAL-D, testing will include form validation testing, login testing, booking testing, database insertion testing, and role-based access testing.

### Deployment

In this phase, the system is placed in the server environment. For INITIAL-D, deployment can be done locally using WAMP Server and later moved to a hosting server if required.

### Maintenance

In this phase, errors are fixed and improvements are added after deployment. For INITIAL-D, maintenance may include fixing bugs, improving design, adding reviews, or adding payment features in the future.

## 12. System Environment

The system environment section describes the hardware, software, tools, and platforms required for the project. It is important because it helps users and evaluators understand where and how the project will run.

INITIAL-D will be developed and tested on a Windows-based environment using WAMP Server. The project will use a browser for accessing web pages and phpMyAdmin for database management.

### 12.1 Introduction

The system environment provides the setup needed to develop and run the project. For INITIAL-D, this includes frontend technologies, backend technology, database, server, development tools, browser, and operating system.

This section is important because a web application needs a proper environment to work correctly. PHP requires a server, MySQL requires a database server, and the browser is needed to view the website.

### 12.2 Software Requirements Specification

The software requirements define the software tools and technologies needed for the project.

Frontend technologies will include HTML5, CSS3, and JavaScript. HTML5 will be used to structure web pages, CSS3 will be used for styling, and JavaScript will be used for basic client-side interactions.

The backend will use PHP in procedural style. PHP will handle form submissions, database operations, sessions, authentication, and page logic.

The database will use MySQL. MySQL will store users, vendors, admins, race tracks, track images, and bookings.

The server environment will use Apache through WAMP Server. WAMP provides Apache, MySQL, and PHP in one local development package.

Development tools will include Visual Studio Code, Git, GitHub, and OpenAI Codex. Visual Studio Code will be used for writing code, Git for version control, GitHub for repository backup, and OpenAI Codex for guided development support.

The browser will be Google Chrome, and the operating system will be Windows 11.

### 12.3 Hardware Requirements Specification

Hardware requirements describe the physical computer resources needed to run the project.

A basic laptop or desktop computer is enough for INITIAL-D. The recommended system includes a modern processor, at least 4 GB RAM, enough free disk space for WAMP Server and project files, and a stable internet connection for GitHub usage and learning resources.

Since INITIAL-D is a mini project, it does not require a high-end server. The project can run locally on WAMP Server for development and demonstration.

### 12.4 Tools and Platforms

The tools and platforms are selected to keep the project simple and practical.

Visual Studio Code will be used as the code editor. WAMP Server will provide Apache, PHP, and MySQL locally. phpMyAdmin will help manage the MySQL database through a browser interface. Git and GitHub will help maintain version control and backup. Google Chrome will be used to test and run the web application.

## 13. User Characteristics

User characteristics describe the types of users who will interact with the system. This section is important because different users need different permissions and features.

INITIAL-D has three user roles: User, Vendor, and Administrator.

The User is a customer who wants to search and book race tracks. A user can register, login, manage profile details, browse tracks, view track details, make bookings, and view booking history.

The Vendor is a track provider who lists race tracks on the platform. A vendor can register, login, manage profile details, add race tracks, update track details, upload images, and accept or reject booking requests.

The Administrator controls the website. The admin can login, manage users, manage vendors, approve or reject race tracks, and monitor bookings. Admin has higher permissions than users and vendors.

## 14. Summary of System Features / Functional Requirements

Functional requirements explain what the system must do. This section is important because it becomes the base for implementation and testing.

The following modules describe the main features of INITIAL-D.

## 14.1 User Registration and Authentication

### Introduction

This module allows normal users to create an account and login to the system. It is important because only registered users should be able to make bookings and view booking history.

### Inputs

The user will provide details such as full name, email, phone number, and password during registration. During login, the user will provide email and password.

### Processing

The system will validate the submitted information, check whether email and phone number are unique, hash the password, and store the user record in the database. During login, the system will verify the email and password and start a session for the user.

### Outputs

After successful registration, the user account will be stored in the database. After successful login, the user will be redirected to the user area. If validation fails, the system will show an error message.

### Database Tables Used

This module will use the `users` table.

### Files That Will Be Created

Expected files include `user/register.php`, `user/login.php`, `user/logout.php`, and related include files for session checking.

### Learning Notes

This module teaches form handling, validation, password hashing, sessions, and database insertion using MySQLi prepared statements.

## 14.2 User Profile Management

### Introduction

This module allows users to view and update their profile information. It is important because users may need to correct their name, phone number, or other account details.

### Inputs

The user may provide updated full name, phone number, and other editable profile details.

### Processing

The system will check whether the user is logged in, validate updated data, check phone uniqueness if changed, and update the record in the database.

### Outputs

The updated profile information will be saved in the database. The user will see a success message or validation error.

### Database Tables Used

This module will use the `users` table.

### Files That Will Be Created

Expected files include `user/profile.php` and possibly `user/update_profile.php`.

### Learning Notes

This module teaches update operations, session-based user identification, and safe database updates using prepared statements.

## 14.3 Vendor Registration and Authentication

### Introduction

This module allows vendors to register and login. It is important because vendors need a separate role to manage race tracks and bookings.

### Inputs

The vendor will provide business name, owner name, email, phone number, password, address, and optionally a profile image.

### Processing

The system will validate vendor data, check uniqueness of email and phone, hash the password, upload the profile image if provided, and store the vendor record. The default vendor status will be Pending until approved by admin.

### Outputs

The vendor account will be stored in the database. The vendor may see a message that the account is pending approval. During login, approved vendors will be allowed to access the vendor area.

### Database Tables Used

This module will use the `vendors` table.

### Files That Will Be Created

Expected files include `vendor/register.php`, `vendor/login.php`, `vendor/logout.php`, and vendor session check files.

### Learning Notes

This module teaches role-based authentication, file upload basics, account approval status, and secure password storage.

## 14.4 Vendor Dashboard & Track Management

### Introduction

This module allows vendors to manage race tracks. It is important because vendors are responsible for adding and maintaining the track listings shown to users.

### Inputs

Vendors will provide track name, location, address, description, price per hour, track type, opening time, closing time, contact number, and images.

### Processing

The system will validate track details, store the track record, upload track images, and connect the track to the logged-in vendor. New tracks will have Pending status until admin approval.

### Outputs

The race track record and images will be stored in the database. Vendors will be able to view their added tracks and edit or delete them according to project rules.

### Database Tables Used

This module will use the `vendors`, `race_tracks`, and `track_images` tables.

### Files That Will Be Created

Expected files include `vendor/dashboard.php`, `vendor/add_track.php`, `vendor/edit_track.php`, `vendor/delete_track.php`, and `vendor/manage_tracks.php`.

### Learning Notes

This module teaches CRUD operations, foreign keys, image uploads, and how vendor records are connected to race track records.

## 14.5 Browse, Search and Filter Race Tracks

### Introduction

This module allows users and visitors to browse available race tracks. It is important because users need a simple way to find tracks based on their requirements.

### Inputs

Users may provide search text, location, track type, or price range as filters.

### Processing

The system will search approved race track records and apply filters based on user input. Only tracks with Approved status should be shown to users.

### Outputs

The system will display a list of matching race tracks with basic details such as name, location, price, and main image.

### Database Tables Used

This module will use the `race_tracks`, `track_images`, and `vendors` tables.

### Files That Will Be Created

Expected files include `user/browse_tracks.php` or `tracks.php`, depending on the final navigation plan.

### Learning Notes

This module teaches SELECT queries, filtering, searching, joins, and displaying database records in a user-friendly format.

## 14.6 Race Track Details & Gallery

### Introduction

This module allows users to view complete information about a selected race track. It is important because users need details before making a booking decision.

### Inputs

The main input is the selected track ID, usually passed through the page URL.

### Processing

The system will fetch the selected race track details, related vendor information, and track images from the database.

### Outputs

The user will see full track information, price, timing, location, description, contact number, and image gallery.

### Database Tables Used

This module will use the `race_tracks`, `track_images`, and `vendors` tables.

### Files That Will Be Created

Expected files include `user/track_details.php`.

### Learning Notes

This module teaches how to fetch one record using an ID, how to display related records, and how to use foreign key relationships in practical pages.

## 14.7 Booking Management

### Introduction

This module allows users to book race tracks and allows vendors to accept or reject booking requests. It is one of the most important modules because booking is the core purpose of the project.

### Inputs

Users will provide booking date, start time, and end time. The system will also use the selected track ID and logged-in user ID.

### Processing

The system will validate the booking details, calculate the total amount based on price per hour, generate a booking reference, and store the booking with Pending status. Vendors can later accept or reject the booking. Users can view their booking history.

### Outputs

The booking record will be stored in the database. Users will see booking status and booking reference. Vendors will see booking requests for their tracks.

### Database Tables Used

This module will use the `users`, `race_tracks`, `vendors`, and `bookings` tables.

### Files That Will Be Created

Expected files include `user/book_track.php`, `user/booking_history.php`, `vendor/manage_bookings.php`, and `vendor/update_booking_status.php`.

### Learning Notes

This module teaches transaction-like thinking, date and time handling, status management, and how one table can connect two main entities.

## 14.8 Administrative Management

### Introduction

This module allows admin to manage the full platform. It is important because the system needs control over users, vendors, race tracks, and bookings.

### Inputs

Admin may provide login credentials, approval decisions, blocking decisions, and status updates.

### Processing

The system will verify admin login and allow admin to view and manage records. Admin can approve vendors, approve or reject race tracks, block users or vendors, and monitor bookings.

### Outputs

Updated statuses will be stored in the database. Admin will see dashboards and management pages showing platform records.

### Database Tables Used

This module will use the `admins`, `users`, `vendors`, `race_tracks`, and `bookings` tables.

### Files That Will Be Created

Expected files include `admin/login.php`, `admin/dashboard.php`, `admin/manage_users.php`, `admin/manage_vendors.php`, `admin/manage_tracks.php`, and `admin/manage_bookings.php`.

### Learning Notes

This module teaches admin authentication, role-based access, record management, and how one role can control multiple parts of the system.

## 15. Development Roadmap

The development roadmap shows the planned order of implementation. It is important because the project should be built step by step instead of generating all features at once.

1. Finalize project documentation and database documentation.
2. Create and import the MySQL database schema.
3. Create the database connection file.
4. Build common includes such as header, footer, and session helpers.
5. Implement user registration and login.
6. Implement vendor registration and login.
7. Implement admin login.
8. Build vendor dashboard and race track management.
9. Add race track image upload.
10. Build race track browsing, searching, and filtering.
11. Build race track detail page and gallery.
12. Implement user booking creation.
13. Implement user booking history.
14. Implement vendor booking management.
15. Implement admin management pages.
16. Test all modules.
17. Prepare final report, screenshots, and viva notes.

This roadmap keeps the project organized and supports learning. Each module should be planned, implemented, explained, tested, and approved before moving to the next module.
