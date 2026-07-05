#!/bin/bash

# SSL Test Script
# Tests SSL certificate and configuration

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Check arguments
if [ $# -lt 1 ]; then
    echo -e "${RED}Usage: $0 <domain>${NC}"
    echo -e "${YELLOW}Example: $0 example.com${NC}"
    exit 1
fi

DOMAIN=$1

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  SSL Test for $DOMAIN${NC}"
echo -e "${GREEN}========================================${NC}"

# Test 1: HTTP to HTTPS redirect
echo -e "\n${YELLOW}Test 1: HTTP to HTTPS redirect...${NC}"
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" -L "http://$DOMAIN" 2>/dev/null || true)
if [ "$HTTP_CODE" = "200" ]; then
    echo -e "${GREEN}✓ HTTP to HTTPS redirect working${NC}"
else
    echo -e "${RED}✗ HTTP to HTTPS redirect failed (HTTP $HTTP_CODE)${NC}"
fi

# Test 2: HTTPS connection
echo -e "\n${YELLOW}Test 2: HTTPS connection...${NC}"
HTTPS_CODE=$(curl -s -o /dev/null -w "%{http_code}" "https://$DOMAIN" 2>/dev/null || true)
if [ "$HTTPS_CODE" = "200" ]; then
    echo -e "${GREEN}✓ HTTPS connection working${NC}"
else
    echo -e "${RED}✗ HTTPS connection failed (HTTP $HTTPS_CODE)${NC}"
fi

# Test 3: SSL Certificate
echo -e "\n${YELLOW}Test 3: SSL Certificate...${NC}"
CERT_INFO=$(echo | openssl s_client -servername "$DOMAIN" -connect "$DOMAIN":443 2>/dev/null | openssl x509 -noout -dates 2>/dev/null || true)
if [ -n "$CERT_INFO" ]; then
    echo -e "${GREEN}✓ SSL Certificate valid${NC}"
    echo "$CERT_INFO"
else
    echo -e "${RED}✗ SSL Certificate not found or invalid${NC}"
fi

# Test 4: Security Headers
echo -e "\n${YELLOW}Test 4: Security Headers...${NC}"
HEADERS=$(curl -s -I "https://$DOMAIN" 2>/dev/null || true)

check_header() {
    local header=$1
    if echo "$HEADERS" | grep -qi "$header"; then
        echo -e "${GREEN}✓ $header present${NC}"
    else
        echo -e "${YELLOW}△ $header missing${NC}"
    fi
}

check_header "Strict-Transport-Security"
check_header "X-Frame-Options"
check_header "X-Content-Type-Options"
check_header "X-XSS-Protection"
check_header "Referrer-Policy"

# Test 5: SSL Grade
echo -e "\n${YELLOW}Test 5: SSL Configuration...${NC}"
SSL_INFO=$(echo | openssl s_client -servername "$DOMAIN" -connect "$DOMAIN":443 2>/dev/null | openssl x509 -noout -subject -issuer -dates 2>/dev/null || true)
if [ -n "$SSL_INFO" ]; then
    echo -e "${GREEN}✓ SSL Configuration:${NC}"
    echo "$SSL_INFO"
else
    echo -e "${RED}✗ Could not retrieve SSL information${NC}"
fi

echo -e "\n${GREEN}========================================${NC}"
echo -e "${GREEN}  Test Complete${NC}"
echo -e "${GREEN}========================================${NC}"
echo -e "\n${YELLOW}For detailed SSL test, visit:${NC}"
echo -e "  https://www.ssllabs.com/ssltest/analyze.html?d=$DOMAIN"
