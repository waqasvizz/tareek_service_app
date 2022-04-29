<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController as BaseController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Service;
use App\Models\Product;
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
            $extension = $request->profile_image->getClientOriginalExtension();

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

            $posted_data['order_status'] = 6;
            $data['requests']['completed'] = Order::getOrder($posted_data);
            $posted_data['order_status'] = 1;
            $data['requests']['pending'] = Order::getOrder($posted_data);

            $posted_data = array();
            $posted_data['count'] = true;

            if ($response->role->id == 3) {
                $posted_data['receiver_id'] = $request_data['user_id'];
            }
            
            $data['requests']['total'] = Order::getOrder($posted_data);
        }
        
        return $this->sendResponse($data, 'Dashboard items successfully fetched.');
    }
}