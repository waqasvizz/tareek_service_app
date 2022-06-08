<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Models\Service;
use App\Models\AssignService;
use App\Models\StorageAssets;
use App\Models\UserAssets;
use App\Models\Notification;
use App\Models\FCM_Token;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class RegisterController extends BaseController
{
    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function registerUser(Request $request)
    {
        $posted_data = $request->all();
        $rules = array(
            'role'              => 'required',
            'user_type'         => 'required',
            'full_name'         => 'nullable|max:50',
            // 'date_of_birth'     => 'nullable|date_format:Y-m-d',
            'date_of_birth'     => 'nullable',
            'address'           => 'nullable|max:100',
            'email'             => $posted_data['user_type'] != 'app' ? 'required|email' : 'required|email|unique:users',
            'phone_number'      => 'nullable|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
            'company_name'      => 'nullable|max:50',
            'company_number'    => 'nullable|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
            /*

            'email'             => 'required|email|unique:users',
            'password'          => 'required|min:8',
            'confirm_password'  => 'required|required_with:password|same:password'
            */
            
            // 'phone_number' => 'nullable|regex:/^([0-9\s\-\+\(\)]*)$/|min:12',
            // 'user_role' => 'required',
            // 'user_name' => 'required',
        );
        
        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);
        }
        else {
            $register_data = array();
            $documents_arr = array();

            if( $posted_data['role'] != 2 && $posted_data['role'] != 3 && $posted_data['role'] != 4 ){
                $error_message['error'] = 'You entered the invalid role.';
                return $this->sendError($error_message['error'], $error_message);  
            }

            if($posted_data['role'] != 2 && isset($posted_data['user_type']) && $posted_data['user_type'] != 'app') {
                $error_message['error'] = 'Sorry, only customers can login using social accounts.';
                return $this->sendError($error_message['error'], $error_message);
            }

            if((!isset($posted_data['full_name']) || empty($posted_data['full_name']))){
                $error_message['error'] = 'Please enter the full name for the customer.';
                return $this->sendError($error_message['error'], $error_message);  
            }
            
            if(isset($posted_data['user_type']) && $posted_data['user_type'] == 'app') {
            
                if((!isset($posted_data['phone_number']) || empty($posted_data['phone_number']))){
                    $error_message['error'] = 'Please enter the phone number for the customer.';
                    return $this->sendError($error_message['error'], $error_message);   
                }

                if((!isset($posted_data['date_of_birth']) || empty($posted_data['date_of_birth']))){
                    $error_message['error'] = 'Please enter the date of birth for the customer.';
                    return $this->sendError($error_message['error'], $error_message);  
                }

                if( empty($posted_data['password']) || empty($posted_data['confirm_password']) ) {
                    $error_message['error'] = 'The password and confirm password must not be empty.';
                    return $this->sendError($error_message['error'], $error_message);
                }

                if( isset($posted_data['password']) && isset($posted_data['confirm_password']) ) {
                    if ( $posted_data['password'] != $posted_data['confirm_password'] ) {
                        $error_message['error'] = 'The password and confirm password must be same.';
                        return $this->sendError($error_message['error'], $error_message);
                    }
                }
            }
            else {
                if( isset($posted_data['user_type']) && ( $posted_data['user_type'] == 'facebook' || $posted_data['user_type'] == 'google' ) ){

                    $user_data = array();
                    $user_data['email'] = $posted_data['email'];
                    $user_data['detail'] = true;
                    $user_data = $this->UserObj->getUser($user_data);

                    if ( isset($user_data['id']) && isset($user_data['user_type']) ) {

                        $user = User::where('email', $posted_data['email'])->first();    
                        if (Auth::loginUsingId($user->id)){
                            $user = Auth::user();
                            $response =  $user;
                            $response['token'] =  $user->createToken('MyApp')->accessToken;
                        }else {
                            $response = false;
                        }

                        if ($response)
                            return $this->sendResponse($response, 'User login successfully.');
                        else {
                            $error_message['error'] = 'This email has already been registered.';
                            return $this->sendError($error_message['error'], $error_message);
                        }
                    }
                    else {
        
                        $user_data = array();
                        $user_data['name'] = $posted_data['full_name'];
                        $user_data['email'] = $posted_data['email'];
                        $user_data['role'] = 2;
                        $user_data['account_status'] = $user_data['role'] == 2 ? 'Active' : 'Block';
                        $user_data['password'] = '12345678@d';
                        $user_data['user_type'] = $posted_data['user_type'];
                        $user_data['email_verified_at'] = date('Y-m-d h:i:s');
            
                        $user_id = $this->UserObj->saveUpdateUser($user_data);
                        
                        if ($user_id) {
                            $response = $this->authorizeUser([
                                'email' => $posted_data['email'],
                                'password' => isset($posted_data['password']) ? $posted_data['password'] : '12345678@d'
                            ]);

                            $notification_text = "A new user has been register into the app.";
    
                            $notification_params = array();
                            $notification_params['sender'] = $user_id->id;
                            $notification_params['receiver'] = 1;
                            $notification_params['slugs'] = "new-user";
                            $notification_params['notification_text'] = $notification_text;
                            $notification_params['metadata'] = "user_id=$user_id";
                            
                            $notif_response = Notification::saveUpdateNotification([
                                'sender' => $notification_params['sender'],
                                'receiver' => $notification_params['receiver'],
                                'slugs' => $notification_params['slugs'],
                                'notification_text' => $notification_params['notification_text'],
                                'metadata' => $notification_params['metadata']
                            ]);
                    
                            $firebase_devices = FCM_Token::getFCM_Tokens(['user_id' => $notification_params['receiver']])->toArray();
                            $notification_params['registration_ids'] = array_column($firebase_devices, 'device_token');
                    
                            if ($notif_response) {
                    
                                $notification = FCM_Token::sendFCM_Notification([
                                    'title' => $notification_params['slugs'],
                                    'body' => $notification_params['notification_text'],
                                    'metadata' => $notification_params['metadata'],
                                    'registration_ids' => $notification_params['registration_ids'],
                                    'details' => []
                                ]);
                            }

                            $data = [
                                'subject' => 'Welcome Email',
                                'name' => $request->get('full_name'),
                                'email' => $request->get('email'),
                                'token' => '',
                            ];

                            Mail::send('emails.welcome_social', ['email_data' => $data], function($message) use ($data) {
                                $message->to($data['email'])
                                        ->subject($data['subject']);
                            });
            
                            if ($response)
                                return $this->sendResponse($response, 'User login successfully.');
                            else
                                $error_message['error'] = 'The user credentials are not valid.';
                                return $this->sendError($error_message['error'], $error_message);
                        }
                    }
                }
            }

            if(!isset($posted_data['email']) || empty($posted_data['email'])){
                $error_message['error'] = 'Please enter the email address.';
                return $this->sendError($error_message['error'], $error_message); 
            }

            // if($posted_data['role'] == 3 && (!isset($posted_data['company_type']) || empty($posted_data['company_type']))){
            //     $error_message['error'] = 'Please enter the company contact type for the Supplier.';
            //     return $this->sendError($error_message['error'], $error_message);
            // }

            if($posted_data['role'] == 3 && (!isset($posted_data['company_name']) || empty($posted_data['company_name']))){
                $error_message['error'] = 'Please enter the company name for the Supplier.';
                return $this->sendError($error_message['error'], $error_message);  
            }

            if($posted_data['role'] == 3 && (!isset($posted_data['company_number']) || empty($posted_data['company_number']))){
                $error_message['error'] = 'Please enter the company contact number for the Supplier.';
                return $this->sendError($error_message['error'], $error_message);  
            }
            
            if( ($posted_data['role'] == 3 || $posted_data['role'] == 4) && (!isset($posted_data['company_documents']) || empty($posted_data['company_documents']))){
                $error_message['error'] = 'Please post the related documents.';
                return $this->sendError($error_message['error'], $error_message);
            }

            $posted_data['account_status'] = $posted_data['role'] == 3 ? 2 : 1;
            $posted_data['user_type'] = 1; //app

            $token = Str::random(64);
            $posted_data['email_token'] = $token;

            if (isset($request->company_documents)) {
                $allowedfileExtension = ['jpeg','jpg','png','pdf'];
                foreach($request->company_documents as $mediaFiles) {
                    $extension = strtolower($mediaFiles->getClientOriginalExtension());
                    $check = in_array($extension, $allowedfileExtension);
                    if(!$check) {
                        $error_message['error'] = 'Invalid file format you can only add jpg, jpeg, png and pdf file format.';
                        return $this->sendError($error_message['error'], $error_message);
                    }
                }
            }

            $user_detail = $this->UserObj->saveUpdateUser($posted_data);
            $user_id = $user_detail->id;

            $login_response = $this->authorizeUser([
                'email' => $posted_data['email'],
                'password' => isset($posted_data['password']) ? $posted_data['password'] : '12345678@d'
            ]);
            
            $message = ($user_id) > 0 ? 'User is successfully registered.' : 'Something went wrong during registration.';
            if ($user_id) {

                if (isset($request->company_documents)) {
                    $allowedfileExtension = ['jpeg','jpg','png','pdf'];
                    foreach($request->company_documents as $mediaFiles) {

                        $extension = strtolower($mediaFiles->getClientOriginalExtension());
                        
                        $check = in_array($extension, $allowedfileExtension);
                        if($check) {

                            $response = upload_files_to_storage($request, $mediaFiles, 'other_assets');

                            if( isset($response['action']) && $response['action'] == true ) {
                                $arr = [];
                                $arr['file_name'] = isset($response['file_name']) ? $response['file_name'] : "";
                                $arr['file_path'] = isset($response['file_path']) ? $response['file_path'] : "";
                            }

                            $asset_id = UserAssets::saveUpdateUserAssets([
                                'user_id'       => $user_id,
                                'asset_type'    => 1,
                                'filepath'      => $arr['file_path'],
                                'filename'      => $arr['file_name'],
                                'mimetypes'     => $mediaFiles->getClientMimeType(),
                                'asset_status'  => 0,
                                'asset_view'    => 0,
                            ]);

                            $arr['asset_id'] = $asset_id;
                            $documents_arr[] = $arr;
                        }
                        else {
                            $error_message['error'] = 'Invalid file format you can only add jpg, jpeg, png and pdf file format.';
                            return $this->sendError($error_message['error'], $error_message);
                        }
                    }
                }
                $user_detail = $this->UserObj->getUser([
                    'id'       => $user_id,
                    'detail'       => true
                ]);
                
                $notification_text = "A new user has been register into the app.";
    
                $notification_params = array();
                $notification_params['sender'] = $user_id;
                $notification_params['receiver'] = 1;
                $notification_params['slugs'] = "new-user";
                $notification_params['notification_text'] = $notification_text;
                $notification_params['metadata'] = "user_id=$user_id";
                
                $response = Notification::saveUpdateNotification([
                    'sender' => $notification_params['sender'],
                    'receiver' => $notification_params['receiver'],
                    'slugs' => $notification_params['slugs'],
                    'notification_text' => $notification_params['notification_text'],
                    'metadata' => $notification_params['metadata']
                ]);
        
                $firebase_devices = FCM_Token::getFCM_Tokens(['user_id' => $notification_params['receiver']])->toArray();
                $notification_params['registration_ids'] = array_column($firebase_devices, 'device_token');
        
                if ($response) {
        
                    if ( isset($model_response['user']) )
                        unset($model_response['user']);
                    if ( isset($model_response['post']) )
                        unset($model_response['post']);
        
                    $notification = FCM_Token::sendFCM_Notification([
                        'title' => $notification_params['slugs'],
                        'body' => $notification_params['notification_text'],
                        'metadata' => $notification_params['metadata'],
                        'registration_ids' => $notification_params['registration_ids'],
                        'details' => $user_detail
                    ]);
                }

                $data = [
                    'subject' => 'Email Verification',
                    'name' => $request->get('full_name'),
                    'email' => $request->get('email'),
                    'token' => $token,
                ];

                Mail::send('emails.welcome_email', ['email_data' => $data], function($message) use ($data) {
                    $message->to($data['email'])
                            ->subject($data['subject']);
                });
                
                $admin_data['id'] = 1;
                $admin_data['detail'] = true;
                $response = $this->UserObj->getUser($admin_data);

                if ($response) {
                    $data = [
                        'subject' => 'New User Registered',
                        'name' => $response->name,
                        'email' => $response->email,
                        'text_line' => "A new user ".$request->get('full_name')." has been registered on ".config('app.name'),
                    ];

                    Mail::send('emails.general_email', ['email_data' => $data], function($message) use ($data) {
                        $message->to($data['email'])
                                ->subject($data['subject']);
                    });
                }

                $user_detail['token'] = isset($login_response['token']) ? $login_response['token'] : '';
                return $this->sendResponse($user_detail, $message);
            }
            else {
                $error_message['error'] = $message;
                return $this->sendError($error_message['error'], $error_message);
            }
        }
    }
   
    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
    public function loginUser(Request $request)
    {
        $user_data = array();
        $posted_data = $request->all();

        $rules = array(
            'email'             => 'required|email',
            'password'          => 'required',
        );
        
        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);
        }

        if ( ( isset($posted_data['facebook_id']) || isset($posted_data['gmail_id']) ) && isset($posted_data['email']) && !isset($posted_data['password']) ) {

            $user_data['email'] = $posted_data['email'];
            $user_data['detail'] = true;
            $user_data = $this->UserObj->getUser($user_data);

            if ( isset($user_data->id) && isset($user_data->user_type) && $user_data->user_type != 1 ) {
                $response = $this->authorizeUser([
                    'email' => $posted_data['email'],
                    'password' => isset($posted_data['password']) ? $posted_data['password'] : '12345678@d'
                ]);

                if ($response){
                    return $this->sendResponse($response, 'User login successfully.');
                }
                else{
                    $error_message['error'] = 'Unauthorised';
                    return $this->sendError($error_message['error'], $error_message);
                }
            }
            else {

                $user_data = array();
                $user_data['email'] = $posted_data['email'];
                $user_data['role'] = 2;
                $user_data['account_status'] = 1;
                $user_data['password'] = '12345678@d';
                
                if ( isset($posted_data['facebook_id']) && !isset($posted_data['gmail_id']) )
                    $user_data['user_type'] = 2; //facebook;
                if ( !isset($posted_data['facebook_id']) && isset($posted_data['gmail_id']) )
                    $user_data['user_type'] = 3; //google;
    
                $user_detail = $this->UserObj->saveUpdateUser($user_data);
                $user_id = $user_detail->id;
                if ($user_id) {
                    $response = $this->authorizeUser([
                        'email' => $posted_data['email'],
                        'password' => isset($posted_data['password']) ? $posted_data['password'] : '12345678@d'
                    ]);
    
                    if ($response){
                        return $this->sendResponse($response, 'User login successfully.');
                    }
                    else{
                        $error_message['error'] = 'Unauthorised';
                        return $this->sendError($error_message['error'], $error_message);
                    }
                }
            }
        }

        else if ( isset($posted_data['email']) && isset($posted_data['password']) ) {

            $response = $this->authorizeUser([
                'email' => isset($posted_data['email']) ? $posted_data['email'] : 'xyz@admin.com',
                'password' => isset($posted_data['password']) ? $posted_data['password'] : '12345678@d'
            ]);
    
            if ($response){
                return $this->sendResponse($response, 'User login successfully.');
            }
            else{
                $error_message['error'] = 'Please enter correct email and password.';
                return $this->sendError($error_message['error'], $error_message);
            }
        }
        else {

            if( (!isset($posted_data['email']) || empty($posted_data['email'])) ){
                $error_message['error'] = 'The email address is required.';
                return $this->sendError($error_message['error'], $error_message);  
            }

            if( (!isset($posted_data['password']) || empty($posted_data['password'])) ){
                $error_message['error'] = 'The password is required.';
                return $this->sendError($error_message['error'], $error_message);  
            }

            $error_message['error'] = 'Please post the valid credentials for login.';
            return $this->sendError($error_message['error'], $error_message);
        }
    }


    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
    public function authorizeUser($posted_data)
    {
        $email = isset($posted_data['email']) ? $posted_data['email'] : '';
        $password = isset($posted_data['password']) ? $posted_data['password'] : '';

        if(\Auth::attempt(['email' => $email, 'password' => $password])){ 
            $user = \Auth::user();
            $response =  $user;

            if ( isset($posted_data['mode']) && $posted_data['mode'] == 'only_validate' ) {
                return $response;
            }

            $response['token'] =  $user->createToken('MyApp')->accessToken;
            return $response;
        }
        else{
            return false;
        }
    }

    public function verifyUserEmail($token){

        $where_query = array(['remember_token', '=', isset($token) ? $token : 0]);
        $verifyUser = User::where($where_query)->first();

        $email_data = [
            'name' => isset($verifyUser->name) ? $verifyUser->name : 'Dear User',
            'text_line' => 'This verfication code is invalid. Please contact to the customer support',
        ];
  
        if($verifyUser){
            if($verifyUser->email_verified_at == NULL) {
                
                $model_response = User::saveUpdateUser([
                    'update_id' => $verifyUser->id,
                    'remember_token' => NULL,
                    'email_verified_at' => date('Y-m-d h:i:s')
                ]);

                if (!empty($model_response)) {
                    $email_data = [
                        'name' => $verifyUser->name,
                        'text_line' => 'Congratulations! You email is successfully verified. Welcome to '.config('app.name'),
                    ];
                }
            }
            else {
                $email_data = [
                    'name' => $verifyUser->name,
                    'text_line' => 'Your email is already verified. Welcome to '.config('app.name'),
                ];
            }
        }
        return view('emails.general_email', compact('email_data'));
    }

    public function forgotPassword(Request $request)
    {
        $rules = array(
            'email' => 'required|email',
        );
        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);     
        } else {

            $users = User::where('email', '=', $request->input('email'))->first();
            if ($users === null) {

                $error_message['error'] = 'We do not recognize this email address. Please try again.';
                return $this->sendError($error_message['error'], $error_message);
            } else {
                $random_hash = substr(md5(uniqid(rand(), true)), 10, 10); 
                $email = $request->get('email');
                $password = Hash::make($random_hash);

                \DB::update('update users set password = ? where email = ?',[$password,$email]);

                $data = [
                    'new_password' => $random_hash,
                    'subject' => 'Reset Password',
                    'email' => $email
                ];

                Mail::send('emails.reset_password', $data, function($message) use ($data) {
                    $message->to($data['email'])
                    ->subject($data['subject']);
                });

                return $this->sendResponse($data, 'Your password has been reset. Please check your email.');

            }

        }
    }

    public function changePassword(Request $request)
    {
        $params = $request->all();
        $rules = array(
            'email'             => 'required|email',
            'old_password'      => 'required',
            'new_password'      => 'required|min:4',
            'confirm_password'  => 'required|required_with:new_password|same:new_password'
        );
        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);     
        }

        // $user_detail = $this->UserObj->getUser(['email' => $params['email'], 'detail' => true]);
        // echo "Line no deee@"."<br>";
        // echo "<pre>";
        // print_r($user_detail);
        // echo "</pre>";
        // exit("@@@@");

        $response = $this->authorizeUser([
            'email' => $params['email'],
            'password' => $params['old_password'],
            'mode' => 'only_validate',
        ]);

        if ($params['old_password'] == $params['new_password']) {
            $error_message['error'] = 'New and old password must be different.';
            return $this->sendError($error_message['error'], $error_message);
        }

        if (!$response) {
            $error_message['error'] = 'Your old password is incorrect.';
            return $this->sendError($error_message['error'], $error_message);
        }
        else {
            $new_password = $params['confirm_password'];
            $email = $request->get('email');
            $password = Hash::make($new_password);

            \DB::update('update users set password = ? where email = ?',[$password,$email]);

            // $data = [
            //     'new_password' => $new_password,
            //     'subject' => 'Reset Password',
            //     'email' => $email
            // ];

            // Mail::send('emails.reset_password', $data, function($message) use ($data) {
            //     $message->to($data['email'])
            //     ->subject($data['subject']);
            // });

            return $this->sendResponse([], 'Your password has been updated.');
        }
    }

    public function logoutUser(Request $request)
    {
        if (!empty(\Auth::user()) ) {
            $user = \Auth::user()->token();
            $user->revoke();
        }
        return $this->sendResponse([], 'User is successfully logout.');
    }

    public function getProfile(Request $request)
    {
        if (!empty(\Auth::user()) ) {
             
            $posted_data = array();
            $posted_data['id'] = \Auth::user()->id; 
            $posted_data['detail'] = true;
            $user = User::getUser($posted_data);
            return $this->sendResponse($user, 'User profile is successfully loaded.');
        }
        else {
            $error_message['error'] = 'Please login to get profile data.';
            return $this->sendError($error_message['error'], $error_message);
        }
    }
}