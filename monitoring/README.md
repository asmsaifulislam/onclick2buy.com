# System & Memory Monitoring with Prometheus + Grafana

## Overview

This monitoring stack provides comprehensive system and memory monitoring for your VPS server using:
- **Prometheus** - Metrics collection and storage (30-day retention)
- **Grafana** - Visualization dashboards
- **Node Exporter** - Host system metrics (CPU, Memory, Disk, Network)
- **cAdvisor** - Docker container metrics
- **Alertmanager** - Alert routing and notifications

## Components

| Service | Port | Description |
|---------|------|-------------|
| Prometheus | 9090 | Metrics collection & storage |
| Grafana | 3000 | Dashboards & visualization |
| Node Exporter | 9100 | Host system metrics |
| cAdvisor | 8080 | Container metrics |
| Alertmanager | 9093 | Alert routing |

## Quick Start

### 1. Start Monitoring Stack

```bash
cd monitoring
docker compose -f docker-compose.monitoring.yml up -d
```

### 2. Access Services

- **Grafana**: http://localhost:3000
  - Username: `admin`
  - Password: `admin123`
- **Prometheus**: http://localhost:9090
- **Alertmanager**: http://localhost:9093

### 3. View Dashboard

1. Open Grafana at http://localhost:3000
2. Navigate to Dashboards → System
3. Click on "System & Memory Monitoring"

## Dashboard Panels

### System Overview
- **CPU Usage** - Real-time CPU utilization gauge
- **Memory Usage** - RAM utilization gauge
- **Disk Usage** - Root filesystem utilization gauge
- **System Load** - 15-minute load average

### CPU Details
- CPU usage over time (line chart)
- CPU modes breakdown (user, system, iowait, etc.)

### Memory Details
- Memory usage over time (total, used, buffers, cached)
- Swap memory usage

### Disk I/O
- Disk read/write rate (bytes/sec)
- Disk IOPS (operations/sec)

### Network
- Network traffic (bytes/sec)
- Network packets (packets/sec)

### Container Metrics
- Container memory usage per container
- Container CPU usage per container

### System Info
- Total memory
- Available memory
- Total disk space

## Alert Rules

The following alerts are pre-configured:

| Alert | Condition | Severity |
|-------|-----------|----------|
| HighCpuUsage | >80% for 5min | Warning |
| CriticalCpuUsage | >95% for 5min | Critical |
| HighMemoryUsage | >85% for 5min | Warning |
| CriticalMemoryUsage | >95% for 5min | Critical |
| HighDiskUsage | >85% | Warning |
| CriticalDiskUsage | >95% | Critical |
| HighNetworkErrors | >10 errors/sec | Warning |
| InstanceDown | >2min | Critical |
| HighSystemLoad | >2 for 15min | Warning |

## Configuring Alerts

Edit `prometheus/alertmanager.yml` to configure notification channels:

```yaml
receivers:
  - name: 'default'
    email_configs:
      - to: 'admin@example.com'
        from: 'alertmanager@example.com'
        smarthost: 'smtp.gmail.com:587'
        auth_username: 'alertmanager@example.com'
        auth_password: 'your-app-password'
        require_tls: true
```

## Production Deployment

### Security Considerations

1. **Change default credentials** in `docker-compose.monitoring.yml`:
   ```yaml
   environment:
     - GF_SECURITY_ADMIN_USER=your-username
     - GF_SECURITY_ADMIN_PASSWORD=your-strong-password
   ```

2. **Restrict access** - Use firewall rules to limit access to monitoring ports:
   ```bash
   # Allow only your IP
   ufw allow from YOUR_IP to any port 3000
   ufw allow from YOUR_IP to any port 9090
   ```

3. **Enable HTTPS** - Use a reverse proxy (Nginx) with SSL:
   ```nginx
   server {
       listen 443 ssl;
       server_name grafana.yourdomain.com;
       
       ssl_certificate /path/to/cert.pem;
       ssl_certificate_key /path/to/key.pem;
       
       location / {
           proxy_pass http://localhost:3000;
           proxy_set_header Host $host;
           proxy_set_header X-Real-IP $remote_addr;
       }
   }
   ```

### Data Persistence

Data is stored in Docker volumes:
- `prometheus_data` - 30 days of metrics
- `grafana_data` - Dashboard configurations

Backup these volumes regularly:
```bash
docker run --rm -v prometheus_data:/data -v $(pwd):/backup alpine tar czf /backup/prometheus_backup.tar.gz /data
docker run --rm -v grafana_data:/data -v $(pwd):/backup alpine tar czf /backup/grafana_backup.tar.gz /data
```

## Troubleshooting

### Check container status
```bash
docker compose -f docker-compose.monitoring.yml ps
```

### View logs
```bash
docker compose -f docker-compose.monitoring.yml logs -f prometheus
docker compose -f docker-compose.monitoring.yml logs -f grafana
```

### Test Prometheus targets
1. Open Prometheus UI: http://localhost:9090/targets
2. Verify all targets are "UP"

### Reset Grafana admin password
```bash
docker exec -it grafana grafana-cli admin reset-admin-password newpassword
```

## File Structure

```
monitoring/
├── docker-compose.monitoring.yml    # Main compose file
├── prometheus/
│   ├── prometheus.yml              # Prometheus configuration
│   ├── alert_rules.yml             # Alert rules
│   └── alertmanager.yml            # Alertmanager configuration
└── grafana/
    ├── provisioning/
    │   ├── datasources/
    │   │   └── prometheus.yml      # Prometheus datasource
    │   └── dashboards/
    │       └── default.yml         # Dashboard provisioning
    └── dashboards/
        └── system-monitoring.json  # Main dashboard
```

## Useful PromQL Queries

### CPU
```promql
# CPU usage percentage
100 - (avg by(instance) (rate(node_cpu_seconds_total{mode="idle"}[5m])) * 100)

# CPU by mode
rate(node_cpu_seconds_total[5m]) * 100
```

### Memory
```promql
# Memory usage percentage
(1 - (node_memory_MemAvailable_bytes / node_memory_MemTotal_bytes)) * 100

# Memory by category
node_memory_MemTotal_bytes
node_memory_MemAvailable_bytes
node_memory_Buffers_bytes
node_memory_Cached_bytes
```

### Disk
```promql
# Disk usage percentage
(1 - (node_filesystem_avail_bytes{mountpoint="/"} / node_filesystem_size_bytes{mountpoint="/"})) * 100

# Disk I/O rate
rate(node_disk_read_bytes_total[5m])
rate(node_disk_written_bytes_total[5m])
```

### Network
```promql
# Network traffic
rate(node_network_receive_bytes_total[5m]) * 8  # bits/sec
rate(node_network_transmit_bytes_total[5m]) * 8
```
