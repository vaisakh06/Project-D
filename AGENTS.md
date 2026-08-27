# AGENTS.md

## Working with INITIAL-D

**Critical first step**: Always start by reading `config/constants.php:1-33` and `config/database.php:1-14` to understand the project's setup and access patterns.

**Project boundaries**: 
- All application code lives in `admin/`, `user/`, and `vendor/` folders
- Shared code in `includes/` and `config/`
- Database schema in `database/initial_d.sql:1-113`

**Database connection pattern**:
- `config/database.php:9` establishes mysqli connection
- `includes/functions.php:24-37` provides `sanitizeInput()` for cleaning form data
- `includes/functions.php:48-58` provides `redirect()` using `BASE_URL` from constants
- `includes/functions.php:68-72` provides `isPostRequest()` for form handling
- Always use `mysqli_prepare()`, `mysqli_stmt_bind_param()`, and `mysqli_stmt_execute()` with prepared statements

**Authentication flow**:
- `includes/functions.php:107-127` provides `isUserLoggedIn()` and `requireUserLogin()`
- Role detection via session variables: `$_SESSION['user_role']`
- Login scripts in `user/login.php`, `vendor/login.php`, `admin/login.php`

**Common include pattern** (at top of every page):
```php
require_once '../config/constants.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
```

**Entry points**:
- `index.php` - Main landing page
- `user/login.php`, `user/register.php` - User authentication
- `vendor/dashboard.php`, `vendor/add_track.php` - Vendor operations
- `admin/dashboard.php` - Admin management

**Files to avoid creating**:
- Do not modify `.agents` - agent instructions
- Do not edit `.gitkeep` files - they are empty placeholders
- Do not add application code until the user approves a feature plan

**Development approach**:
1. Explain the feature first
2. Plan implementation with specific files
3. List required database tables (reference `initial_d.sql:1-113`)
4. Get user confirmation
5. Implement one feature at a time
6. Explain each file creation reason
7. Suggest one practice improvement
