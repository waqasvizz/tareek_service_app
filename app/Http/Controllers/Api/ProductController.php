<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController as BaseController;
use App\Models\Product;
use App\Models\Notification;
use App\Models\FCM_Token;
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
        // $params = $request->all();

        $posted_data = $request->all();
        $posted_data['paginate'] = 10;

        if (isset($posted_data['product_id']))
            $posted_data['id'] = $posted_data['product_id'];
        if (isset($posted_data['product_name']))
            $posted_data['product_name'] = $posted_data['product_name'];
        if (isset($posted_data['per_page']))
            $posted_data['paginate'] = $posted_data['per_page'];
        
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
            'product_description' => 'required',
            'product_contact'     => 'required',
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
            'update_id'    => 'required|exists:products,id',
            'product_category'    => 'required|exists:categories,id',
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
            return $this->sendResponse($product, 'Product is successfully added.');
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
}