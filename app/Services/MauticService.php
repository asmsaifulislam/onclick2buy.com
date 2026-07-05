<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MauticService
{
    protected $baseUrl;
    protected $username;
    protected $password;
    protected $token;

    public function __construct()
    {
        $this->baseUrl = config('mautic.base_url');
        $this->username = config('mautic.api.username');
        $this->password = config('mautic.api.password');
    }

    /**
     * Get API authentication token
     */
    protected function getToken(): ?string
    {
        if ($this->token) {
            return $this->token;
        }

        try {
            $response = Http::withBasicAuth($this->username, $this->password)
                ->get("{$this->baseUrl}/api/authenticate");

            if ($response->successful()) {
                $this->token = $response->json('token');
                return $this->token;
            }
        } catch (\Exception $e) {
            Log::error('Mautic authentication failed: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Make authenticated API request
     */
    protected function request(string $method, string $endpoint, array $data = []): ?array
    {
        $token = $this->getToken();

        if (!$token) {
            return null;
        }

        try {
            $http = Http::withToken($token);

            $response = match($method) {
                'GET' => $http->get("{$this->baseUrl}{$endpoint}"),
                'POST' => $http->post("{$this->baseUrl}{$endpoint}", $data),
                'PUT' => $http->put("{$this->baseUrl}{$endpoint}", $data),
                'DELETE' => $http->delete("{$this->baseUrl}{$endpoint}"),
                default => null
            };

            if ($response && $response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::error("Mautic API request failed ({$endpoint}): " . $e->getMessage());
        }

        return null;
    }

    /**
     * Create or update contact in Mautic
     */
    public function createOrUpdateContact(array $contactData): ?array
    {
        $email = $contactData['email'] ?? null;

        if (!$email) {
            return null;
        }

        $points = $contactData['points'] ?? 0;
        $tags = $contactData['tags'] ?? [];

        $data = [
            'email' => $email,
            'firstname' => $contactData['firstname'] ?? '',
            'lastname' => $contactData['lastname'] ?? '',
            'company' => $contactData['company'] ?? '',
            'phone' => $contactData['phone'] ?? '',
        ];

        // Check if contact exists
        $existing = $this->getContactByEmail($email);

        if ($existing && isset($existing['contact']['id'])) {
            return $this->updateContact($existing['contact']['id'], $data);
        }

        return $this->createContact($data);
    }

    /**
     * Create new contact
     */
    public function createContact(array $data): ?array
    {
        $result = $this->request('POST', '/api/contacts/new', $data);
        return $result['contact'] ?? null;
    }

    /**
     * Update existing contact
     */
    public function updateContact(int $contactId, array $data): ?array
    {
        $result = $this->request('PATCH', "/api/contacts/{$contactId}/edit", $data);
        return $result['contact'] ?? null;
    }

    /**
     * Get contact by email
     */
    public function getContactByEmail(string $email): ?array
    {
        return $this->request('GET', '/api/contacts?search=' . urlencode($email));
    }

    /**
     * Add points to contact
     */
    public function addContactPoints(int $contactId, int $points): bool
    {
        $result = $this->request('POST', "/api/contacts/{$contactId}/points/plus/{$points}");
        return $result !== null;
    }

    /**
     * Add tags to contact
     */
    public function addContactTags(int $contactId, array $tags): bool
    {
        $result = $this->request('POST', "/api/contacts/{$contactId}/tags", [
            'tags' => implode(',', $tags),
        ]);
        return $result !== null;
    }

    /**
     * Track page view
     */
    public function trackPageView(array $data): bool
    {
        $pixelId = config('mautic.tracking.pixel_id');

        if (!$pixelId) {
            return false;
        }

        try {
            $response = Http::post("{$this->baseUrl}/mtc/pixel", array_merge($data, [
                'id' => $pixelId,
            ]));

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Mautic page tracking failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Track order event
     */
    public function trackOrder(array $orderData): bool
    {
        $email = $orderData['email'] ?? null;

        if (!$email) {
            return false;
        }

        $contact = $this->getContactByEmail($email);
        $contactId = $contact['contact']['id'] ?? null;

        if (!$contactId) {
            $newContact = $this->createContact([
                'email' => $email,
                'firstname' => $orderData['firstname'] ?? '',
                'lastname' => $orderData['lastname'] ?? '',
            ]);
            $contactId = $newContact['id'] ?? null;
        }

        if (!$contactId) {
            return false;
        }

        // Add points for purchase
        $this->addContactPoints($contactId, 10);

        // Add order tag
        $this->addContactTags($contactId, ['customer', 'purchaser']);

        // Track order as custom event
        return $this->trackEvent($contactId, [
            'event' => 'order_completed',
            'order_id' => $orderData['order_id'] ?? '',
            'order_total' => $orderData['total'] ?? 0,
            'products' => $orderData['products'] ?? [],
        ]);
    }

    /**
     * Track custom event
     */
    public function trackEvent(int $contactId, array $eventData): bool
    {
        $result = $this->request('POST', "/api/events", [
            'contact_id' => $contactId,
            'event_name' => $eventData['event'] ?? 'custom_event',
            'properties' => $eventData,
        ]);

        return $result !== null;
    }

    /**
     * Get all contacts
     */
    public function getContacts(int $limit = 30, int $start = 0): ?array
    {
        return $this->request('GET', "/api/contacts?limit={$limit}&start={$start}");
    }

    /**
     * Get contact by ID
     */
    public function getContact(int $contactId): ?array
    {
        return $this->request('GET', "/api/contacts/{$contactId}");
    }

    /**
     * Delete contact
     */
    public function deleteContact(int $contactId): bool
    {
        $result = $this->request('DELETE', "/api/contacts/{$contactId}/delete");
        return $result !== null;
    }

    /**
     * Get email frequency settings
     */
    public function getEmails(int $limit = 10): ?array
    {
        return $this->request('GET', "/api/emails?limit={$limit}");
    }

    /**
     * Send email to contact
     */
    public function sendEmail(int $emailId, array $contacts): bool
    {
        $contactIds = array_map(fn($c) => $c['id'] ?? $c, $contacts);

        $result = $this->request('POST', "/api/emails/{$emailId}/send", [
            'contacts' => $contactIds,
        ]);

        return $result !== null;
    }

    /**
     * Get Mautic form HTML
     */
    public function getFormHtml(int $formId): ?string
    {
        $result = $this->request('GET', "/api/forms/{$formId}");

        return $result['form']['formHtml'] ?? null;
    }

    /**
     * Submit form data to Mautic
     */
    public function submitForm(int $formId, array $data): bool
    {
        try {
            $response = Http::post("{$this->baseUrl}/form/{$formId}", $data);
            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Mautic form submission failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get campaign list
     */
    public function getCampaigns(): ?array
    {
        return $this->request('GET', '/api/campaigns');
    }

    /**
     * Add contact to campaign
     */
    public function addToCampaign(int $campaignId, int $contactId): bool
    {
        $result = $this->request('POST', "/api/campaigns/{$campaignId}/contact/{$contactId}/add");
        return $result !== null;
    }

    /**
     * Test Mautic connection
     */
    public function testConnection(): array
    {
        try {
            $token = $this->getToken();

            if ($token) {
                return [
                    'status' => 'success',
                    'message' => 'Connected to Mautic successfully',
                ];
            }

            return [
                'status' => 'error',
                'message' => 'Failed to authenticate with Mautic',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Connection failed: ' . $e->getMessage(),
            ];
        }
    }
}
