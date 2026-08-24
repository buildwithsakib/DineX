# Deployment Guide

## Production Checklist

- Set `APP_ENV` to `production` in `config/config.php`
- Enable HTTPS and set `SESSION_COOKIE_SECURE` to `true`
- Change all demo passwords
- Set proper file permissions (755 for dirs, 644 for files, 755 for writable logs)
- Configure cron job for cleanup: `php /path/to/cron/cleanup.php` every hour
- Disable display_errors in production (already handled if APP_ENV=production)
- Secure `/logs` and `/assets/uploads` with `.htaccess` deny all
- Regularly backup database