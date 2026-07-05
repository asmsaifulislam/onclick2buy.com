#!/bin/bash

# SSL/HTTPS Setup Script for Laravel
# Usage: ./ssl-setup.sh yourdomain.com email@example.com

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Check arguments
if [ $# -lt 2 ]; then
    echo -e "${RED}Usage: $0 <domain> <email>${NC}"
    echo -e "${YELLOW}Example: $0 example.com admin@example.com${NC}"
    exit 1
fi

DOMAIN=$1
EMAIL=$2

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  SSL/HTTPS Setup for $DOMAIN${NC}"
echo -e "${GREEN}========================================${NC}"

# Step 1: Update Nginx config with domain
echo -e "\n${YELLOW}Step 1: Updating Nginx configuration...${NC}"
sed -i "s/yourdomain.com/$DOMAIN/g" ssl/nginx/default.conf
echo -e "${GREEN}✓ Nginx configuration updated${NC}"

# Step 2: Create initial Nginx config without SSL (for Certbot challenge)
echo -e "\n${YELLOW}Step 2: Creating temporary Nginx config...${NC}"
cat > ssl/nginx/initial.conf << EOF
server {
    listen 80;
    listen [::]:80;
    server_name $DOMAIN www.$DOMAIN;

    location /.well-known/acme-challenge/ {
        root /var/www/certbot;
    }

    location / {
        return 200 'Setting up SSL...';
        add_header Content-Type text/plain;
    }
}
EOF

# Step 3: Start Nginx with initial config
echo -e "\n${YELLOW}Step 3: Starting Nginx...${NC}"
docker compose -f ssl/docker-compose.ssl.yml up -d nginx

# Step 4: Request SSL certificate
echo -e "\n${YELLOW}Step 4: Requesting SSL certificate from Let's Encrypt...${NC}"
docker compose -f ssl/docker-compose.ssl.yml run --rm certbot certonly \
    --webroot \
    --webroot-path=/var/www/certbot \
    --email "$EMAIL" \
    --agree-tos \
    --no-eff-email \
    -d "$DOMAIN" \
    -d "www.$DOMAIN"

# Step 5: Restore SSL Nginx config
echo -e "\n${YELLOW}Step 5: Restoring SSL Nginx configuration...${NC}"
rm ssl/nginx/initial.conf

# Step 6: Restart with SSL
echo -e "\n${YELLOW}Step 6: Restarting with SSL...${NC}"
docker compose -f ssl/docker-compose.ssl.yml restart nginx

echo -e "\n${GREEN}========================================${NC}"
echo -e "${GREEN}  SSL Setup Complete!${NC}"
echo -e "${GREEN}========================================${NC}"
echo -e "\n${GREEN}Your site is now available at:${NC}"
echo -e "  ${YELLOW}https://$DOMAIN${NC}"
echo -e "  ${YELLOW}https://www.$DOMAIN${NC}"

echo -e "\n${GREEN}Auto-renewal is configured.${NC}"
echo -e "${GREEN}Certificates will be renewed every 12 hours.${NC}"
