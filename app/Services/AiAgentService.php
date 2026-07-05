<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiAgentService
{
    protected $provider;
    protected $rasaUrl;
    protected $botpressUrl;
    protected $botframeworkAppId;
    protected $botframeworkAppPassword;

    public function __construct()
    {
        $this->provider = config('ai-agents.provider', 'rasa');
        $this->rasaUrl = config('ai-agents.rasa.url', 'http://localhost:5005');
        $this->botpressUrl = config('ai-agents.botpress.url', 'http://localhost:3100');
        $this->botframeworkAppId = config('ai-agents.botframework.app_id', '');
        $this->botframeworkAppPassword = config('ai-agents.botframework.app_password', '');
    }

    /**
     * Send message to AI agent
     */
    public function sendMessage(string $message, string $sessionId, array $context = []): ?array
    {
        return match($this->provider) {
            'rasa' => $this->sendToRasa($message, $sessionId, $context),
            'botpress' => $this->sendToBotpress($message, $sessionId, $context),
            'botframework' => $this->sendToBotFramework($message, $sessionId, $context),
            default => null,
        };
    }

    /**
     * Send message to Rasa
     */
    protected function sendToRasa(string $message, string $sessionId, array $context = []): ?array
    {
        try {
            $response = Http::timeout(30)
                ->post("{$this->rasaUrl}/webhooks/rest/webhook", [
                    'sender' => $sessionId,
                    'message' => $message,
                    'metadata' => $context,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                $replies = [];
                foreach ($data as $item) {
                    if (isset($item['text'])) {
                        $replies[] = [
                            'type' => 'text',
                            'content' => $item['text'],
                        ];
                    }
                    if (isset($item['image'])) {
                        $replies[] = [
                            'type' => 'image',
                            'content' => $item['image'],
                        ];
                    }
                    if (isset($item['buttons'])) {
                        $replies[] = [
                            'type' => 'buttons',
                            'content' => $item['buttons'],
                        ];
                    }
                }

                return [
                    'success' => true,
                    'provider' => 'rasa',
                    'replies' => $replies,
                    'intent' => $data[0]['intent'] ?? null,
                    'confidence' => $data[0]['confidence'] ?? null,
                ];
            }
        } catch (\Exception $e) {
            Log::error('Rasa API error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Send message to Botpress
     */
    protected function sendToBotpress(string $message, string $sessionId, array $context = []): ?array
    {
        try {
            $response = Http::timeout(30)
                ->post("{$this->botpressUrl}/api/v1/bots/ecommerce-bot/converse/{$sessionId}", [
                    'type' => 'text',
                    'text' => $message,
                    'data' => $context,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                $replies = [];
                foreach ($data['responses'] ?? [] as $item) {
                    $replies[] = [
                        'type' => $item['type'] ?? 'text',
                        'content' => $item['text'] ?? $item['payload'] ?? '',
                    ];
                }

                return [
                    'success' => true,
                    'provider' => 'botpress',
                    'replies' => $replies,
                ];
            }
        } catch (\Exception $e) {
            Log::error('Botpress API error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Send message to Microsoft Bot Framework
     */
    protected function sendToBotFramework(string $message, string $sessionId, array $context = []): ?array
    {
        try {
            // Get token
            $tokenResponse = Http::asForm()
                ->post("https://login.microsoftonline.com/botframework.com/oauth2/v2.0/token", [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->botframeworkAppId,
                    'client_secret' => $this->botframeworkAppPassword,
                    'scope' => 'https://api.botframework.com/.default',
                ]);

            if (!$tokenResponse->successful()) {
                return null;
            }

            $token = $tokenResponse->json('access_token');

            // Send message
            $response = Http::withToken($token)
                ->post("https://directline.botframework.com/v3/directline/conversations/{$sessionId}/activities", [
                    'type' => 'message',
                    'from' => ['id' => $sessionId],
                    'text' => $message,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'provider' => 'botframework',
                    'replies' => [
                        [
                            'type' => 'text',
                            'content' => $data['text'] ?? 'Message received',
                        ],
                    ],
                    'conversationId' => $data['conversation']['id'] ?? null,
                ];
            }
        } catch (\Exception $e) {
            Log::error('Bot Framework API error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Train Rasa model
     */
    public function trainRasaModel(): array
    {
        try {
            $response = Http::timeout(300)
                ->post("{$this->rasaUrl}/api/train", [
                    'domain' => config_path('ai-agents/rasa/domain.yml'),
                    'nlu' => config_path('ai-agents/rasa/data/nlu.yml'),
                    'stories' => config_path('ai-agents/rasa/data/stories.yml'),
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Model training started',
                ];
            }
        } catch (\Exception $e) {
            Log::error('Rasa training error: ' . $e->getMessage());
        }

        return [
            'success' => false,
            'message' => 'Failed to start training',
        ];
    }

    /**
     * Get Rasa status
     */
    public function getRasaStatus(): ?array
    {
        try {
            $response = Http::timeout(10)
                ->get("{$this->rasaUrl}/status");

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::error('Rasa status error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Get Botpress status
     */
    public function getBotpressStatus(): ?array
    {
        try {
            $response = Http::timeout(10)
                ->get("{$this->botpressUrl}/api/v1/admin/bots");

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::error('Botpress status error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Test AI agent connection
     */
    public function testConnection(): array
    {
        $status = match($this->provider) {
            'rasa' => $this->testRasaConnection(),
            'botpress' => $this->testBotpressConnection(),
            'botframework' => $this->testBotFrameworkConnection(),
            default => ['status' => 'error', 'message' => 'Unknown provider'],
        };

        return $status;
    }

    /**
     * Test Rasa connection
     */
    protected function testRasaConnection(): array
    {
        try {
            $response = Http::timeout(10)
                ->get("{$this->rasaUrl}/status");

            if ($response->successful()) {
                return [
                    'status' => 'success',
                    'message' => 'Connected to Rasa successfully',
                    'version' => $response->json('fingerprint.rasa_open_source') ?? 'unknown',
                ];
            }

            return [
                'status' => 'error',
                'message' => 'Failed to connect to Rasa',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Connection failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Test Botpress connection
     */
    protected function testBotpressConnection(): array
    {
        try {
            $response = Http::timeout(10)
                ->get("{$this->botpressUrl}/api/v1/admin/bots");

            if ($response->successful()) {
                return [
                    'status' => 'success',
                    'message' => 'Connected to Botpress successfully',
                ];
            }

            return [
                'status' => 'error',
                'message' => 'Failed to connect to Botpress',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Connection failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Test Bot Framework connection
     */
    protected function testBotFrameworkConnection(): array
    {
        if (empty($this->botframeworkAppId) || empty($this->botframeworkAppPassword)) {
            return [
                'status' => 'error',
                'message' => 'Bot Framework credentials not configured',
            ];
        }

        try {
            $response = Http::asForm()
                ->timeout(10)
                ->post("https://login.microsoftonline.com/botframework.com/oauth2/v2.0/token", [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->botframeworkAppId,
                    'client_secret' => $this->botframeworkAppPassword,
                    'scope' => 'https://api.botframework.com/.default',
                ]);

            if ($response->successful()) {
                return [
                    'status' => 'success',
                    'message' => 'Connected to Bot Framework successfully',
                ];
            }

            return [
                'status' => 'error',
                'message' => 'Failed to authenticate with Bot Framework',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Connection failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get current provider
     */
    public function getProvider(): string
    {
        return $this->provider;
    }

    /**
     * Set active provider
     */
    public function setProvider(string $provider): bool
    {
        $validProviders = ['rasa', 'botpress', 'botframework'];

        if (in_array($provider, $validProviders)) {
            config(['ai-agents.provider' => $provider]);
            return true;
        }

        return false;
    }
}
