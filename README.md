# Mutabiq.ai  

A Laravel project running on PHP 8.2+, set up for local development with PHPUnit tests and Xdebug coverage enabled.  

---

## 📦 Requirements
- **PHP**: ^8.2  
- **Composer**: ^2.7  
- **Laravel**: 12.x  
- **MySQL**

---

## 🚀 Setup Instructions  

### 1. Clone the repository
```bash
git clone https://github.com/RobustAgency/mutabiq.ai.git
cd mutabiq.ai
```

### 2. Install dependencies
```bash
composer install
```

### 3. Configure environment
Copy the test environment file:
```bash
cp .env.test .env
```
Update the following in `.env`:
```ini
APP_NAME=MutabiqAI
APP_ENV=local
APP_KEY= # will be generated below
DB_DATABASE=mvp_skeleton
DB_USERNAME=your_mysql_user
DB_PASSWORD=your_mysql_password
```

### 4. Generate application key
```bash
php artisan key:generate
```

### 5. Create a new database and run migrations
Create the database in MySQL CLI or your DB client:
```sql
CREATE DATABASE mvp_skeleton CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```
Run migrations:
```bash
php artisan migrate
```

---

## 🐞 Xdebug Setup (for coverage)
Ensure Xdebug is installed and enabled. On macOS with Homebrew:
```bash
pecl install xdebug
```
Add to your php.ini (check with `php --ini` to locate the file):
```ini
zend_extension="xdebug.so"
xdebug.mode = debug, coverage
```
Restart PHP-FPM or your terminal session to apply changes.

Verify installation:
```bash
php -v
```
You should see something like:
```
with Xdebug v3.x.x, Copyright (c) 2002-2025, by Derick Rethans
```

---

## ✅ Running Tests
Run all tests:
```bash
php artisan test
```
Run with coverage:
```bash
vendor/bin/phpunit --coverage-text
```

---

## 🔧 Useful Commands
Clear cache:
```bash
php artisan cache:clear
```
Run migrations fresh with seeders:
```bash
php artisan migrate:fresh --seed
```
Serve the application:
```bash
php artisan serve
```

---

## 📖 Notes
- Always run tests before pushing changes.
- Ensure your PHP and Composer versions match the project requirements.
- Coverage via `php artisan test --coverage` may not work on some environments; use `vendor/bin/phpunit --coverage-text` instead.