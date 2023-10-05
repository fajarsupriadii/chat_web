<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('dashboard.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    
    public function createChatContact(Request $request)
    {   
        try {
            $data = $request->post();
            $url = env('LIVECHAT_URL') . '/api/v1/omnichannel/contact';
            $client = new Client();
            $request = $client->request('POST', $url, [
                'headers' => [
                    'X-Auth-Token' => env('LIVECHAT_X_TOKEN'),
                    'X-User-Id' => env('LIVECHAT_X_USER_ID'),
                ],
                'form_params' => [
                    'token' =>  $data['token'],
                    'name' =>  $data['name'],
                    'email' =>  $data['email'],
                ]
            ]);
            $response = $request->getBody()->getContents();
            Log::error($response);

            return response($response);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function createChatRoom(Request $request)
    {
        try {
            $user_token = $request->query('token');
            $url = env('LIVECHAT_URL') . '/api/v1/livechat/room?token=' . $user_token;
            $client = new Client();
            $request = $client->request('GET', $url);
            $response = $request->getBody()->getContents();
            Log::error($response);
            $response = json_decode($response, true);

            if (!isset($response['room'])) {
                return response([
                    'message' => 'Create chat room failed!'
                ], 500);  
            }

            return response([
                'message' => 'Create chat room success',
                'room_id' => $response['room']['_id'],
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function sendMessage(Request $request)
    {
        try {
            $data = $request->post();
            $url = env('LIVECHAT_URL') . '/api/v1/livechat/message';
            $client = new Client();
            $request = $client->request('POST', $url, [
                'form_params' => [
                    'token' =>  $data['token'],
                    'rid' =>  $data['rid'],
                    'msg' =>  $data['msg'],
                ]
            ]);
            $response = $request->getBody()->getContents();
            Log::error($response);
            $response = json_decode($response, true);

            if (!isset($response['message'])) {
                return response([]);
            }
            Session::put('lastChatId', $response['message']['_id']);

            return response([
                'message' => 'Chat successfully sent',
                'message_id' => $response['message']['_id'],
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getChatHistory(Request $request)
    {
        try {
            $user_token = $request->query('token');
            $room_id = $request->query('roomId');
            $result = [];
            $lastChatId = Session::get('lastChatId', null);
            $newChatId = null;

            // return if the guest still has not sending a message
            if (!$lastChatId) {
                return response($result);
            }

            $url = env('LIVECHAT_URL') . '/api/v1/livechat/messages.history/' . $room_id . '?token=' . $user_token;
            $client = new Client();
            $request = $client->request('GET', $url);
            $response = $request->getBody()->getContents();
            Log::error($response);
            $response = json_decode($response, true);

            if (isset($response['messages'])) {
                foreach ($response['messages'] as $key => $value) {
                    if ($value['_id'] == $lastChatId) {
                        break;
                    }

                    if (!isset($value['t'])) {
                        if (!$newChatId) {
                            $newChatId = $value['_id'];
                            Session::put('lastChatId', $newChatId);
                        }
                        $result[] = [
                            'id' => $value['_id'],
                            'msg' => $value['msg']
                        ];
                    }
                    $result = array_reverse($result);
                }
            }

            return response($result);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response([
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
