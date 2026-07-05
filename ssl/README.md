# SSL/HTTPS Configuration Guide

## Overview

This guide covers SSL/HTTPS setup for your Laravel application using Let's Encrypt (free SSL certificates) with automatic renewal.

## Prerequisites

- Domain name pointing to your VPS server
- Ports 80 and 443 open in firewall
- Docker and Docker Compose installed

## Quick Start

### Linux/Mac

```bash
chmod +x ssl/ssl-setup.sh ssl/renew-ssl.sh
./ssl/ssl-setup.sh yourdomain.com your@email.com
```

### Windows

```cmd
ssl\ssl-setup.bat yourdomain.com your@email.com
```

## Manual Setup

### Step 1: Update Domain in Nginx Config

Edit `ssl/nginx/default.conf` and replace `yourdomain.com` with your actual domain:

```bash
sed -i 's/yourdomain.com/yourdomain.com/g' ssl/nginx/default.conf
```

### Step 2: Start Nginx (HTTP Only)

```bash
docker compose -f ssl/docker-compose.ssl.yml up -d nginx
```

### Step 3: Get SSL Certificate

```bash
docker compose -f ssl/docker-compose.ssl.yml run --rm certbot certonly \
    --webroot \
    --webroot-path=/var/www/certbot \
    --email your@email.com \
    --agree-tos \
    --no-eff-email \
    -d yourdomain.com \
    -d www.yourdomain.com
```

### Step 4: Restart with SSL

```bash
docker compose -f ssl/docker-compose.ssl.yml restart nginx
```

## File Structure

```
ssl/
├── docker-compose.ssl.yml    # Docker compose for SSL services
├── ssl-setup.sh              # Linux/Mac setup script
├── ssl-setup.bat             # Windows setup script
├── renew-ssl.sh              # Linux/Mac renewal script
├── renew-ssl.bat             # Windows renewal script
├── nginx/
│   └── default.conf          # Nginx config with SSL
├── certs/                    # SSL certificates (auto-generated)
└── www/                      # Certbot webroot
```

## Auto-Renewal

Let's Encrypt certificates expire every 90 days. This setup includes automatic renewal:

- **Docker container**: Runs renewal check every 12 hours
- **Manual renewal**: Run `./ssl/renew-ssl.sh` or `ssl\renew-ssl.bat`

## Production Deployment

### 1. Firewall Rules

```bash
# Ubuntu/Debian
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw reload

# CentOS/RHEL
sudo firewall-cmd --permanent --add-port=80/tcp
sudo firewall-cmd --permanent --add-port=443/tcp
sudo firewall-cmd --reload
```

### 2. Update Laravel for HTTPS

Add to `.env`:

```env
APP_URL=https://yourdomain.com
SESSION_SECURE_COOKIE=true
```

### 3. Start Production Stack

```bash
docker compose -f ssl/docker-compose.ssl.yml up -d
```

## SSL Security Features

### Enabled Protocols
- TLS 1.2
- TLS 1.3

### Security Headers
- `Strict-Transport-Security` (HSTS) - 1 year with includeSubDomains
- `X-Frame-Options` - SAMEORIGIN
- `X-Content-Type-Options` - nosniff
- `X-XSS-Protection` - 1; mode=block
- `Referrer-Policy` - strict-origin-when-cross-origin
- `Content-Security-Policy` - Configurable

### OCSP Stapling
- Enabled for faster SSL handshakes
- Resolvers: Google DNS (8.8.8.8, 8.8.4.4)

### SSL Session
- Session timeout: 1 day
- Session cache: 50MB shared
- Session tickets: Disabled (for security)

## Troubleshooting

### Check Certificate Status

```bash
docker compose -f ssl/docker-compose.ssl.yml run --rm certbot certificates
```

### Force Certificate Renewal

```bash
docker compose -f ssl/docker-compose.ssl.yml run --rm certbot renew --force-renewal
```

### View Nginx Logs

```bash
docker compose -f ssl/docker-compose.ssl.yml logs nginx
```

### Test SSL Configuration

Visit: https://www.ssllabs.com/ssltest/

### Common Issues

#### Certificate Not Found
```bash
# Ensure certs directory exists
ls -la ssl/certs/live/yourdomain.com/
```

#### Nginx Won't Start
```bash
# Check if ports are in use
sudo lsof -i :80
sudo lsof -i :443

# Kill conflicting processes
sudo fuser -k 80/tcp
sudo fuser -k 443/tcp
```

#### Certbot Fails
```bash
# Ensure domain points to server
dig yourdomain.com

# Ensure port 80 is accessible
curl -I http://yourdomain.com
```

## Self-Signed Certificate (Development)

For local development, use self-signed certificates:

```bash
# Generate self-signed certificate
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout ssl/certs/selfsigned.key \
    -out ssl/certs/selfsigned.crt \
    -subj "/C=US/ST=State/L=City/O=Organization/CN=localhost"

# Update nginx/default.conf to use self-signed certs
# ssl_certificate /etc/letsencrypt/selfsigned.crt;
# ssl_certificate_key /etc/letsencrypt/selfsigned.key;
```

## Let's Encrypt Rate Limits

- **Certificates per domain**: 50 per week
- **Duplicate certificates**: 5 per week
- **Pending validations**: 10 per account

## References

- [Let's Encrypt Documentation](https://letsencrypt.org/docs/)
- [Certbot Documentation](https://certbot.eff.org/docs/)
- [Mozilla SSL Configuration Generator](https://ssl-config.mozilla.org/)
- [SSL Labs Test](https://www.ssllabs.com/ssltest/)
