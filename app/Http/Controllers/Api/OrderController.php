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
use App\Models\Service;
use App\Models\EmailMessage;
use App\Models\EmailLogs;
use App\Models\Category;
use App\Models\PaymentTransaction;
use App\Models\UserDeliveryOption;
use App\Models\FCM_Token;
use App\Models\ClearenceService;
use App\Models\Notification;
use App\Models\UserCard;
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

        if (isset($request_data['order_type'])) {
            $data['order_type'] = $request_data['order_type'];
            if ($request_data['order_type'] == 'Service') {
                $data['order_services_join'] = true;
                $data['services_join'] = true;
            }
            else {
                $data['order_products_join'] = true;
                $data['products_join'] = true;
            }
        }
        if (isset($request_data['user_id']))
            $data['sender_id'] = $request_data['user_id'];
        if (isset($request_data['supplier_id']))
            $data['receiver_id'] = $request_data['supplier_id'];
        if (isset($request_data['order_have']))
            $data['order_have'] = $request_data['order_have'];
        if (isset($request_data['order_status']))
            $data['order_status'] = $request_data['order_status'];
        if (isset($request_data['product_id']))
            $data['product_id'] = $request_data['product_id'];
        if (isset($request_data['search_filter'])) {
            if (!(isset($request_data['order_type']) && $request_data['order_type'] != '' )) {
                $error_message['error'] = 'Please select the order type for search filter.';
                return $this->sendError($error_message['error'], $error_message);
            }
            $data['search_filter'] = $request_data['search_filter'];
        }
        if (isset($request_data['supplier_payment'])) {
            if (! (($request_data['supplier_payment'] == 'yes') || ($request_data['supplier_payment'] == 'no')) ) {
                $error_message['error'] = 'Please select a valid key yes / no for search filter.';
                return $this->sendError($error_message['error'], $error_message);
            }
            $data['supplier_payment'] = $request_data['supplier_payment'];
        }
        if (isset($request_data['refund_requests'])) {
            if (! (($request_data['refund_requests'] == 'yes') || ($request_data['refund_requests'] == 'no')) ) {
                $error_message['error'] = 'Please select a valid key yes / no for search filter.';
                return $this->sendError($error_message['error'], $error_message);
            }
            $data['refund_req'] = $data['refund_requests'];
        }
        if (isset($request_data['refund_status'])) {
            if (! (($request_data['refund_status'] == 2) || ($request_data['refund_status'] == 3) || ($request_data['refund_status'] == 4)) ) {
                $error_message['error'] = 'Please select a valid refund status for search filter.';
                return $this->sendError($error_message['error'], $error_message);
            }
            $data['refund_status'] = $request_data['refund_status'];
        }
        if (isset($request_data['service_id']))
            $data['service_id'] = $request_data['service_id'];
        if (isset($request_data['per_page']))
            $data['paginate'] = $request_data['per_page'];

        $data['sender_users_join'] = true;
        $data['receiver_users_join'] = true;
        // $data['print_query'] = true;

        // echo "Line no deee@"."<br>";
        // echo "<pre>";
        // print_r($data);
        // echo "</pre>";
        // exit("@@@@");
        
        // $data['print_query'] = true;

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

        if(isset($request_data['order_type']) && $request_data['order_type'] == 1 )
            $request_data['order_products'] = 'Single';
        else if (isset($request_data['order_type']) && $request_data['order_type'] == 2 ) {
            if ($request_data['product_type'][0] == 'single')
                $request_data['order_products'] = 'Single';
            else if ($request_data['product_type'][0] == 'bulk')
                $request_data['order_products'] = 'Bulk';
        }

        if( !isset($request_data['order_products']) ){
            $error_message['error'] = 'Orders type must be a bulk or single.';
            return $this->sendError($error_message['error'], $error_message);
        }

        $available_points = \Auth::user()->remaining_point;

        if( isset($request_data['redeem_point']) && $request_data['redeem_point'] > 0 ){

            if( $request_data['redeem_point'] > $available_points ){
                $error_message['error'] = 'You have entered invalid redeem points.';
                return $this->sendError($error_message['error'], $error_message);
            }

            $total_amount = 0;
            if( $request_data['order_type'] == 2 && isset($request_data['product_id']) ) {
                foreach ($request_data['product_id'] as $key => $item) {
                    $total_amount += ( $request_data['product_price'][$key] * $request_data['product_quantity'][$key] );
                }
            }
            else if( $request_data['order_type'] == 1 && isset($request_data['service_id']) ){
                $total_amount = $request_data['service_price'];
            }
            $half_total_amount = ceil($total_amount/2);
            
            $reedem_discount = $this->get_available_redeem_amount(['redeem_point' => $request_data['redeem_point']]);
            $half_reedem_discount = floor( ($reedem_discount['total_points_worth']/$reedem_discount['each_point_worth'])/2 );

            if ( $reedem_discount['total_points_worth'] > $half_total_amount ) {
                $error_message['error'] = 'Sorry you can only redeem 50% order amount with maximum '.floor($half_total_amount/$reedem_discount['each_point_worth']).' points.';
                return $this->sendError($error_message['error'], $error_message);
            }
        }
        
        $request_data['sender_id'] = \Auth::user()->id;
        $request_data['order_status'] = 1;

        $response = Order::saveUpdateOrder($request_data);
        $user_detail = User::getUser([
            'detail' => true,
            'id' => $request_data['sender_id']
        ]);

        if ( isset($response->id) ){
            $grand_total = 0;
            $admin_total = 0;
            $supplier_total = 0;
            $count = 0;
            $admin_avg = 0;
            $supplier_avg = 0;
            $reedem_disc_total = 0;
            $delivery_cost = 0;
            $points = 0;

            if($request_data['order_type'] == 2 && isset($request_data['product_id'])){

                foreach ($request_data['product_id'] as $key => $item) {
                    $save_product_order = array();

                    $product_details = Product::getProducts(['id' => $request_data['product_id'][$key], 'detail' => true, 'with_data' => true]);
                    $admin_part = isset($product_details->category->commission) ? $product_details->category->commission : 0;
                    $supplier_part = 100 - $admin_part;
                    
                    $admin_avg += isset($product_details->category->commission) ? $product_details->category->commission : 0;
                    $count += 1;

                    $price = $request_data['product_price'][$key];
                    $prod_price = $request_data['product_quantity'][$key] * $request_data['product_price'][$key];
                    $admin_earn = round( ($admin_part/100) * $prod_price, 2);
                    $supplier_earn = round( ($supplier_part/100) * $prod_price, 2 );
                    $tot_admin = 0;
                    $tot_supplier = 0;

                    $save_product_order['order_id'] = $response->id;
                    $save_product_order['product_id'] = $request_data['product_id'][$key];
                    $save_product_order['quantity'] = $request_data['product_quantity'][$key];
                    $save_product_order['price'] = $request_data['product_price'][$key];
                    $save_product_order['prod_price'] = $prod_price;
                    $save_product_order['admin_earn'] = $admin_earn;
                    $save_product_order['supplier_earn'] = $supplier_earn;
                    $save_product_order['total_admin'] = $admin_earn;
                    $save_product_order['total_supplier'] = $supplier_earn;

                    OrderProduct::saveUpdateOrderProduct($save_product_order);

                    if ($request_data['product_type'][$key] == 'bulk') {

                        $product = Product::find($request_data['product_id'][$key]);
                        $pre_qty = isset($product->consume_qty) && $product->consume_qty ? $product->consume_qty : 0;
                        $new_qty = $pre_qty + $request_data['product_quantity'][$key];

                        $product->consume_qty = $new_qty;
                        $product->save();

                    }
                    $grand_total = $grand_total + ( $request_data['product_price'][$key] * $request_data['product_quantity'][$key] );
                }

                $update_order_prod = array();
                $get_order_prod = OrderProduct::getOrderProduct(['order_id' => $response->id]);

                if(isset($request_data['redeem_point'])){
                    $reedem_disc_total = $this->get_available_redeem_amount(['redeem_point' => $request_data['redeem_point']]);
                    foreach ($get_order_prod as $key => $value) {
                        $update_order_prod['update_id'] = $value->id;
                        $update_order_prod['adm_aftr_reedem'] = round( (($grand_total - $reedem_disc_total['total_points_worth']) / $grand_total ) * $value['admin_earn'] , 2);
                        $update_order_prod['sup_aftr_reedem'] = round( (($grand_total - $reedem_disc_total['total_points_worth']) / $grand_total ) * $value['supplier_earn'] , 2);
                        $update_order_prod['total_admin'] = $update_order_prod['adm_aftr_reedem'];
                        $update_order_prod['total_supplier'] = $update_order_prod['sup_aftr_reedem'];
                        $update_order_prod['reedem_disc'] = 2; // means True

                        $admin_total += $update_order_prod['adm_aftr_reedem'];
                        $supplier_total += $update_order_prod['sup_aftr_reedem'];
                        OrderProduct::saveUpdateOrderProduct($update_order_prod);
                    }
                }
                else {
                    foreach ($get_order_prod as $key => $value) {
                        $admin_total += $value['admin_earn'];
                        $supplier_total += $value['supplier_earn'];
                    }
                }

                if(isset($request_data['redeem_point'])){
                    $user = User::find($request_data['sender_id']);
                    $user->increment('redeem_point',$request_data['redeem_point']);
                    $user->decrement('remaining_point',$request_data['redeem_point']);
                }
            }

            $update_data = array();
            $save_service_order = array();
            $update_data['update_id'] = $response->id;
            
            if($request_data['order_type'] == 1 && isset($request_data['service_id'])){

                $grand_total += $request_data['service_price'];
                $service_details = Service::getServices(['id' => $request_data['service_id'], 'detail' => true, 'with_data' => true]);
                $admin_part = isset($service_details->category->commission) ? $service_details->category->commission : 0;
                $supplier_part = 100 - $admin_part;
                $price = $request_data['service_price'];
                $admin_earn = ceil( ($admin_part/100) * $price );
                $supplier_earn = floor( ($supplier_part/100) * $price );

                $admin_avg += isset($service_details->category->commission) ? $service_details->category->commission : 0;
                $count += 1;

                $save_service_order['order_id'] = $response->id;
                $save_service_order['service_id'] = $request_data['service_id'];
                $save_service_order['schedule_date'] = $request_data['schedule_date'];
                $save_service_order['schedule_time'] = $request_data['schedule_time'];
                $save_service_order['service_price'] = $request_data['service_price'];
                $save_service_order['admin_earn'] = $admin_earn;
                $save_service_order['supplier_earn'] = $supplier_earn;

                if(isset($request_data['redeem_point'])){
                    $reedem_disc_total = $this->get_available_redeem_amount(['redeem_point' => $request_data['redeem_point']]);

                    $save_service_order['adm_aftr_reedem'] = round( (($grand_total - $reedem_disc_total['total_points_worth']) / $grand_total ) * $admin_earn , 2);
                    $save_service_order['sup_aftr_reedem'] = round( (($grand_total - $reedem_disc_total['total_points_worth']) / $grand_total ) * $supplier_earn , 2);
                    $save_service_order['reedem_disc'] = 2; // means True
                    $update_data['redeem_point'] = $request_data['redeem_point'];

                    $admin_total += $save_service_order['adm_aftr_reedem'];
                    $supplier_total += $save_service_order['sup_aftr_reedem'];
                }
                else {
                    $admin_total += $admin_earn;
                    $supplier_total += $supplier_earn;
                }
                OrderService::saveUpdateOrderService($save_service_order);

                if(isset($request_data['redeem_point'])){
                    $user = User::find($request_data['sender_id']);
                    $user->increment('redeem_point',$request_data['redeem_point']);
                    $user->decrement('remaining_point',$request_data['redeem_point']);
                }
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
                    $delivery_cost = $delivery_data->amount;
                    $grand_total = $grand_total + $delivery_cost;
                }
            }

            if( isset($request_data['user_docs']) && count($request_data['user_docs']) > 0 ) {
                foreach ($request_data['user_docs'] as $key => $docs) {
                    ClearenceService::saveUpdateClearenceService([
                        'order_id' => $response->id,
                        'user_asset_id' => $request_data['user_docs'][$key]
                    ]);
                }
            }

            $update_data['shipping_cost'] = $delivery_cost;
            $update_data['total'] = $grand_total;
            $update_data['admin_avg'] = ($count > 0) ? ceil($admin_avg / $count) : 0;
            $update_data['supplier_avg'] = 100 - $update_data['admin_avg'];
            $update_data['discount_redeem'] = isset($reedem_discount['total_points_worth']) ? $reedem_discount['total_points_worth'] : 0;
            $update_data['grand_total'] = $grand_total - ($update_data['discount_redeem'] + $delivery_cost);

            $admin_delivery_earn = $delivery_cost > 0 ? round( ($update_data['admin_avg'] * $delivery_cost) / 100 , 2) : 0;
            $supplier_delivery_earn = $delivery_cost > 0 ? round( ($update_data['supplier_avg'] * $delivery_cost) / 100 , 2) : 0;

            $update_data['admin_gross'] = round($admin_total + $admin_delivery_earn,2);
            $update_data['supplier_gross'] = round($supplier_total + $supplier_delivery_earn,2);

            // $total_orders = Order::getOrder(['sender_id' => \Auth::user()->id, 'count' => true]);

            $points = UserPoint::assignUserPoint([
                'point_categorie_id' => 1,
                'totalprice' => $update_data['grand_total'],
            ]);

            $update_data['points_earn'] = isset($points) ? $points : 0;

            $model_response = Order::saveUpdateOrder($update_data);

            ///////////////////////////////////////////////////////////////////
            ///////////////////*     USER NOTIFICATION     */
            ///////////////////////////////////////////////////////////////////

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
                /*
                $data = [
                    'subject' => 'New Order - '.config('app.name'),
                    'name' => $model_response->receiverDetails->name,
                    'email' => $model_response->receiverDetails->email,
                ];
                */

                $admin['id'] = 1;
                $admin['detail'] = true;
                $admin_data = $this->UserObj->getUser($admin);

                // this email will sent to the newly registered user via mobile app
                $email_content = EmailMessage::getEmailMessage(['id' => 9, 'detail' => true]);
                    
                $email_data = decodeShortCodesTemplate([
                    'subject' => $email_content->subject,
                    'body' => $email_content->body,
                    'email_message_id' => 9,
                    'sender_id' => $model_response->receiver_id,
                    'receiver_id' => $admin_data->id,
                ]);

                EmailLogs::saveUpdateEmailLogs([
                    'email_msg_id' => 9,
                    'sender_id' => $model_response->receiver_id,
                    'receiver_id' => $admin_data->id,
                    'email' => $admin_data->email,
                    'subject' => $email_data['email_subject'],
                    'email_message' => $email_data['email_body'],
                    'send_email_after' => 1, // 1 = Daily Email
                ]);


                // for new order received by the supplier
                $email_content = EmailMessage::getEmailMessage(['id' => 3, 'detail' => true]);
                    
                $email_data = decodeShortCodesTemplate([
                    'subject' => $email_content->subject,
                    'body' => $email_content->body,
                    'email_message_id' => 3,
                    'user_id' => $model_response->receiver_id,
                ]);

                EmailLogs::saveUpdateEmailLogs([
                    'email_msg_id' => 3,
                    'sender_id' => $model_response->sender_id,
                    'receiver_id' => $model_response->receiver_id,
                    'email' => $model_response->receiverDetails->email,
                    'subject' => $email_data['email_subject'],
                    'email_message' => $email_data['email_body'],
                    'send_email_after' => 1, // 1 = Daily Email
                ]);

                /*
                \Mail::send('emails.order_email', ['email_data' => $data], function($message) use ($data) {
                    $message->to($data['email'])
                            ->subject($data['subject']);
                });
                */
            }

            return $this->sendResponse($model_response, 'Order is successfully created.');
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

        // $category_data = Category::getCategories(['']);

        // echo '<pre>';
        // print_r($order_detail->ToArray());
        // print_r($order_detail);
        // exit;

        // $model_response = Order::saveUpdateOrder($request_data);

        // if ($model_response) {
        $this->calculate_orders_min_discounts($request, ['order_id' => $id]);
        $this->calculate_orders_max_discounts($request, ['order_id' => $id]);

        // }

        $order_detail = Order::getOrder(['id' => $id, 'detail' => true]);
        $card_details = UserCard::getUserCard(['user_id' => $order_detail->sender_id, 'to_array' => true, 'card_info' => 'decrypted']);

        if($request_data['order_status'] == 2){

            if ( isset($order_detail) && $order_detail->payment_status == 'False' ) { // False means no payment done yet now against this order.
                $payment_transactions = array();
                try {
                    $currency = 'USD';
                    $total_amount_captured = $order_detail->grand_total + $order_detail->shipping_cost;
    
                    $admin_amount_captured = $order_detail->admin_gross * 100;
                    $provider_amount_captured = $order_detail->supplier_gross * 100;
    
                    $admin_amount_captured += $provider_amount_captured;
    
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
                            'number' => $card_details[0]['card_number'],
                            'exp_month' => $card_details[0]['exp_month'],
                            'exp_year' => $card_details[0]['exp_year'],
                            'cvc' => $card_details[0]['cvc_number']
                        ],
                    ]);
                    $card_tok = $create_token_res->id;
    
                    $admin_charge_res = $admin_stripe->charges->create([
                        'amount' => $admin_amount_captured,
                        'currency' => $currency,
                        'source' => $card_tok,
                        'description' => 'My First Test Charge (created for API docs)',
                    ]);
    
                    // $payment_transactions['admin_response_object'] = $admin_charge_res;
                    // $payment_transactions['admin_amount_captured'] = $admin_amount_captured;
    
                    /*
                    if($check_provider_stripe_info->stripe_mode == 'Test'){
                        $provider_stripe = new \Stripe\StripeClient($check_provider_stripe_info->sk_test);
                    }else{
                        $provider_stripe = new \Stripe\StripeClient($check_provider_stripe_info->sk_live);
                    }
    
                    $create_token_res = $provider_stripe->tokens->create([
                        'card' => [
                            'number' => $card_details[0]['card_number'],
                            'exp_month' => $card_details[0]['exp_month'],
                            'exp_year' => $card_details[0]['exp_year'],
                            'cvc' => $card_details[0]['cvc_number']
                        ],
                    ]);
                    $card_tok = $create_token_res->id;
    
                    $provider_charge_res = $provider_stripe->charges->create([
                        'amount' => $provider_amount_captured,
                        'currency' => $currency,
                        'source' => $card_tok,
                        'description' => 'My First Test Charge (created for API docs)',
                    ]);
                    */
    
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
                        'admin_amount_captured' => ($admin_amount_captured / 100),
                        'provider_amount_captured' => ($provider_amount_captured / 100),
                        'admin_response_object' => $admin_charge_res,
                        'provider_response_object' => NULL, //$provider_charge_res,
                    ]);

                    $order_obj = Order::find($id);

                    if ($order_obj) {
                        $order_obj->payment_status = 2; // means
                        $order_obj->save(); // means
                    }
    
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

        }

        $model_response = Order::saveUpdateOrder($request_data);

        // if ($model_response) {
        //     $this->calculate_orders_min_discounts($request, ['order_id' => $id]);
        //     $this->calculate_orders_max_discounts($request, ['order_id' => $id]);
        // }

        $order_id = $model_response->id;
        $notification_text = "Your order status has been updated.";

        $notification_params = array();
        $notification_params['sender'] = $model_response->receiver_id;
        $notification_params['receiver'] = $model_response->sender_id;
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
            // $data = [
            //     'subject' => 'Order Status Updated - ',/*.config('app.name'),*/
            //     'name' => $model_response->senderDetails->name,
            //     'email' => $model_response->senderDetails->email,
            // ];

            // for order status updated by the supplier for customer
            $email_content = EmailMessage::getEmailMessage(['id' => 4, 'detail' => true]);
    
            $email_data = decodeShortCodesTemplate([
                'subject' => $email_content->subject,
                'body' => $email_content->body,
                'email_message_id' => 4,
                'user_id' => $model_response->sender_id,
            ]);

            // here sender is the customer and receiver is the supplier
            EmailLogs::saveUpdateEmailLogs([
                'email_msg_id' => 4,
                'sender_id' => $model_response->receiver_id,
                'receiver_id' => $model_response->sender_id,
                'email' => $model_response->senderDetails->email,
                'subject' => $email_data['email_subject'],
                'email_message' => $email_data['email_body'],
                'send_email_after' => 1, // 1 = Daily Email
            ]);

            /*
            \Mail::send('emails.order_status', ['email_data' => $data], function($message) use ($data) {
                $message->to($data['email'])
                        ->subject($data['subject']);
            });
            */
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

    public function get_available_redeem_amount($posted_data = array())
    {
        $redeem_point = isset($posted_data['redeem_point']) ? $posted_data['redeem_point'] : 0;
        $response = [
            'total_points_worth' => 0,
            'each_point_worth' => 0,
        ];

        $PointCategorieDetail = PointCategorie::getPointCategorie([
            'id' => 1,
            'detail' => true,
        ]);

        if( isset($PointCategorieDetail) ){
            $response['total_points_worth'] = $redeem_point * $PointCategorieDetail->per_point_value;
            $response['each_point_worth'] = $PointCategorieDetail->per_point_value;
        }

        return $response;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function mark_paid(Request $request)
    {
        $request_data = $request->all();
   
        $validator = \Validator::make($request_data, [
            'order_id' => 'required|exists:orders,id',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);   
        }

        $data['supplier_payment'] = 2; // 2 means paid
        $data['update_id'] = $request_data['order_id'];
        $response = Order::saveUpdateOrder($data);

        $message = ($response->id) ? 'Supplier payment marked successfully.' : 'Something went wrong with your query.';
        return $this->sendResponse($response, $message);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function refund_process(Request $request)
    {
        $request_data = $request->all();
   
        $validator = \Validator::make($request_data, [
            'order_id' => 'required|exists:orders,id',
            'refund_status' => 'required',
            'notes' => 'required|min:10',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);   
        }

        if (isset($request_data['refund_status'])) {
            if (! (($request_data['refund_status'] == 3) || ($request_data['refund_status'] == 4)) ) {
                $error_message['error'] = 'Please select a valid refund status for search filter.';
                return $this->sendError($error_message['error'], $error_message);
            }
        }        

        $data = array();
        $data['id'] = $request_data['order_id'];
        $data['refund_req'] = 'yes';
        // $data['refund_status'] = 2; // means refund is requested.
        $data['detail'] = true;
        $response = Order::getOrder($data);

        $message = 'You have never requested for the refund.';
        
        if ( isset($response->refund_status) && $response->refund_status == 'Not-Requested') 
            $message = 'You have never requested for the refund.';
        else if ( isset($response->refund_status) && $response->refund_status == 'Requested')
            $message = 'Refund request is successfully processed.';
        else if ( isset($response->refund_status) && $response->refund_status == 'Paid') 
            $message = 'Your refund request is already paid.';
        else if ( isset($response->refund_status) && $response->refund_status == 'Rejected') 
            $message = 'Your refund request is already rejected.';

        if ($response) {

            if ($response->refund_status == 'Requested') {
                $data = array();
                $data['update_id'] = $request_data['order_id'];
                $data['refund_status'] = $request_data['refund_status']; // means refund is requested.
                $data['refund_status_reason'] = $request_data['notes'];
                $response = Order::saveUpdateOrder($data);
    
                $message = ($response->id) ? 'Refund request is successfully processed.' : 'Something went wrong with your query.';
                return $this->sendResponse($response, $message);
            }
            else {
                $error_message['error'] = $message;
                return $this->sendError($error_message['error'], $error_message);
            }
        }
        else {
            $error_message['error'] = $message;
            return $this->sendError($error_message['error'], $error_message);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function refund_requests(Request $request)
    {
        $request_data = $request->all();
   
        $validator = \Validator::make($request_data, [
            'order_id' => 'required|exists:orders,id',
            'refund_reason' => 'required|min:10',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);   
        }

        $data = array();
        $data['id'] = $request_data['order_id'];
        $data['refund_req'] = 'no';
        $data['detail'] = true;
        $response = Order::getOrder($data);

        if (!$response)
            return $this->sendResponse([], 'Your refund request is already in progress.');
        else {
            $data = array();
            $data['update_id'] = $request_data['order_id'];
            $data['refund_req'] = 'yes';
            $data['refund_status'] = '2'; // means refund is requested.
            $data['refund_req_reason'] = $request_data['refund_reason'];
            $response = Order::saveUpdateOrder($data);

            $message = ($response->id) ? 'Refund payment requested successfully.' : 'Something went wrong with your query.';
            return $this->sendResponse($response, $message);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function get_pending(Request $request)
    {
        $data = array();
        $request_data =  $request->all();

        $data['supplier_payment'] = 1; // 1 means pending
        // $data['without_with'] = true;
        $data['paginate'] = 10;
        if ( isset($request_data['page']) && $request_data['page'] ) {
            $data['page'] = $request_data['page'];
        }

        $response = Order::getOrder($data);
        $message = count($response) > 0 ? 'Pending orders retrieved successfully.' : 'No data found against your query.';

        return $this->sendResponse($response, $message);
    }
}