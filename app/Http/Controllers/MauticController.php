<?php

namespace App\Http\Controllers;

use App\Services\MauticService;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class MauticController extends Controller
{
    protected $mautic;

    public function __construct(MauticService $mautic)
    {
        $this->mautic = $mautic;
    }

    /**
     * Show Mautic admin page
     */
    public function index()
    {
        return view('admin.mautic.index');
    }

    /**
     * Handle Mautic webhook
     */
    public function webhook(Request $request): JsonResponse
    {
        $secret = config('mautic.webhook.secret');
        $signature = $request->header('X-Mautic-Signature');

        if ($secret && $signature !== hash_hmac('sha256', $request->getContent(), $secret)) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        try {
            $payload = $request->all();
            $type = $payload['type'] ?? '';
            $event = $payload['event'] ?? '';

            Log::info('Mautic webhook received', ['type' => $type, 'event' => $event]);

            match($event) {
                'contact.deleted' => $this->handleContactDeleted($payload),
                'contact.donotcontact' => $this->handleDoNotContact($payload),
                default => null,
            };

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Mautic webhook error: ' . $e->getMessage());
            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    /**
     * Track page view
     */
    public function trackPage(Request $request): JsonResponse
    {
        $data = $request->only([
            'page_title', 'page_url', 'referrer',
            'email', 'firstname', 'lastname',
        ]);

        $this->mautic->trackPageView($data);

        return response()->json(['success' => true]);
    }

    /**
     * Track product view
     */
    public function trackProductView(Request $request): JsonResponse
    {
        $productId = $request->input('product_id');
        $productName = $request->input('product_name');
        $productPrice = $request->input('product_price');
        $email = $request->input('email');

        if ($email) {
            $contact = $this->mautic->getContactByEmail($email);
            $contactId = $contact['contact']['id'] ?? null;

            if ($contactId) {
                $this->mautic->addContactPoints($contactId, 2);
                $this->mautic->addContactTags($contactId, ['product-viewer']);
            }
        }

        return response()->json(['success' => true]);
    }

    /**
     * Track cart abandonment
     */
    public function trackCartAbandonment(Request $request): JsonResponse
    {
        $email = $request->input('email');
        $cartTotal = $request->input('cart_total', 0);
        $items = $request->input('items', []);

        if (!$email) {
            return response()->json(['error' => 'Email required'], 422);
        }

        $contact = $this->mautic->getContactByEmail($email);
        $contactId = $contact['contact']['id'] ?? null;

        if (!$contactId) {
            $newContact = $this->mautic->createContact([
                'email' => $email,
                'firstname' => $request->input('firstname', ''),
                'lastname' => $request->input('lastname', ''),
            ]);
            $contactId = $newContact['id'] ?? null;
        }

        if ($contactId) {
            $this->mautic->addContactTags($contactId, ['cart-abandonment']);
            $this->mautic->trackEvent($contactId, [
                'event' => 'cart_abandonment',
                'cart_total' => $cartTotal,
                'items' => $items,
            ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Handle user registration sync
     */
    public function handleRegistration(User $user): void
    {
        if (!config('mautic.sync.enabled')) {
            return;
        }

        $this->mautic->createOrUpdateContact([
            'email' => $user->email,
            'firstname' => $user->name,
            'lastname' => '',
            'phone' => $user->phone ?? '',
        ]);
    }

    /**
     * Handle order completion sync
     */
    public function handleOrderCompleted(Order $order): void
    {
        if (!config('mautic.sync.enabled')) {
            return;
        }

        $user = $order->user;
        $email = $user->email ?? $order->email;

        $this->mautic->trackOrder([
            'email' => $email,
            'firstname' => $user->name ?? '',
            'lastname' => '',
            'order_id' => $order->id,
            'total' => $order->total,
            'products' => $order->items->map(function ($item) {
                return [
                    'name' => $item->product->name ?? '',
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                ];
            })->toArray(),
        ]);
    }

    /**
     * Test Mautic connection
     */
    public function testConnection(): JsonResponse
    {
        $result = $this->mautic->testConnection();
        return response()->json($result);
    }

    /**
     * Get contacts list
     */
    public function getContacts(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 30);
        $start = $request->input('start', 0);

        $contacts = $this->mautic->getContacts($limit, $start);

        return response()->json($contacts);
    }

    /**
     * Sync user to Mautic
     */
    public function syncContact(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $result = $this->mautic->createOrUpdateContact($request->all());

        if ($result) {
            return response()->json([
                'success' => true,
                'contact' => $result,
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => 'Failed to sync contact',
        ], 500);
    }

    /**
     * Get Mautic tracking pixel script
     */
    public function getTrackingPixel(): string
    {
        $pixelId = config('mautic.tracking.pixel_id');
        $baseUrl = config('mautic.base_url');

        if (!$pixelId || !config('mautic.tracking.enabled')) {
            return '';
        }

        return "<!-- Mautic Tracking Pixel -->
<script>
    (function() {
        var defined = null;
        var script = document.createElement('script');
        script.type = 'text/javascript';
        script.async = true;
        script.src = '{$baseUrl}/mtc/pixel.js?id={$pixelId}';
        document.getElementsByTagName('head')[0].appendChild(script);
    })();
</script>";
    }

    /**
     * Handle contact deleted webhook
     */
    protected function handleContactDeleted(array $payload): void
    {
        $contactId = $payload['contact']['id'] ?? null;

        if ($contactId) {
            Log::info("Mautic contact deleted: {$contactId}");
        }
    }

    /**
     * Handle do not contact webhook
     */
    protected function handleDoNotContact(array $payload): void
    {
        $email = $payload['contact']['email'] ?? null;

        if ($email) {
            Log::info("Mautic contact marked as do not contact: {$email}");
        }
    }
}
