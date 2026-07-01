# INITIAL-D Database Design

## 1. Project Overview

INITIAL-D is a Race Track Booking Website developed as an MCA Mini Project.

The system has three main modules:

- User
- Vendor
- Admin

Users can register, login, search race tracks, view race track details, book race tracks, and view their booking history.

Vendors can register, login, manage their profile, add race tracks, manage race track images, and accept or reject bookings.

Admin can login, manage users, manage vendors, approve or reject race track listings, and monitor bookings.

The database is designed to be simple, professional, and easy to explain during viva. It avoids unnecessary complexity while still following good MySQL design practices.

## 2. Database Overview

The database stores information about users, vendors, admins, race tracks, race track images, and bookings.

Recommended database name:

```text
initial_d
```

No payment table is included in the first version because payment handling is kept simple. The project can assume payment is handled offline or at the venue.

## 3. Entity List

The database will contain the following tables:

1. `users`
2. `vendors`
3. `admins`
4. `race_tracks`
5. `track_images`
6. `bookings`

These six tables are enough for the main features of the INITIAL-D mini project.

## 4. Table Details

## 4.1 users

The `users` table stores customer account details.

This table is needed because users must be able to register, login, book race tracks, and view their booking history.

### Columns

| Column | Data Type | Purpose | Required | Unique | Key |
|---|---|---|---|---|---|
| `user_id` | INT | Unique ID for each user | Yes | Yes | Primary Key |
| `full_name` | VARCHAR(100) | Stores the user's full name | Yes | No | - |
| `email` | VARCHAR(100) | Stores the user's login email | Yes | Yes | - |
| `phone` | VARCHAR(15) | Stores the user's phone number | Yes | Yes | - |
| `password` | VARCHAR(255) | Stores the hashed password | Yes | No | - |
| `status` | ENUM('Active', 'Blocked') | Shows whether the user can access the system | Yes | No | - |
| `created_at` | DATETIME | Stores when the user account was created | Yes | No | - |
| `updated_at` | DATETIME | Stores when the user account was last updated | Yes | No | - |

### Design Notes

- `email` is unique because one email should belong to only one user account.
- `phone` is unique because the same phone number should not be used by multiple users.
- `password` uses `VARCHAR(255)` so PHP password hashes can be stored safely.
- `status` uses fixed values: `Active` and `Blocked`.

## 4.2 vendors

The `vendors` table stores vendor account and business details.

This table is needed because vendors are responsible for adding race tracks and managing bookings for their tracks.

### Columns

| Column | Data Type | Purpose | Required | Unique | Key |
|---|---|---|---|---|---|
| `vendor_id` | INT | Unique ID for each vendor | Yes | Yes | Primary Key |
| `business_name` | VARCHAR(150) | Stores the vendor business name | Yes | No | - |
| `owner_name` | VARCHAR(100) | Stores the vendor owner or contact person name | Yes | No | - |
| `email` | VARCHAR(100) | Stores the vendor login email | Yes | Yes | - |
| `phone` | VARCHAR(15) | Stores the vendor phone number | Yes | Yes | - |
| `password` | VARCHAR(255) | Stores the hashed password | Yes | No | - |
| `address` | TEXT | Stores the vendor business address | No | No | - |
| `profile_image` | VARCHAR(255) | Stores the path of the vendor profile image | No | No | - |
| `status` | ENUM('Pending', 'Approved', 'Blocked') | Shows the vendor account approval status | Yes | No | - |
| `created_at` | DATETIME | Stores when the vendor account was created | Yes | No | - |
| `updated_at` | DATETIME | Stores when the vendor account was last updated | Yes | No | - |

### Design Notes

- Vendor accounts should start as `Pending`.
- Admin can later change vendor status to `Approved` or `Blocked`.
- `profile_image` stores only the file path, not the actual image.
- `phone` is unique to avoid duplicate vendor contact numbers.

## 4.3 admins

The `admins` table stores admin login details.

This table is needed because admin users must login separately to manage the platform.

### Columns

| Column | Data Type | Purpose | Required | Unique | Key |
|---|---|---|---|---|---|
| `admin_id` | INT | Unique ID for each admin | Yes | Yes | Primary Key |
| `admin_name` | VARCHAR(100) | Stores the admin name | Yes | No | - |
| `email` | VARCHAR(100) | Stores the admin login email | Yes | Yes | - |
| `password` | VARCHAR(255) | Stores the hashed password | Yes | No | - |
| `created_at` | DATETIME | Stores when the admin account was created | Yes | No | - |

### Design Notes

- This table is intentionally simple.
- Admin does not need a status field in the first version.
- Admin records may be inserted manually during project setup.

## 4.4 race_tracks

The `race_tracks` table stores race track listing details.

This table is needed because race tracks are the main service being offered on the website.

### Columns

| Column | Data Type | Purpose | Required | Unique | Key |
|---|---|---|---|---|---|
| `track_id` | INT | Unique ID for each race track | Yes | Yes | Primary Key |
| `vendor_id` | INT | Stores which vendor owns the race track | Yes | No | Foreign Key |
| `track_name` | VARCHAR(150) | Stores the race track name | Yes | No | - |
| `location` | VARCHAR(150) | Stores the city or area of the race track | Yes | No | - |
| `address` | TEXT | Stores the full race track address | Yes | No | - |
| `description` | TEXT | Stores details about the race track | No | No | - |
| `price_per_hour` | DECIMAL(10,2) | Stores the hourly booking price | Yes | No | - |
| `track_type` | VARCHAR(50) | Stores the type of track, such as karting, bike, or car | No | No | - |
| `opening_time` | TIME | Stores the daily opening time of the track | Yes | No | - |
| `closing_time` | TIME | Stores the daily closing time of the track | Yes | No | - |
| `contact_number` | VARCHAR(15) | Stores track-specific contact number, if different from vendor phone | No | No | - |
| `status` | ENUM('Pending', 'Approved', 'Rejected') | Shows whether the track is approved by admin | Yes | No | - |
| `created_at` | DATETIME | Stores when the race track was added | Yes | No | - |
| `updated_at` | DATETIME | Stores when the race track was last updated | Yes | No | - |

### Design Notes

- `vendor_id` connects each race track to the vendor who added it.
- `opening_time` and `closing_time` help users understand when the track is available.
- `contact_number` is optional because the vendor phone number may already be enough.
- Race tracks should start as `Pending` until admin approval.

## 4.5 track_images

The `track_images` table stores images for race tracks.

This table is needed because one race track may have multiple images.

### Columns

| Column | Data Type | Purpose | Required | Unique | Key |
|---|---|---|---|---|---|
| `image_id` | INT | Unique ID for each image | Yes | Yes | Primary Key |
| `track_id` | INT | Stores which race track the image belongs to | Yes | No | Foreign Key |
| `image_path` | VARCHAR(255) | Stores the uploaded image file path | Yes | No | - |
| `is_main` | TINYINT | Marks whether this is the main display image, 1 for yes and 0 for no | Yes | No | - |
| `created_at` | DATETIME | Stores when the image was uploaded | Yes | No | - |

### Design Notes

- Image files are stored in the project folder.
- The database stores only the image path.
- `is_main` helps choose one image as the main thumbnail for a track.

## 4.6 bookings

The `bookings` table stores race track booking records.

This table is needed because users must be able to book tracks, vendors must manage booking requests, and admin must monitor bookings.

### Columns

| Column | Data Type | Purpose | Required | Unique | Key |
|---|---|---|---|---|---|
| `booking_id` | INT | Unique ID for each booking | Yes | Yes | Primary Key |
| `booking_reference` | VARCHAR(30) | Human-readable booking reference number | Yes | Yes | - |
| `user_id` | INT | Stores which user made the booking | Yes | No | Foreign Key |
| `track_id` | INT | Stores which race track was booked | Yes | No | Foreign Key |
| `booking_date` | DATE | Stores the date selected for the booking | Yes | No | - |
| `start_time` | TIME | Stores the booking start time | Yes | No | - |
| `end_time` | TIME | Stores the booking end time | Yes | No | - |
| `total_amount` | DECIMAL(10,2) | Stores the total booking amount | Yes | No | - |
| `status` | ENUM('Pending', 'Accepted', 'Rejected', 'Cancelled', 'Completed') | Shows the current booking status | Yes | No | - |
| `created_at` | DATETIME | Stores when the booking was created | Yes | No | - |
| `updated_at` | DATETIME | Stores when the booking was last updated | Yes | No | - |

### Design Notes

- `booking_reference` is useful for displaying a simple reference like `BK20260001` to users.
- Payment is not stored in a separate table in the first version.
- `status` helps vendors accept or reject booking requests.
- Admin can monitor all booking records.

## 5. Primary Keys

Each table has one primary key:

| Table | Primary Key |
|---|---|
| `users` | `user_id` |
| `vendors` | `vendor_id` |
| `admins` | `admin_id` |
| `race_tracks` | `track_id` |
| `track_images` | `image_id` |
| `bookings` | `booking_id` |

Primary keys uniquely identify each record in a table.

## 6. Foreign Keys

Foreign keys connect related tables.

| Table | Foreign Key | References |
|---|---|---|
| `race_tracks` | `vendor_id` | `vendors.vendor_id` |
| `track_images` | `track_id` | `race_tracks.track_id` |
| `bookings` | `user_id` | `users.user_id` |
| `bookings` | `track_id` | `race_tracks.track_id` |

Foreign keys help maintain data consistency.

## 7. Relationships

### Vendor to Race Tracks

One vendor can add many race tracks.

One race track belongs to one vendor.

Relationship:

```text
vendors 1 ---- many race_tracks
```

### Race Tracks to Track Images

One race track can have many images.

One image belongs to one race track.

Relationship:

```text
race_tracks 1 ---- many track_images
```

### Users to Bookings

One user can make many bookings.

One booking belongs to one user.

Relationship:

```text
users 1 ---- many bookings
```

### Race Tracks to Bookings

One race track can have many bookings.

One booking belongs to one race track.

Relationship:

```text
race_tracks 1 ---- many bookings
```

### Admin Relationship

The `admins` table does not need a foreign key relationship in the first version.

Admin will manage records from other tables through PHP pages.

## 8. Design Decisions

- Separate `users`, `vendors`, and `admins` tables are used because each role has different fields and responsibilities.
- `phone` is unique in the `users` and `vendors` tables to avoid duplicate contact accounts.
- Password fields use `VARCHAR(255)` because PHP password hashes can be long.
- `track_images` is separate because one track can have multiple images.
- `bookings` connects users and race tracks.
- Payment handling is kept simple, so no `payments` table is added.
- `updated_at` is added where records are expected to change often.
- ENUM values are clearly defined so status fields are easy to understand.
- The design avoids extra tables like notifications, reviews, and advanced availability to keep the project beginner-friendly.

## 9. Future Improvements

The following features can be added later if the project needs more depth:

- `payments` table for online payment tracking
- `reviews` table for user ratings and feedback
- `track_availability` table for advanced date-wise availability
- `notifications` table for user and vendor alerts
- `admin_logs` table to track admin actions
- Multiple admin roles, such as super admin and staff admin

These improvements are optional and should not be added in the first version unless required by the project guide.

## 10. Final Recommended Tables

For the first version of INITIAL-D, the recommended tables are:

1. `users`
2. `vendors`
3. `admins`
4. `race_tracks`
5. `track_images`
6. `bookings`

This design is simple enough for learning, professional enough for an MCA Mini Project, and clear enough to explain during viva.
