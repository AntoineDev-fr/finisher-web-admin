# finisher-web-admin (PHP 8.2, no framework)

Admin web interface to manage races with auth, CSRF, uploads, AJAX search, Leaflet map, email confirmation, and PDF export.

## Run & Demo
1. Import database schema:
```bash
mysql -u root -p < database/schema.sql
```
2. Configure `.env` (DB + SMTP):
```bash
copy .env.example .env
```
3. Install dependencies:
```bash
composer install
```
4. Start the server:
```bash
php -S localhost:8000 -t public
```
5. Login with the admin seed:
- Email: `admin@finisher.test`
- Password: `Password123!`
6. Create a race (map + photo) from `/admin/races/create`.
7. Check email delivery (ex: Mailtrap or your SMTP inbox).
8. Test AJAX search from `/admin/races`.
9. Export PDF from the admin detail page (`Export PDF`).
10. Public visitor pages are on `/races` and `/races/{id}`.
11. Images are served by the API using `API_PUBLIC_URL`.
