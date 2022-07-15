<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController as BaseController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Service;
use App\Models\Product;
use App\Models\Notification;
use App\Models\FCM_Token;
use App\Models\PaymentTransaction;
use App\Models\Order;

class UserController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $request_data = $request->all();
        $request_data['paginate'] = 10;
        if (isset($request_data['per_page']))
            $request_data['paginate'] = $request_data['per_page'];

        if ( !isset($request_data['id']) ) {
            $request_data['users_not_in'] = [1];
        }
        
        $user = User::getUser($request_data);
        $message = count($user) > 0 ? 'Users retrieved successfully.' : 'Users not found against your query.';

        return $this->sendResponse($user, $message);
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
            'name'    => 'required',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);   
        }

        $user = User::saveUpdateUser($request_data);

        if ( isset($user->id) ){
            return $this->sendResponse($user, 'User is successfully added.');
        }else{
            $error_message['error'] = 'Somthing went wrong during query.';
            return $this->sendError($error_message['error'], $error_message);  
        }
    } 
   
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::find($id);
  
        if (is_null($user)) {
            $error_message['error'] = 'User not found.';
            return $this->sendError($error_message['error'], $error_message);  
        }
   
        return $this->sendResponse($user, 'User retrieved successfully.');
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
        $request_data['update_id'] = $id;
   
        $validator = \Validator::make($request_data, [
            'update_id' => 'required|exists:users,id',
            // 'full_name'    => 'required',
        ],[
            'update_id.exists' => 'Updated record not exists',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);   
        }


        if (isset($request->profile_image)) {
            
            $allowedfileExtension = ['jpg','jpeg','png'];
            $extension = strtolower($request->profile_image->getClientOriginalExtension());

            $check = in_array($extension, $allowedfileExtension);
            if($check) {
                $response = upload_files_to_storage($request, $request->profile_image, 'profile_image');
                $request_data['profile_image'] = $response['file_path'];

                if( isset($response['action']) && $response['action'] == true ) {
                    
                    $img_data['file_name'] = isset($response['file_name']) ? $response['file_name'] : "";
                    $img_data['file_path'] = isset($response['file_path']) ? $response['file_path'] : "";
                }
            }
            else {
                $error_message['error'] = 'Invalid file format you can only add jpg,jpeg and png file format.';
                return $this->sendError($error_message['error'], $error_message);
            }
        }

        $user = User::saveUpdateUser($request_data);

        if ( isset($user->id) ){

            if ( isset($request_data['account_status']) ) {

                if ($request_data['account_status'] == 1)
                    $acc_status = 'activated';
                else if ($request_data['account_status'] == 2)
                    $acc_status = 'blocked';

                $notification_text = "Hi ".ucwords($user->name).", Your acount has been ".$acc_status." by Admin!.";
                // $user_id = \Auth::user()->id;

                $notification_params = array();
                $notification_params['sender'] = 1;
                $notification_params['receiver'] = $user->id;
                $notification_params['slugs'] = "user-update";
                $notification_params['notification_text'] = $notification_text;
                $notification_params['metadata'] = "user_id=$user->id";
                
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
                        'details' => $user
                    ]);
                }
            }

            return $this->sendResponse($user, 'User is successfully updated.');
        }else{
            $error_message['error'] = 'Somthing went wrong during query.';
            return $this->sendError($error_message['error'], $error_message);  
        }
    }
   
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $response = User::deleteUser($id);
        if($response) {
            return $this->sendResponse([], 'User deleted successfully.');
        }
        else {
            $error_message['error'] = 'User already deleted / Not found in database.';
            return $this->sendError($error_message['error'], $error_message);  
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function get_dashboards(Request $request)
    {
        $request_data = $request->all();

        $validator = \Validator::make($request_data, [
            'user_id' => 'required|exists:users,id',
        ],[
            'user_id.exists' => 'User id is not exists',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);
        }
        
        $data = array();
        // my model
        $posted_data = array();
        $posted_data['id'] = $request_data['user_id'];
        $posted_data['detail'] = true;
        $response = User::getUser($posted_data);

        if ($response->role->id == 1) {
            $posted_data = array();
            $posted_data['count'] = true;
            $data['total_users'] = User::getUser($posted_data);
    
            $posted_data = array();
            $posted_data['sumBy_column'] = true;
            $posted_data['sumBy_columnName'] = 'total_amount_captured';
            $data['total_earnings'] = PaymentTransaction::getPaymentTransaction($posted_data);
    
            $posted_data = array();
            $posted_data['count'] = true;
            $data['total_services'] = Service::getServices($posted_data);
    
            $posted_data = array();
            $posted_data['count'] = true;
            $data['total_products'] = Product::getProducts($posted_data);
        }

        if ($response->role->id == 1 || $response->role->id == 3) {
            $posted_data = array();
            $posted_data['count'] = true;
            
            if ($response->role->id == 3) {
                $posted_data['receiver_user_id'] = $request_data['user_id'];
            }

            $posted_data['filter_by_date'] = date('Y-m-d H:i:s', strtotime("-1 day"));
            $data['transactions']['today'] = PaymentTransaction::getPaymentTransaction($posted_data);
            $posted_data['filter_by_date'] = date('Y-m-d H:i:s', strtotime("-30 day"));
            $data['transactions']['monthly'] = PaymentTransaction::getPaymentTransaction($posted_data);
            $posted_data['filter_by_date'] = date('Y-m-d H:i:s', strtotime("-365 day"));
            $data['transactions']['yearly'] = PaymentTransaction::getPaymentTransaction($posted_data);

            $posted_data = array();
            $posted_data['count'] = true;

            if ($response->role->id == 3) {
                $posted_data['receiver_id'] = $request_data['user_id'];
            }

            $posted_data['order_status'] = 1;
            $data['requests']['pending'] = Order::getOrder($posted_data);
            $posted_data['order_status'] = 2;
            $data['requests']['accepted'] = Order::getOrder($posted_data);
            $posted_data['order_status'] = 3;
            $data['requests']['rejected'] = Order::getOrder($posted_data);
            $posted_data['order_status'] = 4;
            $data['requests']['on_the_way'] = Order::getOrder($posted_data);
            $posted_data['order_status'] = 5;
            $data['requests']['in_progres'] = Order::getOrder($posted_data);
            $posted_data['order_status'] = 6;
            $data['requests']['completed'] = Order::getOrder($posted_data);

            $posted_data = array();
            $posted_data['count'] = true;

            if ($response->role->id == 3) {
                $posted_data['receiver_id'] = $request_data['user_id'];
            }
            
            $data['requests']['total'] = Order::getOrder($posted_data);
        }
        
        return $this->sendResponse($data, 'Dashboard items successfully fetched.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function get_revenue_stats(Request $request)
    {
        $stats = array();
        $request_data = $request->all();
   
        $validator = \Validator::make($request_data, [
            'user_id'      => 'required',
            'result_by'    => 'required|in:gross,order,supplier',
        ],[
            'user_id.in' => 'Sorry, only admin have rights to access this info.'
        ]);
   
        if($validator->fails()){
            return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);    
        }
        
        $posted_data = array();
        // $posted_data['print_query'] = true;
        if ($request_data['result_by'] == 'gross') {
            $posted_data['without_with'] = true;
            $posted_data['order_status_not_in'] = [1,3,4,5,6]; // means only order 2 status will be fetched which are accepted
            $posted_data['show_only_sums'] = true;
            $posted_data['sumBy_multiple_column'] = true;
            $posted_data['sumBy_multiple_columnNames'] = ['admin_gross' => 'admin_gross_sum', 'supplier_gross' => 'supplier_gross_sum'];
        }
        else if ($request_data['result_by'] == 'order') {
            // $posted_data['without_with'] = true;
            $posted_data['paginate'] = 10;
        }
        else if ($request_data['result_by'] == 'supplier') {
            // $posted_data['without_with'] = true;
            $posted_data['show_only_sums'] = true;
            $posted_data['groupBy_value'] = 'orders.receiver_id';
            $posted_data['groupBy_with_sum'] = ['admin_gross' => 'admin_gross_sum', 'supplier_gross' => 'supplier_gross_sum'];
            if ( \Auth::user()->role_id == 3 )
                $posted_data['receiver_id'] = \Auth::user()->id;
        }

        if ($request_data['result_by'] != 'order')
            $stats['revenue']['data'] = Order::getOrder($posted_data);
        else 
            $stats['revenue'] = Order::getOrder($posted_data);


        // $data['revenue'] = Order::getOrder($posted_data);

        return $this->sendResponse($stats, 'Revenue analytics are fetched successfully.');
    }
}