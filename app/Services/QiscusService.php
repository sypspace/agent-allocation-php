<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class QiscusService
{
    protected $client;
    protected $appId;
    protected $secret;
    protected $token;

    public function __construct()
    {
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
                    'contents' => $enable
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
        } catch (ClientException $e) {
            Log::error('setMarkAsResolvedWebhook Error: ' . $e->getMessage(), ['params' => $multipart]);
            return ResponseHandler::error($e->getMessage(), $e->getCode());
        }
    }

    public function getChannels(): Collection
    {
        try {
            $response = $this->client->get('/api/v2/channels', [
                'headers' => [
                    'Authorization' => "{$this->token}",
                    'Qiscus-App-Id' => "{$this->appId}",
                    'Content-Type'  => "application/json"
                ],
            ]);

            $response = json_decode($response->getBody()->getContents(), true);
            $channels = $response['data'];

            $formated = [];
            foreach ($channels as $key => $channel) {
                $formated[] = [
                    'source' => str_replace('_channels', '', $key),
                    'channel_id' => $channel
                ];
            }

            return collect($formated);
        } catch (ClientException $e) {
            Log::error('getChannels Error: ' . $e->getMessage(), ['params' => []]);
            return ResponseHandler::error($e->getMessage(), $e->getCode());
        }
    }

    public function getCustomerRooms()
    {
        $channels = $this->getChannels();

        $body = [
            'channels' => $channels->toArray(),
            "serve_status" => "unserved"
        ];

        try {
            $response = $this->client->get('/api/v2/customer_rooms', [
                'headers' => [
                    'Authorization' => "{$this->token}",
                    'Qiscus-App-Id' => "{$this->appId}",
                    'Content-Type'  => "application/json"
                ],
                'body' => json_encode($body)
            ]);

            $response = json_decode($response->getBody()->getContents(), true);

            return collect($response['data']['customer_rooms']);
        } catch (ClientException $e) {
            Log::error('getChannels Error: ' . $e->getMessage(), ['params' => $body]);
            return ResponseHandler::error($e->getMessage(), $e->getCode());
        }
    }

    public function getAgentByIds($ids = [])
    {
        try {
            $query = [];
            foreach ($ids as $value) {
                $query[] = [
                    'ids[]' => $value,
                ];
            }

            $response = $this->client->get('/api/v1/admin/agents/get_by_ids', [
                'headers' => [
                    'Authorization' => "{$this->token}",
                    'Qiscus-App-Id' => "{$this->appId}",
                    // 'Content-Type'  => "application/json"
                ],
                'query' => $query
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (ClientException $e) {
            Log::error('getAgentByIds Error: ' . $e->getMessage(), ['params' => $query]);
            return ResponseHandler::error($e->getMessage(), $e->getCode());
        }
    }

    public function getRoomById($id)
    {
        try {
            $response = $this->client->get('/api/v2/customer_rooms/' . $id, [
                'headers' => [
                    'Authorization' => "{$this->token}",
                    'Qiscus-App-Id' => "{$this->appId}",
                    'Content-Type'  => "application/json"
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (ClientException $e) {
            Log::error('getAgentByIds Error: ' . $e->getMessage(), ['params' => ['room_id' => $id]]);
            return ResponseHandler::error($e->getMessage(), $e->getCode());
        }
    }

    public function assignAgent($room_id, $agent_id, $replace_latest_agent = false, $max_agent = 1)
    {
        try {
            $form_params = [
                'room_id' => $room_id,
                'agent_id' => $agent_id,
                'replace_latest_agent' => $replace_latest_agent,
                'max_agent' => $max_agent
            ];

            $response = $this->client->post('/api/v1/admin/service/assign_agent', [
                'headers' => [
                    'Qiscus-App-Id' => "{$this->appId}",
                    'Qiscus-Secret-Key' => "{$this->secret}",
                    'Content-Type'  => "application/x-www-form-urlencoded"
                ],
                'form_params' => $form_params
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (ClientException $e) {
            Log::error('assignAgent Error: ' . $e->getMessage(), ['params' => $form_params]);
            return ResponseHandler::error($e->getMessage(), $e->getCode());
        }
    }

    public function getAvailableAgents($room_id, $is_available_in_room = false, $limit = 10, $cursor_after = null, $cursor_before = null)
    {
        try {
            $query = [
                'room_id' => $room_id,
                'limit' => $limit,
                'cursor_after' => $cursor_after,
                'cursor_before' => $cursor_before,
                'is_available_in_room' => $is_available_in_room
            ];

            $response = $this->client->post('/api/v2/admin/service/available_agents', [
                'headers' => [
                    'Qiscus-App-Id' => "{$this->appId}",
                    'Qiscus-Secret-Key' => "{$this->secret}"
                ],
                'query' => $query
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (ClientException $e) {
            Log::error('getAvailableAgents Error: ' . $e->getMessage(), ['params' => $query]);
            return ResponseHandler::error($e->getMessage(), $e->getCode());
        }
    }
}
