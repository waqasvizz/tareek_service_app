<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController as BaseController;
use App\Models\Chat;
use App\Models\User;
use App\Models\Notification;
use App\Models\EmailMessage;
use App\Models\EmailLogs;
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
        if (isset($params['search_chat'])) {
            unset($posted_data['last_chat_all']);
            $posted_data['text'] = $params['search_chat'];
            $posted_data['last_chat'] = true;
            $posted_data['users_join'] = true;
        }

        if (isset($params['last_chat_all']) && !isset($params['sender_id'])){
            $error_message['error'] = 'Please enter the sender id.';
            return $this->sendError($error_message['error'], $error_message);
        }

        if (isset($params['search_chat']) && !isset($params['sender_id'])){
            $error_message['error'] = 'Please enter the sender id.';
            return $this->sendError($error_message['error'], $error_message);
        }

        // echo "Line no @"."<br>";
        // echo "<pre>";
        // print_r($posted_data);
        // echo "</pre>";
        // exit("@@@@");
        
        $chats = Chat::getChats($posted_data);
        if (isset($params['search_chat'])) {
            $result['data'][] = $chats;
            $chats = $result;
        }
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
        $notification_params['metadata'] = "receiver_id=".$request_data['receiver_id'];

        
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

        if (config('app.chat_email')) {
            $data = [
                'subject' => 'New Chat Message - '.config('app.name'),
                'name' => $model_response['receiver_details']['name'],
                'email' => $model_response['receiver_details']['email'],
                'text_line' => 'You have got a new chat message on '.config('app.name'),
            ];

            // for new chat messages email template
            $email_content = EmailMessage::getEmailMessage(['id' => 1, 'detail' => true]);
            
            $email_data = decodeShortCodesTemplate([
                'subject' => $email_content->subject,
                'body' => $email_content->body,
                'email_message_id' => 1,
                'sender_id' => $request_data['sender_id'],
                'receiver_id' => $request_data['receiver_id'],
            ]);

            EmailLogs::saveUpdateEmailLogs([
                'email_msg_id' => 1,
                'sender_id' => $model_response['sender_details']['id'],
                'receiver_id' => $model_response['receiver_details']['id'],
                'email' => $model_response['receiver_details']['email'],
                'subject' => $email_data['email_subject'],
                'email_message' => $email_data['email_body'],
                'send_email_after' => 1, // 1 = Daily Email
            ]);

            /*
            \Mail::send('emails.general_email', ['email_data' => $data], function($message) use ($data) {
                $message->to($data['email'])
                        ->subject($data['subject']);
            });
            */
        }

        return $this->sendResponse($model_response, 'Chat posted successfully.');
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
        $data = Chat::find($id);
        if($data){
            $attachQuery = Chat::latest()->whereNotNull('attachment_path')->get();
            foreach($attachQuery as $filePath){
                delete_files_from_storage($filePath['attachment_path']);
            }

            
            $del_query = Chat::latest();
            $del_query = $del_query->orWhere(function ($del_query) use ($data) {
                $del_query->where('sender_id', '=', $data['sender_id'])
                    ->where('receiver_id', '=', $data['receiver_id']);
            });
            $del_query = $del_query->orWhere(function ($del_query) use ($data) {
                $del_query->where('sender_id', '=', $data['receiver_id'])
                    ->where('receiver_id', '=', $data['sender_id']);
            });
            $del_query->delete();

            return $this->sendResponse([], 'Chat deleted successfully.');
        }else{
            $error_message['error'] = 'The chat is already deleted.';
            return $this->sendError($error_message['error'], $error_message);
        } 
    }



    public function read_chats(Request $request)
    {
        $request_data = $request->all(); 
   
        $validator = \Validator::make($request_data, [
            'chat_id' => 'required|exists:chats,id',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);
        }

        $request_data['update_id'] = $request_data['chat_id'];
        $request_data['seen_at'] = date('Y-m-d h:i:s');

        $check_read_chat = Chat::where('id', $request_data['update_id'])->whereNull('seen_at')->first();
        if($check_read_chat){
            $chat = Chat::saveUpdateChat($request_data);
            return $this->sendResponse($chat, 'Chat read successfully.');
        }else{
            $error_message['error'] = 'Chat already read successfully.';
            return $this->sendError($error_message['error'], $error_message);
        }
    } 
}