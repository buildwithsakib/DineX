# Security Architecture

## Authentication
- Founder and Restaurant admin are isolated sessions.
- Passwords hashed with bcrypt (cost 12).
- Session IDs regenerated on login.
- CSRF tokens on all POST forms.

## Authorization
- Multi-tenant isolation: every query includes `restaurant_id`.
- Role-based access control (Owner/Manager/Cashier) with permissions.
- Feature entitlement checks (`restaurant_has_feature()`) for subscription-gated features.
- Founder endpoints verify founder auth; restaurant endpoints verify restaurant auth.

## Input Validation
- Server-side validation for all inputs.
- Prepared statements (PDO) to prevent SQL injection.
- Output escaping with `e()` to prevent XSS.

## Additional Protections
- Security headers (CSP, X-Frame-Options, etc.) in production.
- Rate limiting on login attempts.
- Secure file upload validation (extension, size).
- Session cookie HTTPOnly and SameSite Lax; Secure flag in production.