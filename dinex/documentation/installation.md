# DineX Installation Guide

## Prerequisites
- PHP 8.0+
- MySQL 8.0+ / MariaDB
- Apache/Nginx with mod_rewrite (for .htaccess)
- cPanel/shared hosting or VPS

## Steps

1. **Upload files**  
   Upload the `dinex/` folder to your web root or a subdirectory.

2. **Create database**  
   Create a MySQL database and user. Import `database/dinex.sql`.

3. **Configure**  
   Edit `config/config.php` and set database credentials, base URL, and environment.

4. **Permissions**  
   Ensure `logs/` and `assets/uploads/` are writable.

5. **Access**  
   - Home: `http://yourdomain.com/dinex/`
   - Founder: `/admin/founder/login.php`
   - Restaurant: `/admin/restaurant/login.php`

6. **Demo credentials**  
   Founder: `founder@dinex.local` / `password`  
   Restaurant Owner: `owner@spicegarden.local` / `password`

7. **Generate QR codes**  
   Login as restaurant owner, go to Tables, then QR Codes to generate QR for each table.