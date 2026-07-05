@echo off
REM SSL Certificate Renewal Script (Windows)

echo ========================================
echo   SSL Certificate Renewal
echo ========================================

if not exist "ssl\certs\live" (
    echo No certificates found. Run ssl-setup.bat first.
    exit /b 1
)

echo.
echo Renewing SSL certificates...
docker compose -f ssl/docker-compose.ssl.yml run --rm certbot renew

echo.
echo Reloading Nginx...
docker compose -f ssl/docker-compose.ssl.yml exec nginx nginx -s reload

echo.
echo ========================================
echo   SSL certificates renewed successfully!
echo   Next renewal check: 12 hours
echo ========================================
