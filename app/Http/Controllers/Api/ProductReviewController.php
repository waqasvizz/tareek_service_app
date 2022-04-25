<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController as BaseController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductReview;
use App\Models\Product;

class ProductReviewController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $request_data = $request->all();
        // $request_data['paginate'] = 10;
        if (isset($request_data['per_page']))
            $request_data['paginate'] = $request_data['per_page'];
        
        $response = ProductReview::getProductReview($request_data);
        $message = count($response) > 0 ? 'Product review retrieved successfully.' : 'Product review not found against your query.';

        return $this->sendResponse($response, $message);
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
            'product_id' => 'required|exists:products,id',
            'order_id' => 'required|exists:orders,id',
            'sender_id' => 'required|exists:users,id',
            'receiver_id' => 'required|exists:users,id',
            'stars'    => 'required',
            'description'    => 'required',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);   
        }
        
        $check_request_data['product_id'] = $request_data['product_id'];
        $check_request_data['order_id'] = $request_data['order_id'];
        $check_request_data['sender_id'] = $request_data['sender_id'];
        $check_request_data['detail'] = true;
        $checkProductReview = ProductReview::getProductReview($check_request_data);
        if($checkProductReview){
            return $this->sendResponse($checkProductReview, 'You have already submitted Product review.');
        }

        $request_data['user_id'] = \Auth::user()->id;
        $response = ProductReview::saveUpdateProductReview($request_data);
            

        if ( isset($response->id) ){
            $rating = ProductReview::where('product_id', $request_data['product_id'])->sum('stars');
            $total_reviews = ProductReview::where('product_id', $request_data['product_id'])->count();

            $avg_rating = round( ( ($rating/($total_reviews*5)) * 5 ) , 2 );

            Product::saveUpdateProduct([
                'update_id' => $request_data['product_id'],
                'avg_rating' => $avg_rating
            ]);

            return $this->sendResponse($response, 'Product review is successfully added.');
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
        $response = ProductReview::find($id);
  
        if (is_null($response)) {
            $error_message['error'] = 'Product review not found.';
            return $this->sendError($error_message['error'], $error_message);  
        }
   
        return $this->sendResponse($response, 'Product review retrieved successfully.');
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
            'update_id' => 'exists:product_reviews,id',
            'product_id' => 'required|exists:products,id',
            'order_id' => 'required|exists:orders,id',
            'sender_id' => 'required|exists:users,id',
            'receiver_id' => 'required|exists:users,id',
            'stars'    => 'required',
            'description'    => 'required',
        ],[
            'update_id.exists' => 'Updated record not exists',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);   
        }

        $request_data['update_id'] = $id;
        $response = ProductReview::saveUpdateProductReview($request_data);

        if ( isset($response->id) ){
            $rating = ProductReview::where('product_id', $request_data['product_id'])->sum('stars');
            $total_reviews = ProductReview::where('product_id', $request_data['product_id'])->count();

            $avg_rating = round( ( ($rating/($total_reviews*5)) * 5 ) , 2 );

            Product::saveUpdateProduct([
                'update_id' => $request_data['product_id'],
                'avg_rating' => $avg_rating
            ]);

            return $this->sendResponse($response, 'Product review is successfully updated.');
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
        $response = ProductReview::deleteProductReview($id);
        if($response) {
            return $this->sendResponse([], 'Product review deleted successfully.');
        }
        else {
            $error_message['error'] = 'Product review already deleted / Not found in database.';
            return $this->sendError($error_message['error'], $error_message);  
        }
    }
}