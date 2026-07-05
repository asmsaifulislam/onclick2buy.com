<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;

class AutomationHubController extends Controller
{
    public function index()
    {
        $services = $this->getServicesStatus();
        return view('admin.automation-hub', compact('services'));
    }

    public function getServicesStatus()
    {
        $services = [];

        // Google Analytics
        $services['analytics'] = [
            'name' => 'Google Analytics (GA4)',
            'icon' => 'chart-bar',
            'color' => 'blue',
            'status' => !empty(Config::get('app.GOOGLE_ANALYTICS_ID')) ? 'active' : 'inactive',
            'description' => 'Track visitor behavior, conversions, and campaign performance.',
            'url' => route('admin.analytics.index'),
            'config_url' => 'https://analytics.google.com',
        ];

        // Prometheus + Grafana
        $services['monitoring'] = [
            'name' => 'Server Monitoring',
            'icon' => 'server',
            'color' => 'orange',
            'status' => $this->checkService('monitoring', 'prometheus', 9090),
            'description' => 'Prometheus metrics collection + Grafana dashboards for server health.',
            'url' => '#monitoring',
            'config_url' => 'http://127.0.0.1:3001',
        ];

        // Mautic Marketing
        $services['mautic'] = [
            'name' => 'Marketing Automation',
            'icon' => 'bolt',
            'color' => 'purple',
            'status' => $this->checkService('mautic', 'mautic', 8090),
            'description' => 'Email campaigns, lead scoring, cart abandonment recovery.',
            'url' => route('admin.mautic.index'),
            'config_url' => 'http://127.0.0.1:8090',
        ];

        // ERPNext
        $services['erpnext'] = [
            'name' => 'ERP & Inventory',
            'icon' => 'cube',
            'color' => 'indigo',
            'status' => $this->checkService('erpnext', 'erpnext', 8000),
            'description' => 'Enterprise resource planning, stock management, accounting.',
            'url' => route('admin.erpnext.index'),
            'config_url' => 'http://localhost:8000',
        ];

        // SSL/HTTPS
        $services['ssl'] = [
            'name' => 'SSL / HTTPS',
            'icon' => 'lock-closed',
            'color' => 'green',
            'status' => request()->isSecure() ? 'active' : 'inactive',
            'description' => 'Encrypted connections, security headers, auto-renewal via Let\'s Encrypt.',
            'url' => '#ssl',
            'config_url' => null,
        ];

        // AI Agents
        $services['ai_agents'] = [
            'name' => 'AI Chat Agents',
            'icon' => 'chip',
            'color' => 'yellow',
            'status' => $this->checkService('ai-agents', 'botpress', 3000),
            'description' => 'Rasa / Botpress / Bot Framework conversational AI for customer support.',
            'url' => route('admin.ai-agents.index'),
            'config_url' => 'http://localhost:3001',
        ];

        // Recommendations
        $services['recommendations'] = [
            'name' => 'Recommendation Engine',
            'icon' => 'sparkles',
            'color' => 'pink',
            'status' => $this->checkService('recommendation', 'recommendation', 8001),
            'description' => 'AI-powered product suggestions using Surprise (SVD/KNN/NMF).',
            'url' => route('admin.recommendations.index'),
            'config_url' => 'http://localhost:8001',
        ];

        // Live Chat
        $services['livechat'] = [
            'name' => 'Live Chat',
            'icon' => 'chat',
            'color' => 'cyan',
            'status' => 'active',
            'description' => 'Real-time customer support chat with admin dashboard.',
            'url' => route('admin.chat.index'),
            'config_url' => null,
        ];

        return $services;
    }

    private function checkService($composeDir, $containerKeyword, $port)
    {
        try {
            $response = Http::timeout(2)->get("http://localhost:{$port}");
            return $response->successful() ? 'active' : 'error';
        } catch (\Exception $e) {
            return 'offline';
        }
    }
}
