<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController as BaseController;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderProduct;
use App\Models\OrderService;
use Illuminate\Http\Request;
use App\Models\PaymentTransaction;

class PaymentTransactionController extends BaseController
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
        
        $week_day = PaymentTransaction::getPaymentTransaction($request_data);
        $message = count($week_day) > 0 ? 'Payment transactions retrieved successfully.' : 'Payment transactions not found against your query.';

        return $this->sendResponse($week_day, $message);
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

        $week_day = PaymentTransaction::saveUpdatePaymentTransaction($request_data);

        if ( isset($week_day->id) ){
            return $this->sendResponse($week_day, 'Payment transaction is successfully added.');
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
        $week_day = PaymentTransaction::find($id);
  
        if (is_null($week_day)) {
            $error_message['error'] = 'Payment transaction not found.';
            return $this->sendError($error_message['error'], $error_message);  
        }
   
        return $this->sendResponse($week_day, 'Payment transaction retrieved successfully.');
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
            'update_id' => 'required|exists:payment_transactions,id',
            'name'    => 'required',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);   
        }

        $week_day = PaymentTransaction::saveUpdatePaymentTransaction($request_data);

        if ( isset($week_day->id) ){
            return $this->sendResponse($week_day, 'Payment transaction is successfully updated.');
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
        $response = PaymentTransaction::deletePaymentTransaction($id);
        if($response) {
            return $this->sendResponse([], 'Payment transaction deleted successfully.');
        }
        else {
            $error_message['error'] = 'Payment transaction already deleted / Not found in database.';
            return $this->sendError($error_message['error'], $error_message);  
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function start_payment_transaction()
    {
        $posted_data = array();
        $posted_data['orders_join'] = true;
        // $posted_data['product_orders_join'] = true;
        $posted_data['order_type'] = 'Product'; // Product or Service.
        // $posted_data['order_products'] = 'Bulk'; // Single or Bulk.
        $posted_data['order_status'] = 2; // all pending orders.
        $posted_data['to_array'] = true;
        $posted_data['with'] = true;
        // $posted_data['to_sql'] = true;
        // $posted_data['paginate'] = 5;
        $response = PaymentTransaction::getPaymentTransaction($posted_data);

        echo "Line no deeee@"."<br>";
        echo "<pre>";
        print_r($response);
        echo "</pre>";
        exit("@@@@");
    }
}