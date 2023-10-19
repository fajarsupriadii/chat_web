<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use GuzzleHttp\Client;

class RocketChatController extends Controller
{
    public function RocketchatRouting(Request $request)
    {   
        try {
            $client = new Client();
            // Get token from session
            // $authToken = Session::get('liveChatToken', null);
            // $authUserId = Session::get('liveChatUserId', null);
            $urlGetRoom = env('LIVECHAT_URL') . '/api/v1/livechat/rooms?open=true';
            $urlGetAgent = env('LIVECHAT_URL') . '/api/v1/omnichannel/agents/available';
            $listUnavailableAgent = [];
            $agentId = $agentUsername = null;

            // Login to live chat API
            // if (!$authToken) {
                $responseLogin = $this->loginChatApi(env('LIVECHAT_USERNAME'), env('LIVECHAT_PASSWORD'));
                $authToken = $responseLogin['authToken'];
                $authUserId = $responseLogin['authUserId'];
            // }

            // Get livechat open room
            $requestRoom = $client->request('GET', $urlGetRoom, [
                'headers' => [
                    'X-Auth-Token' => $authToken,
                    'X-User-Id' => $authUserId,
                ],
            ]);
            $responseRoom = $requestRoom->getBody()->getContents();
            $responseRoom = json_decode($responseRoom, true);

            // Map agent with open livechat
            if (isset($responseRoom['rooms'])) {
                foreach ($responseRoom['rooms'] as $key => $value) {
                    if (isset($value['servedBy'])) {
                        $listUnavailableAgent[] = $value['servedBy']['_id'];
                    }
                }
            }

            // Get livechat available agent
            $requestAgent = $client->request('GET', $urlGetAgent, [
                'headers' => [
                    'X-Auth-Token' => $authToken,
                    'X-User-Id' => $authUserId,
                ],
            ]);
            $responseAgent = $requestAgent->getBody()->getContents();
            $responseAgent = json_decode($responseAgent, true);

            // Set available agent
            if (isset($responseAgent['agents'])) {
                foreach ($responseAgent['agents'] as $key => $value) {
                    if (!in_array($value['_id'], $listUnavailableAgent) && in_array($value['status'], ['online', 'away'])) {
                        $agentId = $value['_id'];
                        $agentUsername = $value['username'];
                        break;
                    }
                }
            }


            return response()->json([
                '_id' => $agentId,
                'username' => $agentUsername
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response([], 500);
        }
    }

    private function loginChatApi($username, $password)
    {
        $authToken = $authUserId = null;
        $urlLogin = env('LIVECHAT_URL') . '/api/v1/login';
        $client = new Client();
        $requestLogin = $client->request('POST', $urlLogin, [
            'form_params' => [
                'username' =>  $username,
                'password' =>  $password,
            ]
        ]);
        $responseLogin = $requestLogin->getBody()->getContents();
        // Log::error($responseLogin);
        $responseLogin = json_decode($responseLogin, true);
        if ($responseLogin['status'] == 'success') {
            $authToken = $responseLogin['data']['authToken'];
            $authUserId = $responseLogin['data']['userId'];
        }

        return [
            'authToken' => $authToken,
            'authUserId' => $authUserId,
        ];
    }
}
