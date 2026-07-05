#!/bin/bash

# SSL Certificate Renewal Script
# This script renews SSL certificates and reloads Nginx

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  SSL Certificate Renewal${NC}"
echo -e "${GREEN}========================================${NC}"

# Check if certificates exist
if [ ! -d "ssl/certs/live" ]; then
    echo -e "${RED}No certificates found. Run ssl-setup.sh first.${NC}"
    exit 1
fi

# Renew certificates
echo -e "\n${YELLOW}Renewing SSL certificates...${NC}"
docker compose -f ssl/docker-compose.ssl.yml run --rm certbot renew

# Reload Nginx
echo -e "\n${YELLOW}Reloading Nginx...${NC}"
docker compose -f ssl/docker-compose.ssl.yml exec nginx nginx -s reload

echo -e "\n${GREEN}SSL certificates renewed successfully!${NC}"
echo -e "${GREEN}Next renewal check: 12 hours${NC}"
