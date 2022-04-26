<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController as BaseController;
use App\Models\Chat;
use App\Models\User;
use App\Models\Notification;
use App\Models\FCM_Token;

class ChatController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $params = $request->all();

        $posted_data =  $params;
        $posted_data['paginate'] = 10;

        if (isset($params['sender_id']))
            $posted_data['sender_id'] = $params['sender_id'];
        if (isset($params['receiver_id']))
            $posted_data['receiver_id'] = $params['receiver_id'];
        if (isset($params['per_page']))
            $posted_data['paginate'] = $params['per_page'];


        if (isset($params['last_chat_all']) && !isset($params['sender_id'])){
            $error_message['error'] = 'Please enter sender id.';
            return $this->sendError($error_message['error'], $error_message);
        }
            
        
        $chats = Chat::getChats($posted_data);
        $message = !empty($chats) ? 'Chats retrieved successfully.' : 'Chats not found against your query.';

        return $this->sendResponse($chats, $message);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request_data = $request->all(); 
   
        $validator = \Validator::make($request_data, [
            'receiver_id' => 'required|exists:users,id',
            'sender_id' => 'required|exists:users,id',
            'text' => 'required',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);
        }

        if (isset($request->attachment_path)) {
            $response = upload_files_to_storage($request, $request->attachment_path, 'chat_attachments');
            $request_data['attachment_path'] = $response['file_path'];
        }
        $chat = Chat::saveUpdateChat($request_data);
        
        $posted_data = array();
        $posted_data['detail'] = true;
        $posted_data['chat_id'] = $chat['id'];
        $chat_data = Chat::getChats($posted_data);
        $model_response = $chat_data->toArray();
        
        $notification_text = "You have got a new message from ".$model_response['sender_details']['name'].'.';
        $notification_params = array();
        $notification_params['sender'] = $request_data['sender_id'];
        $notification_params['receiver'] = $request_data['receiver_id'];
        $notification_params['slugs'] = "new-chat";
        $notification_params['notification_text'] = $notification_text;
        // $notification_params['seen_by'] = "";
        $notification_params['metadata'] = "receiver_id=".$request_data['receiver_id'];

        // $notification_params['receiver_devices'] = array_column($firebase_devices, 'device_token');
        // $response = Notification::saveUpdateNotification($notification_params);
        
        $response = Notification::saveUpdateNotification($notification_params);

        $firebase_devices = FCM_Token::getFCM_Tokens(['user_id' => $request_data['receiver_id']])->toArray();
        $notification_params['registration_ids'] = array_column($firebase_devices, 'device_token');
        
        $notification = false;
        if ($response) {
            $notification = FCM_Token::sendFCM_Notification([
                'title' => $notification_params['slugs'],
                'body' => $notification_params['notification_text'],
                'metadata' => $notification_params['metadata'],
                'registration_ids' => $notification_params['registration_ids'],
                'details' => $model_response
            ]);
        }

        return $this->sendResponse($chat_data, 'Chat posted successfully.');
    } 
   
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $chat = Chat::find($id);
  
        if (is_null($chat)) {
            $error_message['error'] = 'The chat is not found.';
            return $this->sendError($error_message['error'], $error_message);
        }
   
        return $this->sendResponse($chat, 'Chat retrieved successfully.');
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
        $request_data = $request->all();
        $validator = \Validator::make($request_data, [
            'receiver_id' => 'required|exists:users,id',
            'sender_id' => 'required|exists:users,id',
            'stars' => 'required',
            'description' => 'required'
        ]);
   
        if($validator->fails()){
            return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);
        }

        $posted_data = array();
        $posted_data['detail'] = true;
        $posted_data['id'] = $id;
        $chat = Chat::getChats($posted_data);
        if(!$chat){
            $error_message['error'] = 'The chat is not found.';
            return $this->sendError($error_message['error'], $error_message);
        }
        
        $request_data['update_id'] = $id;
        $chat = Chat::saveUpdateChat($request_data);

        return $this->sendResponse($chat, 'Chat updated successfully.');
    }
   
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(Chat::find($id)){
            Chat::deleteChat($id); 
            return $this->sendResponse([], 'Chat deleted successfully.');
        }else{
            $error_message['error'] = 'The chat is already deleted.';
            return $this->sendError($error_message['error'], $error_message);
        } 
    }
}