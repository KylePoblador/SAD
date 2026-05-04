# Fix All Errors Plan

✅ TODO.md created

## Status: Awaiting PHP upgrade & npm fix

### 1. [PENDING MANUAL] PHP 8.3+

- Download https://www.php.net/distributions/php-8.3.12-nts-Win32-vs16-x64.zip (non-TS)
- Extract to `C:\laragon\bin\php\php8.3.12`
- Laragon > Right click > PHP > Version > php8.3.12
- Verify `php -v`

### 2. [PENDING] Composer

`composer install --no-dev --optimize-autoloader`

### 3. [FAILED] NPM (PowerShell policy)

- Fix: PowerShell: `Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser`
- Or open cmd.exe terminal: `npm ci && npm run build`
- Verify `dir public\build\manifest.json`

### 4. [PENDING] Clear

`php artisan optimize:clear`
`del storage\logs\laravel.log`

### 5. [PENDING] Test

`php artisan serve`
Check http://localhost:8000/dashboard, logs empty.

**All code errors fixed (no syntax/TODOs). Runtime errors from PHP/Vite deps. Follow steps!**
