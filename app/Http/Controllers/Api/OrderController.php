<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController as BaseController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use Auth;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\OrderService;
use Stripe;

class OrderController extends BaseController
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
        
        $response = Order::getOrder($request_data);
        $message = count($response) > 0 ? 'Order retrieved successfully.' : 'Order not found against your query.';

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
        // $STRIPE_SECRET = 'sk_test_51KqBGECRyRnAcPDLU1rfQ3M49v1xkf3dYYF0ekLprUYMWEEdno7FPLPToWwGFjspnmui2tK8wPMnRS9ybHXVdkjR00b7Dh6QsC';
        // $stripe = new \Stripe\StripeClient($STRIPE_SECRET);
        // $res = '';
        // $card_tok = '';
        // try {
        // //   $res = $stripe->tokens->create([
        // //     'card' => [
        // //       'number' => '4242424242424242',
        // //       'exp_month' => 4,
        // //       'exp_year' => 2023,
        // //       'cvc' => '314',
        // //     ],
        // //   ]);
        // //   $card_tok = $res->id;

        // //   $res = $stripe->charges->create([
        // //     'amount' => 1000,
        // //     'currency' => 'usd',
        // //     'source' => $card_tok,
        // //     'description' => 'My First Test Charge (created for API docs)',
        // //   ]);


          
        //   $res = $stripe->transfers->create([
        //     'amount' => 1000,
        //     'currency' => 'usd',
        //     // 'destination' => 'acct_1KqDTlGLDz2RvoIA',
        //     // 'destination' => 'acct_1KqBGECRyRnAcPDL', //me
        //     'destination' => 'acct_1K2894K5viigToqx', //lms
        //   ]);


        // } catch (\Throwable $th) { 
        //     echo $th->getMessage();
        // }

        // echo '<pre>';
        // print_r($card_tok);
        // print_r($res);
        // exit;
        $request_data = $request->all(); 
   
        $validator = Validator::make($request_data, [
            'order_type'    => 'required',
            'user_multiple_address_id' => 'required|exists:user_multiple_addresses,id',
            'user_delivery_option_id' => 'required|exists:user_delivery_options,id',
            'user_card_id' => 'required|exists:user_cards,id',
            'grand_total'    => 'required',
            'service_id' => $request->order_type == 1 ? 'required|exists:services,id': 'nullable',
            'service_price' => $request->order_type == 1 ? 'required': 'nullable',
            'schedule_date' => $request->order_type == 1 ? 'required': 'nullable',
            'schedule_time' => $request->order_type == 1 ? 'required': 'nullable',
            'product_id' => $request->order_type == 2 ? 'required|exists:products,id': 'nullable',
            'product_quantity' => $request->order_type == 2 ? 'required': 'nullable',
            'product_price' => $request->order_type == 2 ? 'required': 'nullable',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);   
        }

        $request_data['user_id'] = Auth::user()->id;
        $request_data['order_status'] = 1;
        $response = Order::saveUpdateOrder($request_data);

        if ( isset($response->id) ){

            if(isset($request_data['product_id'])){
                foreach ($request_data['product_id'] as $key => $item) {
                    OrderProduct::saveUpdateOrderProduct([
                        'order_id' => $response->id,
                        'product_id' => $request_data['product_id'][$key],
                        'quantity' => $request_data['product_quantity'][$key],
                        'price' => $request_data['product_price'][$key],
                    ]);
                }
            }

            if(isset($request_data['service_id'])){
                OrderService::saveUpdateOrderService([
                    'order_id' => $response->id,
                    'service_id' => $request_data['service_id'],
                    'schedule_date' => $request_data['schedule_date'],
                    'schedule_time' => $request_data['schedule_time'],
                    'service_price' => $request_data['service_price'],
                ]);
            }

            return $this->sendResponse($response, 'Order is successfully added.');
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
        $response = Order::find($id);
  
        if (is_null($response)) {
            $error_message['error'] = 'Order not found.';
            return $this->sendError($error_message['error'], $error_message);  
        }
   
        return $this->sendResponse($response, 'Order retrieved successfully.');
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
   
        $validator = Validator::make($request_data, [
            'order_status'    => 'required',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);   
        }

        $request_data['update_id'] = $id;
        $response = Order::saveUpdateOrder($request_data);

        if ( isset($response->id) ){
            return $this->sendResponse($response, 'Order is successfully updated.');
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
        $response = Order::deleteOrder($id);
        if($response) {
            return $this->sendResponse([], 'Order deleted successfully.');
        }
        else {
            $error_message['error'] = 'Order already deleted / Not found in database.';
            return $this->sendError($error_message['error'], $error_message);  
        }
    }
}