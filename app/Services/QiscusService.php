<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class QiscusService
{
    protected $baseUrl;
    protected $client;
    protected $appId;
    protected $secret;
    protected $token;

    public function __construct()
    {
        $this->baseUrl = env('QISCUS_BASE_URL');
        $this->client = new Client(['base_uri' => env('QISCUS_BASE_URL')]);
        $this->appId = env('QISCUS_APP_ID');
        $this->secret = env('QISCUS_SECRET');

        $auth = new QiscusAuthService();
        $this->token = $auth->getToken();
    }

    public function setMarkAsResolvedWebhook($endpoint, $enable = true)
    {
        try {
            $multipart = [
                [
                    'name' => 'webhook_url',
                    'contents' => $endpoint
                ],
                [
                    'name' => 'is_webhook_enabled',
                    'contents' => ($enable) ? 'true' : 'false'
                ]
            ];

            $response = $this->client->post('/api/v1/app/webhook/mark_as_resolved', [
                'headers' => [
                    'Authorization' => "{$this->token}",
                    'Qiscus-App-Id' => "{$this->appId}"
                ],
                'multipart' => $multipart
            ]);

            return json_decode($response->getBody()->getContents(), true);

            $headers = [
                'Authorization' => "{$this->token}",
            ];

            $response = Http::qiscus()
                ->asMultipart()
                ->withHeaders($headers)
                ->post("/api/v1/app/webhook/mark_as_resolved", $multipart);

            if ($response->successful()) {
                return $response->json();
            }

            return ResponseHandler::error('Failed to fetch data from the API', $response->status(), $response->json('errors'));
        } catch (RequestException $e) {
            Log::error('getAgentByIds Error: ' . $e->getCode() . ': ' . $e->getMessage(), ['params' => $multipart]);
            return ResponseHandler::error($e->getMessage(), $e->getCode());
        }
    }

    public function getChannels(): Collection
    {
        try {
            $headers = [
                'Authorization' => "{$this->token}",
                'Content-Type'  => "application/json"
            ];

            $response = Http::qiscus()
                ->withHeaders($headers)
                ->get("/api/v2/channels");

            if ($response->successful()) {
                $response = $response->json();

                $channels = $response['data'];

                $formated = [];
                foreach ($channels as $source => $items) {
                    foreach ($items as $key => $channel) {
                        $formated[] = [
                            'source' => str_replace('_channels', '', $source),
                            'channel_id' => $channel['id']
                        ];
                    }
                }

                return collect($formated);
            }

            return ResponseHandler::error('Failed to fetch data from the API', $response->status(), $response->json('errors'));
        } catch (RequestException $e) {
            Log::error('getAvailableAgents Error: ' . $e->getCode() . ': ' . $e->getMessage(), ['params' => []]);
            return ResponseHandler::error($e->getMessage(), $e->getCode());
        }
    }

    public function getCustomerRooms($status = "unserved")
    {
        try {
            $headers = [
                'Authorization' => "{$this->token}",
                'Content-Type'  => "application/json"
            ];

            $channels = $this->getChannels();

            $body = [
                'channels' => $channels->toArray(),
                "serve_status" => $status
            ];

            $response = Http::qiscus()
                ->withHeaders($headers)
                ->withBody(json_encode($body))
                ->get("/api/v2/customer_rooms");

            if ($response->successful()) {
                $response = $response->json();

                $rooms = collect($response['data']['customer_rooms']);

                // Status "unserved" tidak menjamin data yg didapat error free.
                // Ditemukan data dengan status "is_resolved = true" dan "is_waiting = false" di respons API diatas.
                // Jadi, kita perlu buang data yang tidak valid
                $rooms = $rooms->where('is_resolved', false)->where('is_waiting', true);

                return $rooms;
            }

            return ResponseHandler::error('Failed to fetch data from the API', $response->status(), $response->json('errors'));
        } catch (RequestException $e) {
            Log::error('getAvailableAgents Error: ' . $e->getCode() . ': ' . $e->getMessage(), ['params' => $body]);
            return ResponseHandler::error($e->getMessage(), $e->getCode());
        }
    }

    public function getAgentByIds($ids = [])
    {
        try {
            $headers = [
                'Authorization' => "{$this->token}",
                'Qiscus-App-Id' => "{$this->appId}",
                // 'Content-Type'  => "application/json"
            ];

            $query = [];
            foreach ($ids as $value) {
                $query[] = [
                    'ids[]' => $value,
                ];
            }

            $response = Http::qiscus()
                ->withHeaders($headers)
                ->withQueryParameters($query)
                ->get("/api/v1/admin/agents/get_by_ids");

            if ($response->successful()) {
                return $response->json();
            }

            return ResponseHandler::error('Failed to fetch data from the API', $response->status(), $response->json('errors'));
        } catch (RequestException $e) {
            Log::error('getAgentByIds Error: ' . $e->getCode() . ': ' . $e->getMessage(), ['params' => $query]);
            return ResponseHandler::error($e->getMessage(), $e->getCode());
        }
    }

    public function getRoomById($id)
    {
        try {
            $headers = [
                'Authorization' => "{$this->token}",
                'Qiscus-App-Id' => "{$this->appId}",
                'Content-Type'  => "application/json"
            ];

            $response = Http::qiscus()
                ->withHeaders($headers)
                ->get("/api/v2/customer_rooms/" . $id);

            if ($response->successful()) {
                return $response->json();
            }

            return ResponseHandler::error('Failed to fetch data from the API', $response->status(), $response->json('errors'));
        } catch (RequestException $e) {
            Log::error('getRoomById Error: ' . $e->getCode() . ': ' . $e->getMessage(), ['params' => ['room_id' => $id]]);
            return ResponseHandler::error($e->getMessage(), $e->getCode());
        }
    }

    public function assignAgent($room_id, $agent_id, $replace_latest_agent = false, $max_agent = 1)
    {
        try {
            $headers = [
                'Qiscus-Secret-Key' => "{$this->secret}",
                'Content-Type'  => "application/x-www-form-urlencoded"
            ];

            $form_params = [
                'room_id' => $room_id,
                'agent_id' => $agent_id,
                'replace_latest_agent' => $replace_latest_agent,
                'max_agent' => $max_agent
            ];

            $response = Http::qiscus()
                ->asForm()
                ->withHeaders($headers)
                ->post("/api/v1/admin/service/assign_agent", $form_params);

            if ($response->successful()) {
                return $response->json();
            }

            return ResponseHandler::error('Failed to fetch data from the API', $response->status(), $response->json('errors'));
        } catch (RequestException $e) {
            Log::error('assignAgent Error: ' . $e->getCode() . ': ' . $e->getMessage(), ['params' => $form_params]);
            return ResponseHandler::error($e->getMessage(), $e->getCode());
        }
    }

    public function getAvailableAgents($room_id, $is_available_in_room = false, $limit = 10, $cursor_after = null, $cursor_before = null)
    {
        try {
            $headers = [
                'Authorization' => "{$this->token}",
            ];

            $query = [
                'room_id' => $room_id,
                'limit' => $limit,
                'cursor_after' => $cursor_after,
                'cursor_before' => $cursor_before,
                'is_available_in_room' => ($is_available_in_room) ? true : false
            ];

            $response = Http::qiscus()
                ->withHeaders($headers)
                ->withQueryParameters($query)
                ->get("/api/v2/admin/service/available_agents");

            if ($response->successful()) {
                return $response->json();
            }

            return ResponseHandler::error('Failed to fetch data from the API', $response->status(), $response->json('errors'));
        } catch (RequestException $e) {
            Log::error('getAvailableAgents Error: ' . $e->getCode() . ': ' . $e->getMessage(), ['params' => $query]);
            return ResponseHandler::error($e->getMessage(), $e->getCode());
        }
    }
}
