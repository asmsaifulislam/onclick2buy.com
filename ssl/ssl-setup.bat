@echo off
REM SSL/HTTPS Setup Script for Laravel (Windows)
REM Usage: ssl-setup.bat yourdomain.com email@example.com

setlocal enabledelayedexpansion

if "%~1"=="" (
    echo Usage: %0 ^<domain^> ^<email^>
    echo Example: %0 example.com admin@example.com
    exit /b 1
)

if "%~2"=="" (
    echo Usage: %0 ^<domain^> ^<email^>
    echo Example: %0 example.com admin@example.com
    exit /b 1
)

set DOMAIN=%~1
set EMAIL=%~2

echo ========================================
echo   SSL/HTTPS Setup for %DOMAIN%
echo ========================================

echo.
echo Step 1: Updating Nginx configuration...
powershell -Command "(Get-Content ssl\nginx\default.conf) -replace 'yourdomain.com', '%DOMAIN%' | Set-Content ssl\nginx\default.conf"
echo ✓ Nginx configuration updated

echo.
echo Step 2: Creating temporary Nginx config...
(
echo server {
echo     listen 80;
echo     listen [::]:80;
echo     server_name %DOMAIN% www.%DOMAIN%;
echo.
echo     location /.well-known/acme-challenge/ {
echo         root /var/www/certbot;
echo     }
echo.
echo     location / {
echo         return 200 'Setting up SSL...'^;
echo         add_header Content-Type text/plain^;
echo     }
echo }
) > ssl\nginx\initial.conf

echo.
echo Step 3: Starting Nginx...
docker compose -f ssl/docker-compose.ssl.yml up -d nginx

echo.
echo Step 4: Requesting SSL certificate from Let's Encrypt...
docker compose -f ssl/docker-compose.ssl.yml run --rm certbot certonly ^
    --webroot ^
    --webroot-path=/var/www/certbot ^
    --email "%EMAIL%" ^
    --agree-tos ^
    --no-eff-email ^
    -d "%DOMAIN%" ^
    -d "www.%DOMAIN%"

echo.
echo Step 5: Restoring SSL Nginx configuration...
del ssl\nginx\initial.conf

echo.
echo Step 6: Restarting with SSL...
docker compose -f ssl/docker-compose.ssl.yml restart nginx

echo.
echo ========================================
echo   SSL Setup Complete!
echo ========================================
echo.
echo Your site is now available at:
echo   https://%DOMAIN%
echo   https://www.%DOMAIN%
echo.
echo Auto-renewal is configured.
echo Certificates will be renewed every 12 hours.

endlocal
