<?php

namespace App\Http\Controllers;

use App\Services\AiAgentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AiAgentController extends Controller
{
    protected $aiAgent;

    public function __construct(AiAgentService $aiAgent)
    {
        $this->aiAgent = $aiAgent;
    }

    /**
     * Show AI agents admin page
     */
    public function index()
    {
        return view('admin.ai-agents.index');
    }

    /**
     * Send message to AI agent
     */
    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'session_id' => 'required|string',
        ]);

        $message = $request->input('message');
        $sessionId = $request->input('session_id');
        $context = $request->only(['user_id', 'page', 'product_id']);

        // Check for human handoff trigger
        if ($this->shouldHandoff($message)) {
            return response()->json([
                'success' => true,
                'type' => 'handoff',
                'message' => 'I\'m connecting you with a human agent. Please hold on.',
            ]);
        }

        $result = $this->aiAgent->sendMessage($message, $sessionId, $context);

        if ($result && $result['success']) {
            return response()->json($result);
        }

        return response()->json([
            'success' => false,
            'error' => 'Failed to get response from AI agent',
        ], 500);
    }

    /**
     * Test AI agent connection
     */
    public function testConnection(): JsonResponse
    {
        $result = $this->aiAgent->testConnection();
        return response()->json($result);
    }

    /**
     * Get AI agent status
     */
    public function status(): JsonResponse
    {
        $status = match(config('ai-agents.provider')) {
            'rasa' => $this->aiAgent->getRasaStatus(),
            'botpress' => $this->aiAgent->getBotpressStatus(),
            default => null,
        };

        return response()->json([
            'provider' => config('ai-agents.provider'),
            'status' => $status,
        ]);
    }

    /**
     * Get current provider
     */
    public function getProvider(): JsonResponse
    {
        return response()->json([
            'provider' => $this->aiAgent->getProvider(),
        ]);
    }

    /**
     * Set active provider
     */
    public function setProvider(Request $request): JsonResponse
    {
        $request->validate([
            'provider' => 'required|in:rasa,botpress,botframework',
        ]);

        $provider = $request->input('provider');
        $success = $this->aiAgent->setProvider($provider);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => "Provider set to {$provider}",
                'provider' => $provider,
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => 'Invalid provider',
        ], 400);
    }

    /**
     * Train Rasa model
     */
    public function trainRasa(): JsonResponse
    {
        $result = $this->aiAgent->trainRasaModel();
        return response()->json($result);
    }

    /**
     * Handle Bot Framework webhook
     */
    public function botframeworkWebhook(Request $request): JsonResponse
    {
        $activity = $request->all();

        // Verify Bot Framework connector service
        $authorizationHeader = $request->header('Authorization');

        if (!$authorizationHeader) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Process activity
        $type = $activity['type'] ?? '';
        $text = $activity['text'] ?? '';
        $conversationId = $activity['conversation']['id'] ?? '';

        // Route to appropriate handler
        return match($type) {
            'message' => $this->handleBotFrameworkMessage($text, $conversationId, $activity),
            'conversationUpdate' => $this->handleBotFrameworkConversationUpdate($activity),
            default => response()->json(['status' => 'ignored']),
        };
    }

    /**
     * Handle Bot Framework message
     */
    protected function handleBotFrameworkMessage(string $text, string $conversationId, array $activity): JsonResponse
    {
        $result = $this->aiAgent->sendMessage($text, $conversationId);

        if ($result && $result['success']) {
            $replyText = '';
            foreach ($result['replies'] as $reply) {
                $replyText .= $reply['content'] . "\n";
            }

            return response()->json([
                'type' => 'message',
                'text' => trim($replyText),
                'from' => ['id' => 'onclick2buy-bot'],
                'conversation' => ['id' => $conversationId],
            ]);
        }

        return response()->json([
            'type' => 'message',
            'text' => 'I\'m having trouble processing your request. Please try again.',
            'from' => ['id' => 'onclick2buy-bot'],
            'conversation' => ['id' => $conversationId],
        ]);
    }

    /**
     * Handle Bot Framework conversation update
     */
    protected function handleBotFrameworkConversationUpdate(array $activity): JsonResponse
    {
        $membersAdded = $activity['membersAdded'] ?? [];

        foreach ($membersAdded as $member) {
            if ($member['id'] !== 'bot') {
                return response()->json([
                    'type' => 'message',
                    'text' => 'Welcome to OnClick2Buy! How can I help you today?',
                    'from' => ['id' => 'onclick2buy-bot'],
                    'conversation' => ['id' => $activity['conversation']['id']],
                ]);
            }
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * Check if message should trigger human handoff
     */
    protected function shouldHandoff(string $message): bool
    {
        if (!config('ai-agents.handoff.enabled')) {
            return false;
        }

        $triggerKeywords = config('ai-agents.handoff.trigger_keywords', []);
        $lowerMessage = strtolower($message);

        foreach ($triggerKeywords as $keyword) {
            if (str_contains($lowerMessage, $keyword)) {
                return true;
            }
        }

        return false;
    }
}
