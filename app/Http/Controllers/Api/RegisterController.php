<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Validator;
use DB;
use App\Models\User;
use App\Models\Service;
use App\Models\AssignService;
use App\Models\StorageAssets;
use App\Models\UserAssets;

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
            'full_name'         => 'nullable|max:50',
            'date_of_birth'     => 'nullable|numeric',
            'address'           => 'nullable|max:100',
            'email'             => 'required|email|unique:users',
            'phone_number'      => 'nullable|max:15',
            'company_name'      => 'nullable|max:50',
            'company_number'    => 'nullable|max:15',
            'password'          => 'required|min:8',
            'confirm_password'  => 'required|required_with:password|same:password'
            
            // 'phone_number' => 'nullable|regex:/^([0-9\s\-\+\(\)]*)$/|min:12',
            // 'user_role' => 'required',
            // 'user_name' => 'required',
        );
        
        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());  
        }
        else {

            $register_data = array();
            $documents_arr = array();

            if( $posted_data['role'] != 2 && $posted_data['role'] != 3 ){
                $error_message['role'] = 'You entered the invalid role.';
                return $this->sendError('Validation Error.', $error_message);  
            }

            if($posted_data['role'] == 2 && (!isset($posted_data['full_name']) || empty($posted_data['full_name']))){
                $error_message['full_name'] = 'Please enter the full name for the customer.';
                return $this->sendError('Validation Error.', $error_message);  
            }

            if($posted_data['role'] == 2 && (!isset($posted_data['phone_number']) || empty($posted_data['phone_number']))){
                $error_message['phone_number'] = 'Please enter the phone number for the customer.';
                return $this->sendError('Validation Error.', $error_message);  
            }

            if($posted_data['role'] == 2 && (!isset($posted_data['date_of_birth']) || empty($posted_data['date_of_birth']))){
                $error_message['date_of_birth'] = 'Please enter the date of birth for the customer.';
                return $this->sendError('Validation Error.', $error_message);
            }

            if(!isset($posted_data['email']) || empty($posted_data['email'])){
                $error_message['email'] = 'Please enter the email address.';
                return $this->sendError('Validation Error.', $error_message);  
            }

            if($posted_data['role'] == 3 && (!isset($posted_data['company_name']) || empty($posted_data['company_name']))){
                $error_message['company_name'] = 'Please enter the company name for the Supplier.';
                return $this->sendError('Validation Error.', $error_message);
            }

            if($posted_data['role'] == 3 && (!isset($posted_data['company_number']) || empty($posted_data['company_number']))){
                $error_message['company_number'] = 'Please enter the company contact number for the Supplier.';
                return $this->sendError('Validation Error.', $error_message);
            }
            
            if($posted_data['role'] == 3 && (!isset($posted_data['company_documents']) || empty($posted_data['company_documents']))){
                $error_message['company_documents'] = 'Please enter the company documents for the Supplier.';
                return $this->sendError('Validation Error.', $error_message);
            }

            $posted_data['account_status'] = $posted_data['role'] == 3 ? 'no' : 'yes';
            $posted_data['user_type'] = 'app';
            $user_id = $this->UserObj->saveUpdateUser($posted_data);
        
            $message = ($user_id) > 0 ? 'User is successfully registered.' : 'Something went wrong during registration.';
            if ($user_id) {

                if (isset($request->company_documents)) {
                    $allowedfileExtension = ['jpeg','jpg','png','pdf'];
                    foreach($request->company_documents as $mediaFiles) {

                        $extension = $mediaFiles->getClientOriginalExtension();

                        $check = in_array($extension, $allowedfileExtension);
                        if($check) {
                            $response = upload_files_to_storage($request, $mediaFiles, 'other_assets');

                            if( isset($response['action']) && $response['action'] == true ) {
                                $arr = [];
                                $arr['file_name'] = isset($response['file_name']) ? $response['file_name'] : "";
                                $arr['file_path'] = isset($response['file_path']) ? $response['file_path'] : "";
                            }

                            $asset_id = StorageAssets::saveUpdateStorageAssets([
                                'filepath' => $arr['file_path'],
                                'filename' => $arr['file_name'],
                                'mimetypes' => $mediaFiles->getClientMimeType(),
                            ]);

                            $arr['asset_id'] = $asset_id;
                            $documents_arr[] = $arr;
                        }
                        else {
                            return response()->json(['invalid_file_format'], 422);
                        }
                    }

                    foreach ($documents_arr as $key => $item) {
                        UserAssets::saveUpdateUserAssets([
                            'user_id' => $user_id,
                            'storage_id' => $item['asset_id'],
                        ]);
                    }
                }
                return $this->sendResponse([], $message);
            }
            else
                return $this->sendError($message);
        }
    }
   
    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
    public function loginUser(Request $request)
    {
        $posted_data = $request->all();
        $user_data = array();
        
        if ( ( isset($posted_data['facebook_id']) || isset($posted_data['gmail_id']) ) && isset($posted_data['email']) && !isset($posted_data['password']) ) {

            $user_data['email'] = $posted_data['email'];
            $user_data['detail'] = true;
            $user_data = $this->UserObj->getUser($user_data);

            if ( isset($user_data->id) && isset($user_data->user_type) && $user_data->user_type != 'app' ) {
                $response = $this->authorizeUser([
                    'email' => $posted_data['email'],
                    'password' => isset($posted_data['password']) ? $posted_data['password'] : '12345678@d'
                ]);

                if ($response)
                    return $this->sendResponse($response, 'User login successfully.');
                else
                    return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
            }
            else {

                $user_data = array();
                $user_data['email'] = $posted_data['email'];
                $user_data['role'] = 2;
                $user_data['account_status'] = $user_data['role'] == 3 ? 'no' : 'yes';
                $user_data['password'] = '12345678@d';
                
                if ( isset($posted_data['facebook_id']) && !isset($posted_data['gmail_id']) )
                    $user_data['user_type'] = 'facebook';
                if ( !isset($posted_data['facebook_id']) && isset($posted_data['gmail_id']) )
                    $user_data['user_type'] = 'gmail';
    
                $user_id = $this->UserObj->saveUpdateUser($user_data);
                
                if ($user_id) {
                    $response = $this->authorizeUser([
                        'email' => $posted_data['email'],
                        'password' => isset($posted_data['password']) ? $posted_data['password'] : '12345678@d'
                    ]);
    
                    if ($response)
                        return $this->sendResponse($response, 'User login successfully.');
                    else
                        return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
                }
            }
        }

        else if ( isset($posted_data['email']) && isset($posted_data['password']) ) {

            $response = $this->authorizeUser([
                'email' => isset($posted_data['email']) ? $posted_data['email'] : 'xyz@admin.com',
                'password' => isset($posted_data['password']) ? $posted_data['password'] : '12345678@d'
            ]);

            if ($response)
                return $this->sendResponse($response, 'User login successfully.');
            else
                return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);

        }
        else {
            $error_message['email'] = 'Please post the valid credentials for login.';
            return $this->sendError('Validation Error.', $error_message);
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

        if(Auth::attempt(['email' => $email, 'password' => $password])){ 
            $user = Auth::user();
            $response =  $user;
            $response['token'] =  $user->createToken('MyApp')->accessToken;

            return $response;
        }
        else{
            return false;
        }
    }


    public function forgotPassword(Request $request)
    {
        $rules = array(
            'email' => 'required|email',
        );
        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());  
        } else {

            $users = User::where('email', '=', $request->input('email'))->first();
            if ($users === null) {

                $error_message['email'] = 'We do not recognize this email address. Please try again.';
                return $this->sendError('Validation Error.', $error_message); 
            } else {
                $random_hash = substr(md5(uniqid(rand(), true)), 10, 10); 
                $email = $request->get('email');
                $password = Hash::make($random_hash);

                DB::update('update users set password = ? where email = ?',[$password,$email]);

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

    public function logoutUser(Request $request)
    {
        exit('ceeeee');
        exit($request->user()->token());
        // $request->user()->token()->revoke();
        // Auth::logout();
        return $this->sendResponse([], 'User is successfully logout.');
    }

    public function getProfile(Request $request)
    {
        if (!empty(Auth::user()) ) {
            $user = Auth::user();
            return $this->sendResponse($user, 'User profile is successfully loaded.');
        }
        else {
            return $this->sendError('Please login to get profile data.', []);
        }
    }
}