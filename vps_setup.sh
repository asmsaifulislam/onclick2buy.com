#!/bin/bash
# ============================================
# Onclick2Buy VPS Server Setup Script
# For Ubuntu/Debian 20.04+ / 22.04+
# ============================================

set -e

echo "=========================================="
echo " Onclick2Buy Server Setup"
echo "=========================================="

# Update system
echo "[1/10] Updating system..."
apt update && apt upgrade -y

# Install PHP 8.3
echo "[2/10] Installing PHP 8.3..."
apt install -y software-properties-common
add-apt-repository -y ppa:ondrej/php
apt update
apt install -y php8.3 php8.3-cli php8.3-fpm php8.3-mbstring php8.3-xml php8.3-curl php8.3-sqlite3 php8.3-zip php8.3-bcmath php8.3-gd php8.3-intl php8.3-redis php8.3-fileinfo

# Install Nginx
echo "[3/10] Installing Nginx..."
apt install -y nginx

# Install Composer
echo "[4/10] Installing Composer..."
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php --install-dir=/usr/local/bin --filename=composer
rm composer-setup.php

# Install Node.js 20
echo "[5/10] Installing Node.js 20..."
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt install -y nodejs

# Install SQLite3
echo "[6/10] Installing SQLite3..."
apt install -y sqlite3

# Install Certbot for SSL
echo "[7/10] Installing Certbot..."
apt install -y certbot python3-certbot-nginx

# Create project directory
echo "[8/10] Setting up project..."
mkdir -p /var/www/onclick2buy
chown -R www-data:www-data /var/www/onclick2buy

# Create database directory
mkdir -p /var/www/onclick2buy/database
touch /var/www/onclick2buy/database/database.sqlite
chown -R www-data:www-data /var/www/onclick2buy/database

# Setup Nginx config
echo "[9/10] Configuring Nginx..."
cat > /etc/nginx/sites-available/onclick2buy << 'NGINX_CONF'
server {
    listen 80;
    server_name _;
    root /var/www/onclick2buy/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realroot$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
NGINX_CONF

ln -sf /etc/nginx/sites-available/onclick2buy /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default
nginx -t
systemctl restart nginx
systemctl restart php8.3-fpm

# Set permissions
echo "[10/10] Setting permissions..."
chmod -R 755 /var/www/onclick2buy
chown -R www-data:www-data /var/www/onclick2buy/storage
chown -R www-data:www-data /var/www/onclick2buy/bootstrap/cache

# Setup firewall
ufw allow 'Nginx Full'
ufw allow OpenSSH
echo "y" | ufw enable

echo ""
echo "=========================================="
echo " Setup Complete!"
echo "=========================================="
echo ""
echo " Next steps:"
echo " 1. Upload your project to /var/www/onclick2buy/"
echo " 2. Run: cd /var/www/onclick2buy"
echo " 3. Run: composer install --no-dev --optimize-autoloader"
echo " 4. Run: cp .env.example .env"
echo " 5. Run: php artisan key:generate"
echo " 6. Run: php artisan migrate --force"
echo " 7. Run: php artisan db:seed"
echo " 8. Run: npm install && npm run build"
echo " 9. Run: php artisan storage:link"
echo " 10. Run: chown -R www-data:www-data storage bootstrap/cache"
echo ""
echo " PHP: $(php -v | head -1)"
echo " Node: $(node -v)"
echo " Composer: $(composer --version)"
echo ""
