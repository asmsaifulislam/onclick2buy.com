<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ServiceHealthController extends Controller
{
    public function index()
    {
        $services = $this->checkAllServices();
        return view('admin.service-health', compact('services'));
    }

    public function checkAll()
    {
        $services = $this->checkAllServices();
        return response()->json($services);
    }

    public function testService($service)
    {
        $result = match($service) {
            'analytics' => $this->testAnalytics(),
            'monitoring' => $this->testMonitoring(),
            'mautic' => $this->testMautic(),
            'erpnext' => $this->testErpNext(),
            'ssl' => $this->testSSL(),
            'ai_agents' => $this->testAiAgents(),
            'recommendations' => $this->testRecommendations(),
            'database' => $this->testDatabase(),
            'redis' => $this->testRedis(),
            default => ['status' => 'unknown', 'message' => 'Service not found']
        };

        return response()->json($result);
    }

    private function checkAllServices()
    {
        return [
            'database' => [
                'name' => 'Database (MySQL)',
                'status' => $this->checkDatabase(),
                'description' => 'Primary data storage for products, orders, users',
                'critical' => true,
            ],
            'cache' => [
                'name' => 'Cache (Redis)',
                'status' => $this->checkRedis(),
                'description' => 'Session & cache storage for fast page loads',
                'critical' => false,
            ],
            'analytics' => [
                'name' => 'Google Analytics (GA4)',
                'status' => $this->checkAnalytics(),
                'description' => 'Visitor tracking, conversion analytics',
                'critical' => false,
            ],
            'monitoring' => [
                'name' => 'Prometheus + Grafana',
                'status' => $this->checkMonitoring(),
                'description' => 'Server metrics, alerts, dashboards',
                'critical' => false,
            ],
            'mautic' => [
                'name' => 'Mautic Marketing',
                'status' => $this->checkMautic(),
                'description' => 'Email campaigns, lead scoring, automation',
                'critical' => false,
            ],
            'erpnext' => [
                'name' => 'ERPNext ERP',
                'status' => $this->checkErpNext(),
                'description' => 'Inventory, accounting, stock management',
                'critical' => false,
            ],
            'ssl' => [
                'name' => 'SSL/HTTPS',
                'status' => $this->checkSSL(),
                'description' => 'Encrypted connections, security',
                'critical' => true,
            ],
            'ai_agents' => [
                'name' => 'AI Chat Agents',
                'status' => $this->checkAiAgents(),
                'description' => 'Customer support chatbot (Rasa/Botpress)',
                'critical' => false,
            ],
            'recommendations' => [
                'name' => 'Recommendation Engine',
                'status' => $this->checkRecommendations(),
                'description' => 'AI product suggestions (Surprise SVD)',
                'critical' => false,
            ],
        ];
    }

    private function checkDatabase()
    {
        try {
            DB::connection()->getPdo();
            return 'healthy';
        } catch (\Exception $e) {
            return 'error';
        }
    }

    private function checkRedis()
    {
        try {
            Cache::store('redis')->put('health_check', true, 10);
            return Cache::store('redis')->get('health_check') ? 'healthy' : 'error';
        } catch (\Exception $e) {
            return 'warning';
        }
    }

    private function checkAnalytics()
    {
        $id = Config::get('app.GOOGLE_ANALYTICS_ID');
        return !empty($id) ? 'healthy' : 'warning';
    }

    private function checkMonitoring()
    {
        try {
            $response = Http::timeout(2)->get('http://localhost:9090/-/healthy');
            return $response->successful() ? 'healthy' : 'error';
        } catch (\Exception $e) {
            return 'offline';
        }
    }

    private function checkMautic()
    {
        try {
            $url = Config::get('mautic.url', 'http://127.0.0.1:8090');
            $response = Http::timeout(3)->get($url);
            return $response->successful() ? 'healthy' : 'error';
        } catch (\Exception $e) {
            return 'offline';
        }
    }

    private function checkErpNext()
    {
        try {
            $url = Config::get('erpnext.url', 'http://localhost:8000');
            $response = Http::timeout(3)->get($url);
            return $response->successful() ? 'healthy' : 'error';
        } catch (\Exception $e) {
            return 'offline';
        }
    }

    private function checkSSL()
    {
        return request()->isSecure() ? 'healthy' : 'warning';
    }

    private function checkAiAgents()
    {
        try {
            $provider = Config::get('ai-agents.provider', 'botpress');
            $port = $provider === 'rasa' ? 5005 : 3000;
            $response = Http::timeout(2)->get("http://localhost:{$port}");
            return $response->successful() ? 'healthy' : 'error';
        } catch (\Exception $e) {
            return 'offline';
        }
    }

    private function checkRecommendations()
    {
        try {
            $response = Http::timeout(2)->get('http://localhost:8001/health');
            return $response->successful() ? 'healthy' : 'error';
        } catch (\Exception $e) {
            return 'offline';
        }
    }

    private function testAnalytics()
    {
        $id = Config::get('app.GOOGLE_ANALYTICS_ID');
        if (empty($id)) {
            return ['status' => 'warning', 'message' => 'No GA4 ID configured in .env (GOOGLE_ANALYTICS_ID)', 'fix' => 'Add your Google Analytics 4 Measurement ID to .env'];
        }
        return ['status' => 'healthy', 'message' => "GA4 ID configured: {$id}", 'detail' => 'Tracking script is embedded in app.blade.php layout'];
    }

    private function testMonitoring()
    {
        try {
            $response = Http::timeout(2)->get('http://localhost:9090/api/v1/query?up');
            return ['status' => 'healthy', 'message' => 'Prometheus is running on port 9090'];
        } catch (\Exception $e) {
            return ['status' => 'offline', 'message' => 'Prometheus not reachable on port 9090', 'fix' => 'Run: docker-compose -f monitoring/docker-compose.monitoring.yml up -d'];
        }
    }

    private function testMautic()
    {
        try {
            $url = Config::get('mautic.url', 'http://127.0.0.1:8090');
            $response = Http::timeout(3)->get($url);
            if ($response->successful()) {
                return ['status' => 'healthy', 'message' => "Mautic is running at {$url}"];
            }
            return ['status' => 'error', 'message' => 'Mautic returned error status'];
        } catch (\Exception $e) {
            return ['status' => 'offline', 'message' => 'Mautic not reachable', 'fix' => 'Run: docker-compose -f mautic/docker-compose.mautic.yml up -d'];
        }
    }

    private function testErpNext()
    {
        try {
            $url = Config::get('erpnext.url', 'http://localhost:8000');
            $response = Http::timeout(3)->get($url);
            if ($response->successful()) {
                return ['status' => 'healthy', 'message' => "ERPNext is running at {$url}"];
            }
            return ['status' => 'error', 'message' => 'ERPNext returned error status'];
        } catch (\Exception $e) {
            return ['status' => 'offline', 'message' => 'ERPNext not reachable', 'fix' => 'Run: docker-compose -f erpnext/docker-compose.erpnext.yml up -d'];
        }
    }

    private function testSSL()
    {
        $isSecure = request()->isSecure();
        return $isSecure
            ? ['status' => 'healthy', 'message' => 'Site is using HTTPS encryption']
            : ['status' => 'warning', 'message' => 'Site is not using HTTPS', 'fix' => 'Run SSL setup script in ssl/ directory'];
    }

    private function testAiAgents()
    {
        try {
            $provider = Config::get('ai-agents.provider', 'botpress');
            $port = $provider === 'rasa' ? 5005 : 3000;
            $response = Http::timeout(2)->get("http://localhost:{$port}");
            if ($response->successful()) {
                return ['status' => 'healthy', 'message' => "{$provider} is running on port {$port}"];
            }
            return ['status' => 'error', 'message' => "{$provider} returned error"];
        } catch (\Exception $e) {
            return ['status' => 'offline', 'message' => 'AI agent not reachable', 'fix' => "Run: docker-compose -f ai-agents/docker-compose.{$provider}.yml up -d"];
        }
    }

    private function testRecommendations()
    {
        try {
            $response = Http::timeout(2)->get('http://localhost:8001/health');
            if ($response->successful()) {
                return ['status' => 'healthy', 'message' => 'Recommendation engine is running on port 8001'];
            }
            return ['status' => 'error', 'message' => 'Recommendation engine returned error'];
        } catch (\Exception $e) {
            return ['status' => 'offline', 'message' => 'Recommendation engine not reachable', 'fix' => 'Run: docker-compose -f recommendation/docker-compose.recommendation.yml up -d'];
        }
    }

    private function testDatabase()
    {
        try {
            DB::connection()->getPdo();
            $tables = DB::select('SHOW TABLES');
            return ['status' => 'healthy', 'message' => 'Database connected with ' . count($tables) . ' tables'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Database connection failed: ' . $e->getMessage()];
        }
    }

    private function testRedis()
    {
        try {
            Cache::store('redis')->put('health_test', 'ok', 10);
            $val = Cache::store('redis')->get('health_test');
            return $val === 'ok'
                ? ['status' => 'healthy', 'message' => 'Redis cache is working']
                : ['status' => 'error', 'message' => 'Redis read/write failed'];
        } catch (\Exception $e) {
            return ['status' => 'offline', 'message' => 'Redis not reachable', 'detail' => 'Cache will use file driver instead'];
        }
    }
}
