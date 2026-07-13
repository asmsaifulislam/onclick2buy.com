#!/bin/bash
sqlite3 /opt/nginx-proxy-manager/data/database.sqlite "INSERT INTO proxy_host (created_on, modified_on, owner_user_id, is_deleted, domain_names, forward_host, forward_port, access_list_id, certificate_id, ssl_forced, caching_enabled, block_exploits, advanced_config, meta, allow_websocket_upgrade, http2_support, forward_scheme, enabled, locations, hsts_enabled, hsts_subdomains, trust_forwarded_proto) VALUES (datetime('now'), datetime('now'), 1, 0, '[\"meet.onclik2buy.com\"]', '127.0.0.1', 8083, 0, 1, 1, 0, 1, '', '{}', 1, 1, 'http', 1, '[]', 0, 0, 0);"
echo "DB insert done: $?"
nginx -s reload 2>&1
echo "Nginx reloaded: $?"
