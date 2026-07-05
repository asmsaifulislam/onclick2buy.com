<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class SystemStatusController extends Controller
{
    public function index()
    {
        $data = [
            'services' => $this->getServicesStatus(),
            'system' => $this->getSystemInfo(),
            'connections' => $this->getConnections(),
            'config' => $this->getConfigInfo(),
            'promotions' => $this->getAiPromotions(),
        ];
        return view('admin.system-status', $data);
    }

    public function api()
    {
        return response()->json([
            'services' => $this->getServicesStatus(),
            'system' => $this->getSystemInfo(),
        ]);
    }

    public function createPromotion(Request $request)
    {
        $request->validate([
            'type' => 'required|in:flash_sale,discount,combo,bundle,seasonal,clearance',
            'products' => 'required|array|min:1',
            'title' => 'required|string|max:255',
            'discount' => 'required|numeric|min:1|max:90',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        $promo = $this->generatePromotion($request->all());

        return response()->json([
            'success' => true,
            'message' => 'AI Promotion created successfully',
            'promotion' => $promo,
        ]);
    }

    private function getServicesStatus()
    {
        $services = [];

        // Database
        try {
            \DB::connection()->getPdo();
            $tables = \DB::select('SHOW TABLES');
            $services['database'] = [
                'name' => 'MySQL Database',
                'status' => 'healthy',
                'type' => 'Core',
                'ip' => Config::get('database.connections.mysql.host', '127.0.0.1'),
                'port' => Config::get('database.connections.mysql.port', '3306'),
                'path' => Config::get('database.connections.mysql.database'),
                'details' => count($tables) . ' tables',
            ];
        } catch (\Exception $e) {
            $services['database'] = [
                'name' => 'MySQL Database',
                'status' => 'error',
                'type' => 'Core',
                'ip' => Config::get('database.connections.mysql.host', '127.0.0.1'),
                'port' => Config::get('database.connections.mysql.port', '3306'),
                'path' => Config::get('database.connections.mysql.database'),
                'details' => 'Connection failed',
            ];
        }

        // Redis
        try {
            \Cache::store('redis')->put('_health', 1, 10);
            $ok = \Cache::store('redis')->get('_health') === 1;
            $services['redis'] = [
                'name' => 'Redis Cache',
                'status' => $ok ? 'healthy' : 'error',
                'type' => 'Cache',
                'ip' => Config::get('database.redis.default.host', '127.0.0.1'),
                'port' => Config::get('database.redis.default.port', '6379'),
                'path' => '/',
                'details' => $ok ? 'Read/Write OK' : 'Read/Write failed',
            ];
        } catch (\Exception $e) {
            $services['redis'] = [
                'name' => 'Redis Cache',
                'status' => 'offline',
                'type' => 'Cache',
                'ip' => '127.0.0.1',
                'port' => '6379',
                'path' => '/',
                'details' => 'Not reachable',
            ];
        }

        // Google Analytics
        $gaId = Config::get('app.GOOGLE_ANALYTICS_ID', '');
        $services['analytics'] = [
            'name' => 'Google Analytics',
            'status' => !empty($gaId) ? 'healthy' : 'warning',
            'type' => 'Analytics',
            'ip' => 'google.com',
            'port' => '443',
            'path' => '/analytics/collect',
            'details' => !empty($gaId) ? "ID: {$gaId}" : 'Not configured',
        ];

        // Prometheus
        $services['prometheus'] = $this->checkPort('Prometheus Monitoring', 'Monitoring', '127.0.0.1', '9090', '/api/v1/query?up');

        // Grafana
        $services['grafana'] = $this->checkPort('Grafana Dashboard', 'Monitoring', '127.0.0.1', '3000', '/api/health');

        // Mautic
        $mauticUrl = Config::get('mautic.url', 'http://localhost:8080');
        $services['mautic'] = [
            'name' => 'Mautic Marketing',
            'status' => $this->checkUrl($mauticUrl) ? 'healthy' : 'offline',
            'type' => 'Marketing',
            'ip' => parse_url($mauticUrl, PHP_URL_HOST) ?: 'localhost',
            'port' => parse_url($mauticUrl, PHP_URL_PORT) ?: '80',
            'path' => parse_url($mauticUrl, PHP_URL_PATH) ?: '/',
            'details' => $mauticUrl,
        ];

        // ERPNext
        $erpUrl = Config::get('erpnext.url', 'http://localhost:8000');
        $services['erpnext'] = [
            'name' => 'ERPNext ERP',
            'status' => $this->checkUrl($erpUrl) ? 'healthy' : 'offline',
            'type' => 'ERP',
            'ip' => parse_url($erpUrl, PHP_URL_HOST) ?: 'localhost',
            'port' => parse_url($erpUrl, PHP_URL_PORT) ?: '80',
            'path' => parse_url($erpUrl, PHP_URL_PATH) ?: '/',
            'details' => $erpUrl,
        ];

        // SSL
        $services['ssl'] = [
            'name' => 'SSL/HTTPS',
            'status' => request()->isSecure() ? 'healthy' : 'warning',
            'type' => 'Security',
            'ip' => request()->getHost(),
            'port' => request()->isSecure() ? '443' : '80',
            'path' => '/',
            'details' => request()->isSecure() ? 'Encrypted' : 'Not encrypted',
        ];

        // AI Agents
        $aiProvider = Config::get('ai-agents.provider', 'botpress');
        $aiPort = $aiProvider === 'rasa' ? '5005' : '3000';
        $services['ai_agents'] = [
            'name' => 'AI Chat Agents',
            'status' => $this->checkPortDirect('127.0.0.1', $aiPort) ? 'healthy' : 'offline',
            'type' => 'AI',
            'ip' => '127.0.0.1',
            'port' => $aiPort,
            'path' => '/',
            'details' => ucfirst($aiProvider) . " on port {$aiPort}",
        ];

        // Recommendations
        $services['recommendations'] = $this->checkPort('Recommendation Engine', 'AI', '127.0.0.1', '8001', '/health');

        // Nginx
        $services['nginx'] = [
            'name' => 'Nginx Web Server',
            'status' => 'healthy',
            'type' => 'Server',
            'ip' => '0.0.0.0',
            'port' => request()->isSecure() ? '443' : '80',
            'path' => '/etc/nginx/',
            'details' => 'Serving requests',
        ];

        return $services;
    }

    private function checkPort($name, $type, $ip, $port, $path)
    {
        try {
            $response = Http::timeout(2)->get("http://{$ip}:{$port}{$path}");
            return [
                'name' => $name,
                'status' => $response->successful() ? 'healthy' : 'error',
                'type' => $type,
                'ip' => $ip,
                'port' => $port,
                'path' => $path,
                'details' => $response->successful() ? 'Responding' : 'Error: ' . $response->status(),
            ];
        } catch (\Exception $e) {
            return [
                'name' => $name,
                'status' => 'offline',
                'type' => $type,
                'ip' => $ip,
                'port' => $port,
                'path' => $path,
                'details' => 'Not reachable',
            ];
        }
    }

    private function checkPortDirect($ip, $port)
    {
        $fp = @fsockopen($ip, $port, $errno, $errstr, 2);
        if ($fp) {
            fclose($fp);
            return true;
        }
        return false;
    }

    private function checkUrl($url)
    {
        try {
            $response = Http::timeout(3)->get($url);
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    private function getSystemInfo()
    {
        $cpu = $this->getCpuUsage();
        $memory = $this->getMemoryUsage();
        $disk = $this->getDiskUsage();
        $load = sys_getloadavg();

        return [
            'os' => PHP_OS,
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'uptime' => $this->getUptime(),
            'cpu' => [
                'cores' => $cpu['cores'],
                'usage' => $cpu['usage'],
                'model' => $cpu['model'],
            ],
            'memory' => [
                'total' => $memory['total'],
                'used' => $memory['used'],
                'free' => $memory['free'],
                'usage_percent' => $memory['usage_percent'],
            ],
            'disk' => [
                'total' => $disk['total'],
                'used' => $disk['used'],
                'free' => $disk['free'],
                'usage_percent' => $disk['usage_percent'],
            ],
            'load' => [
                '1min' => round($load[0], 2),
                '5min' => round($load[1], 2),
                '15min' => round($load[2], 2),
            ],
        ];
    }

    private function getCpuUsage()
    {
        $cores = trim(shell_exec('nproc') ?? '1');
        $model = trim(shell_exec('cat /proc/cpuinfo | grep "model name" | head -1 | cut -d: -f2') ?? 'Unknown');
        
        $usage = 0;
        if (PHP_OS_FAMILY === 'Linux') {
            $stat1 = shell_exec('cat /proc/stat | head -1');
            sleep(0.1);
            $stat2 = shell_exec('cat /proc/stat | head -1');
            
            if ($stat1 && $stat2) {
                preg_match_all('/(\d+)/', $stat1, $m1);
                preg_match_all('/(\d+)/', $stat2, $m2);
                
                $idle1 = $m1[1][3] ?? 0;
                $idle2 = $m2[1][3] ?? 0;
                $total1 = array_sum($m1[1]);
                $total2 = array_sum($m2[1]);
                
                if ($total2 - $total1 > 0) {
                    $usage = round(100 - (($idle2 - $idle1) / ($total2 - $total1) * 100), 1);
                }
            }
        } elseif (PHP_OS_FAMILY === 'Windows') {
            $output = shell_exec('wmic cpu get loadpercentage /value');
            preg_match('/LoadPercentage=(\d+)/', $output ?? '', $matches);
            $usage = $matches[1] ?? 0;
        }

        return [
            'cores' => $cores,
            'usage' => $usage,
            'model' => trim($model) ?: 'Unknown CPU',
        ];
    }

    private function getMemoryUsage()
    {
        if (PHP_OS_FAMILY === 'Linux') {
            $meminfo = file_get_contents('/proc/meminfo');
            preg_match('/MemTotal:\s+(\d+)/', $meminfo, $total);
            preg_match('/MemAvailable:\s+(\d+)/', $meminfo, $available);
            
            $totalKB = $total[1] ?? 0;
            $availableKB = $available[1] ?? 0;
            $usedKB = $totalKB - $availableKB;
            
            return [
                'total' => round($totalKB / 1048576, 2) . ' GB',
                'used' => round($usedKB / 1048576, 2) . ' GB',
                'free' => round($availableKB / 1048576, 2) . ' GB',
                'usage_percent' => $totalKB > 0 ? round(($usedKB / $totalKB) * 100, 1) : 0,
            ];
        } else {
            $total = round(memory_get_usage(true) / 1073741824, 2);
            $used = round(memory_get_usage() / 1073741824, 2);
            return [
                'total' => $total . ' GB',
                'used' => $used . ' GB',
                'free' => round($total - $used, 2) . ' GB',
                'usage_percent' => $total > 0 ? round(($used / $total) * 100, 1) : 0,
            ];
        }
    }

    private function getDiskUsage()
    {
        $total = round(disk_total_space('/') / 1073741824, 2);
        $free = round(disk_free_space('/') / 1073741824, 2);
        $used = round($total - $free, 2);
        
        return [
            'total' => $total . ' GB',
            'used' => $used . ' GB',
            'free' => $free . ' GB',
            'usage_percent' => $total > 0 ? round(($used / $total) * 100, 1) : 0,
        ];
    }

    private function getUptime()
    {
        if (PHP_OS_FAMILY === 'Linux') {
            $uptime = trim(shell_exec('uptime -p') ?? '');
            return $uptime ?: 'Unknown';
        }
        return 'N/A (Windows)';
    }

    private function getConnections()
    {
        $connections = [];
        
        // Database connections
        $connections[] = [
            'service' => 'MySQL',
            'type' => 'Database',
            'host' => Config::get('database.connections.mysql.host'),
            'port' => Config::get('database.connections.mysql.port'),
            'database' => Config::get('database.connections.mysql.database'),
            'username' => Config::get('database.connections.mysql.username'),
            'status' => $this->testDbConnection(),
        ];

        // Redis
        $connections[] = [
            'service' => 'Redis',
            'type' => 'Cache',
            'host' => Config::get('database.redis.default.host', '127.0.0.1'),
            'port' => Config::get('database.redis.default.port', '6379'),
            'database' => Config::get('database.redis.default.database', '0'),
            'username' => 'default',
            'status' => $this->testRedisConnection(),
        ];

        // Mautic
        $connections[] = [
            'service' => 'Mautic',
            'type' => 'Marketing',
            'host' => Config::get('mautic.url', 'http://localhost:8080'),
            'port' => '8080',
            'database' => 'mautic_db',
            'username' => Config::get('mautic.username', 'admin'),
            'status' => $this->checkUrl(Config::get('mautic.url', 'http://localhost:8080')),
        ];

        // ERPNext
        $connections[] = [
            'service' => 'ERPNext',
            'type' => 'ERP',
            'host' => Config::get('erpnext.url', 'http://localhost:8000'),
            'port' => '8000',
            'database' => Config::get('erpnext.site', 'localhost'),
            'username' => Config::get('erpnext.username', 'Administrator'),
            'status' => $this->checkUrl(Config::get('erpnext.url', 'http://localhost:8000')),
        ];

        return $connections;
    }

    private function testDbConnection()
    {
        try {
            \DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function testRedisConnection()
    {
        try {
            \Cache::store('redis')->put('_test', 1, 5);
            return \Cache::store('redis')->get('_test') === 1;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function getConfigInfo()
    {
        $envPath = base_path('.env');
        $configPath = config_path();
        $publicPath = public_path();
        $storagePath = storage_path();

        return [
            'env_file' => [
                'path' => $envPath,
                'exists' => File::exists($envPath),
                'readable' => File::isReadable($envPath),
                'writable' => File::isWritable($envPath),
                'size' => File::exists($envPath) ? round(File::size($envPath) / 1024, 2) . ' KB' : 'N/A',
            ],
            'config_dir' => [
                'path' => $configPath,
                'exists' => is_dir($configPath),
                'files' => count(glob($configPath . '/*.php')),
            ],
            'public_dir' => [
                'path' => $publicPath,
                'exists' => is_dir($publicPath),
                'writable' => is_writable($publicPath),
            ],
            'storage_dir' => [
                'path' => $storagePath,
                'exists' => is_dir($storagePath),
                'writable' => is_writable($storagePath),
                'cache_size' => $this->getDirSize($storagePath . '/framework/cache'),
                'log_size' => $this->getDirSize($storagePath . '/logs'),
            ],
            'app_url' => Config::get('app.url', 'Not set'),
            'app_env' => Config::get('app.env', 'production'),
            'app_debug' => Config::get('app.debug', false),
            'timezone' => Config::get('app.timezone', 'UTC'),
            'key_path' => base_path('storage/logs'),
        ];
    }

    private function getDirSize($path)
    {
        if (!is_dir($path)) return '0 B';
        
        $size = 0;
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path)) as $file) {
            $size += $file->getSize();
        }
        
        if ($size > 1073741824) return round($size / 1073741824, 2) . ' GB';
        if ($size > 1048576) return round($size / 1048576, 2) . ' MB';
        if ($size > 1024) return round($size / 1024, 2) . ' KB';
        return $size . ' B';
    }

    private function getAiPromotions()
    {
        return Cache::get('ai_promotions', [
            [
                'id' => 1,
                'title' => 'Summer Flash Sale',
                'type' => 'flash_sale',
                'discount' => 30,
                'products' => [1, 2, 3],
                'status' => 'active',
                'start_date' => now()->subDays(2)->toDateString(),
                'end_date' => now()->addDays(5)->toDateString(),
                'created_at' => now()->subDays(2)->toDateTimeString(),
            ],
            [
                'id' => 2,
                'title' => 'Electronics Bundle Deal',
                'type' => 'combo',
                'discount' => 20,
                'products' => [4, 5],
                'status' => 'scheduled',
                'start_date' => now()->addDays(1)->toDateString(),
                'end_date' => now()->addDays(7)->toDateString(),
                'created_at' => now()->subHours(3)->toDateTimeString(),
            ],
        ]);
    }

    private function generatePromotion($data)
    {
        $promotions = Cache::get('ai_promotions', []);
        
        $promo = [
            'id' => count($promotions) + 1,
            'title' => $data['title'],
            'type' => $data['type'],
            'discount' => $data['discount'],
            'products' => $data['products'],
            'status' => 'active',
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'created_at' => now()->toDateTimeString(),
            'ai_suggestion' => $this->getAiSuggestion($data['type'], $data['discount']),
        ];
        
        $promotions[] = $promo;
        Cache::put('ai_promotions', $promotions, now()->addDays(30));
        
        return $promo;
    }

    private function getAiSuggestion($type, $discount)
    {
        $suggestions = [
            'flash_sale' => "Flash sales with {$discount}% off typically see 2-3x conversion rates. Consider targeting cart abandoners and email subscribers for maximum impact.",
            'discount' => "A {$discount}% discount is competitive. Pair with free shipping for 15-20% higher AOV.",
            'combo' => "Bundle deals increase average order value by 30%. Recommend including complementary products.",
            'bundle' => "Product bundles reduce decision fatigue. Suggest 3-4 related items at {$discount}% off.",
            'seasonal' => "Seasonal promotions should start 2-3 weeks before the event for best engagement.",
            'clearance' => "Clearance sales with {$discount}% off work best for inventory over 90 days old.",
        ];
        return $suggestions[$type] ?? "Promotion created with {$discount}% discount.";
    }
}
