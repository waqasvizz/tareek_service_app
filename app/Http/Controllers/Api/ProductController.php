<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController as BaseController;
use App\Models\Product;
use App\Models\User;
use App\Models\Notification;
use App\Models\FCM_Token;
use App\Models\Order;

// use App\Http\Resources\Product as ProductResource;

class ProductController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $posted_data = array();
        $params = $request->all();
        $posted_data['paginate'] = 10;

        if (isset($params['product_id']))
            $posted_data['id'] = $params['product_id'];
        if (isset($params['orders_exists'])) {
            $posted_data['product_orders_join'] = $params['orders_exists'];
            $posted_data['groupBy_value'] = 'order_products.product_id';
        }
        if (isset($params['category_id']))
            $posted_data['category_id'] = $params['category_id'];
        if (isset($params['user_id']))
            $posted_data['user_id'] = $params['user_id'];
        if (isset($params['product_name']))
            $posted_data['product_name'] = $params['product_name'];
        if (isset($params['product_type'])) {
            $posted_data['product_type'] = $params['product_type'];
            $posted_data['orders_join'] = true;
        }
        if (isset($params['orders_count']))
            $posted_data['orders_count'] = $params['orders_count'];
        if (isset($params['orders_users_list']))
            $posted_data['orders_users_list'] = $params['orders_users_list'];
        if (isset($params['per_page']))
            $posted_data['paginate'] = $params['per_page'];
            
            // $posted_data['print_query'] = true;
        $posted_data['with_data'] = true;
        $products = Product::getProducts($posted_data);
        $message = count($products) > 0 ? 'Products retrieved successfully.' : 'Products not found against your query.';

        return $this->sendResponse($products, $message);
        
        // $posted_data['count'] = true;
        // $count = Product::getProducts($posted_data);
    
        // return $this->sendResponse($products, 'Products retrieved successfully.', $count);
        // return $this->sendResponse(ProductResource::collection($products), 'Products retrieved successfully.', $count);
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
            'product_title'       => 'required',
            'product_price'       => 'required',
            'product_category'    => 'required|exists:categories,id',
            'product_location'    => 'required',
            'product_lat'         => 'required',
            'product_long'        => 'required',
            'product_type'        => 'required|in:single,bulk',
            'product_description' => 'required',
            'product_contact'     => 'required',
            'bulk_qty'            => 'required_if:product_type,==,bulk',
            'min_qty'             => 'required_if:product_type,==,bulk',
            'max_qty'             => 'required_if:product_type,==,bulk',
            'min_discount'        => 'required_if:product_type,==,bulk',
            'max_discount'        => 'required_if:product_type,==,bulk',
            'time_limit'          => 'required_if:product_type,==,bulk',
        ],[
            'product_type.in' => 'You have selected a invalid product type.'
        ]);
   
        if($validator->fails()){
            return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);    
        }
        
        $img_data = array();
        if (isset($request->product_image)) {
            
            $allowedfileExtension = ['jpg','jpeg','png'];
            $extension = $request->product_image->getClientOriginalExtension();

            $check = in_array($extension, $allowedfileExtension);
            if($check) {
                $response = upload_files_to_storage($request, $request->product_image, 'product_image');

                if( isset($response['action']) && $response['action'] == true ) {
                    
                    $img_data['file_name'] = isset($response['file_name']) ? $response['file_name'] : "";
                    $img_data['file_path'] = isset($response['file_path']) ? $response['file_path'] : "";
                }
            }
            else {
                return response()->json(['invalid_file_format'], 422);
            }
        }
        else {
            $error_message['error'] = 'Product Image is not found.';
            return $this->sendError($error_message['error'], $error_message);  
        }

        $product = Product::saveUpdateProduct([
            'user_id'             => \Auth::user()->id,
            'product_title'       => $request_data['product_title'],
            'product_price'       => $request_data['product_price'],
            'product_category'    => $request_data['product_category'],
            'product_location'    => $request_data['product_location'],
            'product_lat'         => $request_data['product_lat'],
            'product_long'        => $request_data['product_long'],
            'product_description' => $request_data['product_description'],
            'product_contact'     => $request_data['product_contact'],
            'product_img'         => $img_data['file_path'],
            'product_type'        => isset($request_data['product_type']) ? $request_data['product_type'] : '',
            'bulk_qty'            => isset($request_data['bulk_qty']) ? $request_data['bulk_qty'] : 0,
            'min_qty'             => isset($request_data['min_qty']) ? $request_data['min_qty'] : 0,
            'min_discount'        => isset($request_data['min_discount']) ? $request_data['min_discount'] : 0,
            'max_qty'             => isset($request_data['max_qty']) ? $request_data['max_qty'] : 0,
            'max_discount'        => isset($request_data['max_discount']) ? $request_data['max_discount'] : 0,
            'time_limit'          => isset($request_data['time_limit']) ? $request_data['time_limit'] : NULL,
        ]);

        $notification_text = "A new product has been added into the app.";
        $user_id = \Auth::user()->id;

        $notification_params = array();
        $notification_params['sender'] = $user_id;
        $notification_params['receiver'] = 1;
        $notification_params['slugs'] = "new-product";
        $notification_params['notification_text'] = $notification_text;
        $notification_params['metadata'] = "product_id=$product->id";
        
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
                'details' => $product
            ]);
        }

        if ( isset($product->id) ){
            return $this->sendResponse($product, 'Product is successfully added.');
        }
        else{
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
        $product = Product::find($id);
  
        if (is_null($product)) {
            $error_message['error'] = 'Product not found.';
            return $this->sendError($error_message['error'], $error_message);  
        }
   
        return $this->sendResponse($product, 'Product retrieved successfully.');
        // return $this->sendResponse(new ProductResource($product), 'Product retrieved successfully.');
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function update(Request $request, Product $product)
    public function update(Request $request, $id)
    {
        $request_data = $request->all();
        $request_data['update_id'] = $id;
   
        $validator = \Validator::make($request_data, [
            'update_id'           => 'required|exists:products,id',
            'product_category'    => 'exists:categories,id',
            'product_type'        => 'in:single,bulk',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);    
        }
        
        $img_data = array();
        if (isset($request->product_image)) {
            
            $allowedfileExtension = ['jpg','jpeg','png'];
            $extension = $request->product_image->getClientOriginalExtension();

            $check = in_array($extension, $allowedfileExtension);
            if($check) {
                
                $res['action'] = true;
                if (isset($product_record->product_img) && $product_record->product_img != '')
                    $res = delete_files_from_storage($product_record->product_img);

                if ($res['action']) {
                    $response = upload_files_to_storage($request, $request->product_image, 'product_image');
                    if( isset($response['action']) && $response['action'] == true ) {
                        $img_data['file_name'] = isset($response['file_name']) ? $response['file_name'] : "";
                        $img_data['file_path'] = isset($response['file_path']) ? $response['file_path'] : "";
                        $request_data['product_img'] = $img_data['file_path'];
                    }
                }
                else {
                    $error_message['error'] = 'Somthing went wrong during image replacement.';
                    return $this->sendError($error_message['error'], $error_message);  
                }
            }
            else {
                return response()->json(['invalid_file_format'], 422);
            }
        }

        $product = Product::saveUpdateProduct($request_data);

        if ( isset($product->id) ){
            return $this->sendResponse($product, 'Product is successfully updated.');
        }
        else{
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
    // public function destroy(Product $product)
    public function destroy($id)
    {
        if(Product::find($id)){
            $filepath = Product::find($id)->product_img;
            delete_files_from_storage($filepath);
            Product::deleteProduct($id);
            return $this->sendResponse([], 'Product deleted successfully.');
        }else{
            $error_message['error'] = 'Product already deleted.';
            return $this->sendError($error_message['error'], $error_message);  
        } 
    }

    public function get_details(Request $request)
    {
        $request_data = $request->all();
        $validator = \Validator::make($request_data, [
            'product_id'    => 'required|exists:products,id',
            'statuses'      => 'in:yes,no',
            'users_list'    => 'in:yes,no',
        ],[
            'product_id.exists' => 'You have selected a invalid product.'
        ]);
   
        if($validator->fails()){
            return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);    
        }

        $posted_data = array();
        $posted_data['id'] = $request_data['product_id'];
        $posted_data['product_orders_join'] = true;
        $posted_data['orders_join'] = true;
        $posted_data['to_array'] = true;
        $posted_data['with_data'] = false;
        $products = Product::getProducts($posted_data);

        $response = [];
        $message = "";
                
        if (isset($request_data['statuses']) && $request_data['statuses'] == 'yes') {
            $bulk_ids = array_unique(array_column($products, 'orders_id'));
    
            if ( count($bulk_ids) > 0 ) {
                $response = Order::getOrder(['orders_in' => $bulk_ids]);
            }
            $message = count($bulk_ids) > 0 ? 'Orders statuses list retrieved successfully.' : 'Orders statuses list not found against your query.';
        }
        else if (isset($request_data['users_list']) && $request_data['users_list'] == 'yes') {
            $bulk_ids = array_unique(array_column($products, 'client_id'));
    
            if ( count($bulk_ids) > 0 ) {
                $response = User::getUser(['users_in' => $bulk_ids]);
            }
            $message = count($bulk_ids) > 0 ? 'Users list retrieved successfully.' : 'Users list not found against your query.';
        }

        return $this->sendResponse($response, $message);
    }

    public function update_bulk_orders(Request $request)
    {
        $request_data = $request->all();
        $validator = \Validator::make($request_data, [
            'product_id'    => 'required|exists:products,id',
            'status'        => 'required',
        ],[
            'product_id.exists' => 'You have selected a invalid product.'
        ]);
   
        if($validator->fails()){
            return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);    
        }

        if ( !isset($request_data['rejection_message']) || $request_data['rejection_message'] == '' ) {
            unset($request_data['rejection_message']);
        }

        $posted_data = array();
        $posted_data['id'] = $request_data['product_id'];
        $posted_data['product_orders_join'] = true;
        $posted_data['orders_join'] = true;
        $posted_data['to_array'] = true;
        $posted_data['with_data'] = false;
        $products = Product::getProducts($posted_data);

        $order_ids = array_unique(array_column($products, 'orders_id'));
        $bulk_records = getSpecificColumnsFromArray($products, ['user_id', 'client_id', 'orders_id']);

                                        // echo "Line no deeee@"."<br>";
                                        // echo "<pre>";
                                        // print_r($products);
                                        // print_r($bulk_records);
                                        // echo "</pre>";
                                        // exit("@@@@");

        $orders_list = [];
        if ( count($order_ids) > 0 ) {
            $orders_list = Order::saveUpdateOrder([
                'update_bulk_statuses'  => $request_data['status'],
                'order_ids'             => $order_ids,
                'rejection_message'     => isset($request_data['rejection_message']) ? $request_data['rejection_message'] : NULL,
            ]);
        }

                                        // $notif_data = array();

                                        // foreach ($bulk_records as $key => $value) {
                                        //     $data = User::getUser(['id' => $value['client_id'], 'detail' => true])->ToArray();

                                        //     echo "Line no data@"."<br>";
                                        //     echo "<pre>";
                                        //     print_r($data);
                                        //     echo "</pre>";
                                        //     exit("@@@@");
                                        // }

                    


                                        // $data = array();
                                        // $data['detail'] = true;
                                        // $data['id'] = $post->id;
                                        // $post_data = Post::getPost($data);
                                        // $model_response = $post_data->toArray();
                                        
                                        // $data = array();
                                        // $data['role'] = 2;
                                        // $user_data = User::getUser($data)->ToArray();

        $notification_text = "You order status has been updated.";

        foreach ($bulk_records as $key => $value) {
            $user_data = User::getUser(['id' => $value['client_id'], 'detail' => true])->ToArray();

            $supplier_id = $value['user_id'];
            $receiver_id = $value['client_id'];
            $order_id = $value['orders_id'];

            $notification_params = array();
            $notification_params['sender'] = $supplier_id;
            $notification_params['receiver'] = $receiver_id;
            $notification_params['slugs'] = "order-update";
            $notification_params['notification_text'] = $notification_text;
            $notification_params['seen_by'] = "";
            $notification_params['metadata'] = "supplier_id=$supplier_id"."&order_id=$order_id";
           
            $response = Notification::saveUpdateNotification([
                'sender' => $notification_params['sender'],
                'receiver' => $notification_params['receiver'],
                'slugs' => $notification_params['slugs'],
                'notification_text' => $notification_params['notification_text'],
                'seen_by' => $notification_params['seen_by'],
                'metadata' => $notification_params['metadata']
            ]);
            
            // $tokens[] = array_column($user_data['fcm_tokens'], 'device_token');
            $token = array_column($user_data['fcm_tokens'], 'device_token');

            $notification = FCM_Token::sendFCM_Notification([
                'title' => $notification_params['slugs'],
                'body' => $notification_params['notification_text'],
                'metadata' => $notification_params['metadata'],
                'registration_ids' => $token,
                'details' => []
            ]);
        }
        
                                        // $notification = false;
                                        // if ($response) {
                                        //     $notification = FCM_Token::sendFCM_Notification([
                                        //         'title' => $notification_params['slugs'],
                                        //         'body' => $notification_params['notification_text'],
                                        //         'metadata' => $notification_params['metadata'],
                                        //         'registration_ids' => $registration_ids,
                                        //         'details' => $model_response
                                        //     ]);
                                        // }
        
        if (config('app.product_email')) {
            foreach ($bulk_records as $key => $value) {
                $user_data = User::getUser(['id' => $value['client_id'], 'detail' => true])->ToArray();
                
                $data = [
                    'subject' => 'Order Status Updated - '.config('app.name'),
                    'name' => $user_data['name'],
                    'email' => $user_data['email'],
                ];
    
                \Mail::send('emails.order_status', ['email_data' => $data], function($message) use ($data) {
                    $message->to($data['email'])
                            ->subject($data['subject']);
                });
            }
        }

        $message = $orders_list ? 'Orders statuses updated successfully.' : 'Something went wrong during query data.';
        return $this->sendResponse('', $message);
    }
}