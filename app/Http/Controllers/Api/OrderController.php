<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController as BaseController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\OrderService;
use App\Models\PointCategorie;
use App\Models\UserPoint;
use App\Models\User;
use App\Models\Product;
use App\Models\PaymentTransaction;
use App\Models\UserDeliveryOption;
use App\Models\FCM_Token;
use App\Models\Notification;
use App\Models\UserStripeInformation;


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
        $data = array();
        $data['paginate'] = 10;

        if (isset($request_data['order_type']))
            $data['order_type'] = $request_data['order_type'];
        if (isset($request_data['user_id']))
            $data['sender_id'] = $request_data['user_id'];
        if (isset($request_data['supplier_id']))
            $data['receiver_id'] = $request_data['supplier_id'];
        if (isset($request_data['order_have']))
            $data['products_join'] = $request_data['order_have'];
        if (isset($request_data['order_status']))
            $data['order_status'] = $request_data['order_status'];
        if (isset($request_data['per_page']))
            $data['paginate'] = $request_data['per_page'];
        
        // $request_data['print_query'] = true;

        $response = Order::getOrder($data);
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


        //     // $res = $stripe->tokens->create([
        //     //     'card' => [
        //     //     'number' => '4242424242424242',
        //     //     'exp_month' => 4,
        //     //     'exp_year' => 2023,
        //     //     'cvc' => '314',
        //     //     ],
        //     // ]);
        //     // $card_tok = $res->id;


        //     // // $cusRec = $stripe->customers->create([
        //     // //     'email' => \Auth::user()->email,
        //     // //     'source'  => $card_tok,
        //     // //     'description' => 'new customer added',
        //     // // ]);
        //     // // $stripe_cus_id = $cusRec->id;

        //     // $res = $stripe->charges->create([
        //     //     'amount' => 1000,
        //     //     'currency' => 'usd',
        //     //     'source' => $card_tok,
        //     //     'description' => 'My First Test Charge (created for API docs)',
        //     // ]);


        // // $stripe->paymentIntents->create([
        // //     'amount' => 10000,
        // //     'currency' => 'usd',
        // //     'customer' => $stripe_cus_id,
        // //     'transfer_group' => 'ORDER_95',
        // // ]);


        // // $res = $stripe->transfers->create([
        // //     'amount' => 10000,
        // //     'currency' => 'usd',
        // //     'destination' => 'acct_1KqXpr2EuFwucR3a',
        // //     'transfer_group' => 'ORDER_95',
        // // ]);






          
        // //   $res = $stripe->transfers->create([
        // //     'amount' => 1000,
        // //     'currency' => 'usd',
        // //     'destination' => 'acct_1KqXpr2EuFwucR3a',
        // //   ]);
        // //   $res = $stripe->balance->retrieve();


        // // $res = $stripe->accounts->create([
        // //     'type' => 'custom',
        // //     'country' => 'US',
        // //     'email' => 'testburhan.akhtar1221@gmail.com',
        // //     'capabilities' => [
        // //     'card_payments' => ['requested' => true],
        // //     'transfers' => ['requested' => true],
        // //     ],
        // // ]);
        // //   $res = $stripe->accounts->update(
        // //         'acct_1KqXpr2EuFwucR3a',
        // //         ['tos_acceptance' => ['date' => 1609798905, 'ip' => '8.8.8.8']]
        // //   );

        // //   $res = $stripe->accounts->all(['limit' => 3]);


        // } catch (\Throwable $th) { 
        //     echo $th->getMessage();
        // }

        // echo '<pre>';
        // print_r($card_tok);
        // print_r($res);
        // exit;
        $request_data = $request->all(); 
   
        $validator = \Validator::make($request_data, [
            'order_type'                => 'required',
            'receiver_id'               => 'required|exists:users,id',
            'user_multiple_address_id'  => 'required|exists:user_multiple_addresses,id',
            'user_delivery_option_id'   => 'exists:user_delivery_options,id',
            'user_delivery_option_id'   => $request->order_type == 2 ? 'exists:user_delivery_options,id': 'nullable',
            'user_card_id'              => 'required|exists:user_cards,id',
            // 'grand_total'    => 'required',
            'service_id'                => $request->order_type == 1 ? 'required|exists:services,id': 'nullable',
            'service_price'             => $request->order_type == 1 ? 'required': 'nullable',
            'schedule_date'             => $request->order_type == 1 ? 'required': 'nullable',
            'schedule_time'             => $request->order_type == 1 ? 'required': 'nullable',
            // 'product_id'                => $request->order_type == 2 ? 'required|exists:products,id': 'nullable',
            'product_id'                => $request->order_type == 2 ? 'required|array' : 'nullable',
            'product_id.*'              => $request->order_type == 2 ? 'exists:products,id' : 'nullable',
            'product_quantity'          => $request->order_type == 2 ? 'required' : 'nullable',
            'product_price'             => $request->order_type == 2 ? 'required' : 'nullable',
            'product_type'              => $request->order_type == 2 ? 'required' : 'nullable',
            // 'redeem_point'    => 'required',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);   
        }
        
        $request_data['sender_id'] = \Auth::user()->id;
        $request_data['order_status'] = 1;
        $response = Order::saveUpdateOrder($request_data);
        $user_detail = User::getUser([
            'detail' => true,
            'id' => $request_data['sender_id']
        ]);

        if(isset($request_data['redeem_point']) && ($request_data['redeem_point'] > $user_detail->remaining_point)){
            $error_message['error'] = 'You have entered invalid redeem points.';
            return $this->sendError($error_message['error'], $error_message);
        }

        if ( isset($response->id) ){
            $grand_total = 0;

            if($request_data['order_type'] == 2 && isset($request_data['product_id'])){

                foreach ($request_data['product_id'] as $key => $item) {
                    OrderProduct::saveUpdateOrderProduct([
                        'order_id' => $response->id,
                        'product_id' => $request_data['product_id'][$key],
                        'quantity' => $request_data['product_quantity'][$key],
                        'price' => $request_data['product_price'][$key],
                    ]);
                    $grand_total = $grand_total + $request_data['product_price'][$key] * $request_data['product_quantity'][$key];
                    
                    if ($request_data['product_type'][$key] == 'bulk') {
                        $product = Product::find($request_data['product_id'][$key]);
                        $pre_qty = isset($product->consume_qty) && $product->consume_qty ? $product->consume_qty : 0;
                        $new_qty = $pre_qty + $request_data['product_quantity'][$key];

                        $product->consume_qty = $new_qty;
                        $product->save();
                    }
                }
            }

            if($request_data['order_type'] == 1 && isset($request_data['service_id'])){
                OrderService::saveUpdateOrderService([
                    'order_id' => $response->id,
                    'service_id' => $request_data['service_id'],
                    'schedule_date' => $request_data['schedule_date'],
                    'schedule_time' => $request_data['schedule_time'],
                    'service_price' => $request_data['service_price'],
                ]);
                $grand_total = $request_data['service_price'];
            }

            $update_data = array();
            $update_data['update_id'] = $response->id;
            $update_data['total'] = $grand_total;
            if(isset($request_data['redeem_point'])){

                $PointCategorieDetail = PointCategorie::getPointCategorie([
                    'id' => 1,
                    'detail' => true,
                ]);
                if(isset($PointCategorieDetail)){
                    $discount = $request_data['redeem_point']*$PointCategorieDetail->per_point_value;
                    $grand_total = $grand_total - $discount;
                    $update_data['discount'] = $discount;
                    $update_data['redeem_point'] = $request_data['redeem_point'];
                }

                $user = User::find($request_data['sender_id']);
                $user->increment('redeem_point',$request_data['redeem_point']);
                $user->decrement('remaining_point',$request_data['redeem_point']);
            }

            if( isset($request_data['user_delivery_option_id']) && $request_data['user_delivery_option_id'] ){
                $delivery_data = false;
                if( isset($request_data['user_delivery_option_id']) && $request_data['user_delivery_option_id'] ){
                    $delivery_data = UserDeliveryOption::getUserDeliveryOption([
                        'user_id' => $request_data['receiver_id'],
                        'detail' => true,
                    ]);
                }
                if ($delivery_data) {
                    $grand_total = $grand_total + $delivery_data->amount;
                }
            }

            $update_data['grand_total'] = $grand_total;
            $model_response = Order::saveUpdateOrder($update_data);

            $total_orders = Order::getOrder(['sender_id' => \Auth::user()->id, 'count' => true]);

            UserPoint::assignUserPoint([
                'point_categorie_id' => 1,
                'totalorder' => $total_orders,
            ]);

            $order_id = $model_response->id;
            $notification_text = "A new order has been placed.";

            $notification_params = array();
            $notification_params['sender'] = $model_response->sender_id;
            $notification_params['receiver'] = $model_response->receiver_id;
            $notification_params['slugs'] = "new-order";
            $notification_params['notification_text'] = $notification_text;
            $notification_params['metadata'] = "order_id=$order_id";
            
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
                    'details' => $model_response
                ]);
            }
            
            if (config('app.order_email')) {
                $data = [
                    'subject' => 'New Order - '.config('app.name'),
                    'name' => $model_response->receiverDetails->name,
                    'email' => $model_response->receiverDetails->email,
                ];

                \Mail::send('emails.order_email', ['email_data' => $data], function($message) use ($data) {
                    $message->to($data['email'])
                            ->subject($data['subject']);
                });
            }

            return $this->sendResponse($model_response, 'Order is successfully added.');
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
    public function update(Request $request, $id = 0)
    {
        $request_data = $request->all(); 
        $request_data['update_id'] = $id;
   
        $validator = \Validator::make($request_data, [
            'update_id' => 'required|exists:orders,id',
            'order_status'    => 'required',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);   
        }

        $order_detail = Order::getOrder(['id' => $id, 'detail' => true]);

        echo '<pre>';
        print_r($order_detail->ToArray());
        // print_r($order_detail);
        exit;

        if($request_data['order_status'] == 2){
            $payment_transactions = array();  
            try {
                $currency = 'USD';
                $total_amount_captured = $order_detail->grand_total;

                $admin_amount_captured = $order_detail->grand_total * .10; // 10% ammount to admin;
                $provider_amount_captured = $order_detail->grand_total * .90; // 90% ammount to supplier;

                $check_admin_stripe_info = UserStripeInformation::getUserStripeInformation([
                    'user_id' => 1,
                    'detail' => true
                ]);

                if(!$check_admin_stripe_info){
                    $error_message['error'] = 'Something went wrong please contact with support.';
                    return $this->sendError($error_message['error'], $error_message);  
                }

                $check_provider_stripe_info = UserStripeInformation::getUserStripeInformation([
                    'user_id' => \Auth::user()->id,
                    'detail' => true
                ]);

                if(!$check_provider_stripe_info){
                    $error_message['error'] = 'Your stripe information is missing, Please enter your stripe information.';
                    return $this->sendError($error_message['error'], $error_message);  
                }

                // exit($check_admin_stripe_info->sk_test);

                // send commission to the admin
                // start
                // ***********************************************************
                if($check_admin_stripe_info->stripe_mode == 'Test'){
                    $admin_stripe = new \Stripe\StripeClient($check_admin_stripe_info->sk_test);
                }else{
                    $admin_stripe = new \Stripe\StripeClient($check_admin_stripe_info->sk_live);
                }

                $create_token_res = $admin_stripe->tokens->create([
                    'card' => [
                        'number' => $order_detail->user_card->card_number,
                        'exp_month' => $order_detail->user_card->exp_month,
                        'exp_year' => $order_detail->user_card->exp_year,
                        'cvc' => $order_detail->user_card->cvc_number
                    ],
                ]);
                $card_tok = $create_token_res->id;

                $admin_charge_res = $admin_stripe->charges->create([
                    'amount' => $admin_amount_captured * 100,
                    'currency' => $currency,
                    'source' => $card_tok,
                    'description' => 'My First Test Charge (created for API docs)',
                ]);

                // $payment_transactions['admin_response_object'] = $admin_charge_res;
                // $payment_transactions['admin_amount_captured'] = $admin_amount_captured;

                
                if($check_provider_stripe_info->stripe_mode == 'Test'){
                    $provider_stripe = new \Stripe\StripeClient($check_provider_stripe_info->sk_test);
                }else{
                    $provider_stripe = new \Stripe\StripeClient($check_provider_stripe_info->sk_live);
                }

                $create_token_res = $provider_stripe->tokens->create([
                    'card' => [
                        'number' => $order_detail->user_card->card_number,
                        'exp_month' => $order_detail->user_card->exp_month,
                        'exp_year' => $order_detail->user_card->exp_year,
                        'cvc' => $order_detail->user_card->cvc_number
                    ],
                ]);
                $card_tok = $create_token_res->id;

                $provider_charge_res = $provider_stripe->charges->create([
                    'amount' => $provider_amount_captured * 100,
                    'currency' => $currency,
                    'source' => $card_tok,
                    'description' => 'My First Test Charge (created for API docs)',
                ]);

                // $payment_transactions['provider_response_object'] = $admin_charge_res;
                // $payment_transactions['provider_amount_captured'] = $provider_amount_captured;
                // end
                // ***********************************************************
                // $payment_transactions['total_amount_captured'] = $total_amount_captured;
                // $payment_transactions['currency'] = $currency;





                PaymentTransaction::saveUpdatePaymentTransaction([
                    'order_id' => $request_data['update_id'],
                    'sender_user_id' => $order_detail->senderDetails->id,
                    'receiver_user_id' => $order_detail->receiverDetails->id,
                    'currency' => 'USD',
                    'total_amount_captured' => $total_amount_captured,
                    'admin_amount_captured' => $admin_amount_captured,
                    'provider_amount_captured' => $provider_amount_captured,
                    'admin_response_object' => $admin_charge_res,
                    'provider_response_object' => $provider_charge_res,
                ]);


                // end
                // ***********************************************************


                /*

                // send remaining amount to the provider
                // start
                // ***********************************************************
                
                */

            } catch (\Throwable $th) {
                $error_message['error'] = $th->getMessage();
                return $this->sendError($error_message['error'], $error_message);
            }
        }

        $model_response = Order::saveUpdateOrder($request_data);

        $order_id = $model_response->id;
        $notification_text = "Your order status has been updated.";

        $notification_params = array();
        $notification_params['sender'] = $model_response->sender_id;
        $notification_params['receiver'] = $model_response->receiver_id;
        $notification_params['slugs'] = "order-update";
        $notification_params['notification_text'] = $notification_text;
        $notification_params['metadata'] = "order_id=$order_id";
        
        $response = Notification::saveUpdateNotification([
            'sender' => $notification_params['sender'],
            'receiver' => $notification_params['receiver'],
            'slugs' => $notification_params['slugs'],
            'notification_text' => $notification_params['notification_text'],
            'metadata' => $notification_params['metadata']
        ]);

        $firebase_devices = FCM_Token::getFCM_Tokens(['user_id' => $notification_params['sender']])->toArray();
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
                'details' => $model_response
            ]);
        }

        
        if (config('app.order_email')) {
            $data = [
                'subject' => 'Order Status Updated - '.config('app.name'),
                'name' => $model_response->senderDetails->name,
                'email' => $model_response->senderDetails->email,
            ];

            \Mail::send('emails.order_status', ['email_data' => $data], function($message) use ($data) {
                $message->to($data['email'])
                        ->subject($data['subject']);
            });
        }

        if ( isset($model_response->id) ){
            return $this->sendResponse($model_response, 'Order is successfully updated.');
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