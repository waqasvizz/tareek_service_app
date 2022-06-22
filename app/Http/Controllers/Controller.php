<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

use App\Models\Role;
use App\Models\Service;
use App\Models\User;

use App\Models\Order;
use App\Models\Product;
use App\Models\OrderProduct;
use App\Models\OrderService;
use Illuminate\Http\Request;
use App\Models\PaymentTransaction;

use DB;
use Validator;
use Auth;
use Session;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public $RoleObj;
    public $ServiceObj;
    public $UserObj;

    public function __construct() {
        
        $this->RoleObj = new Role();
        $this->ServiceObj = new Service();
        $this->UserObj = new User();
    }

    public function firebase() {
        return view('firebase');
    }

    public function sendNotification() {
        
        $token = "fHRRYnyQyrA:APA91bFGF5j4A76XXsC4xb2canvjRPlJqlcL_yKBmgQrOu9egO3Qk9v86Lh5eSE6EQ13DC6qdE4AoxgdFsIYZvv3PtCeNdbtj6zXazZuJKGI6Doxcriw-Zdpd9QnigCD_mDCgz_BA5N7";  
        $from = "AAAA1x62L-A:APA91bHPEZuPTTVn8tWhggUur4h2_k92s4cRWIu5L9lkRgS2pHtYJKMgCIkg4UcIMui1lWcXRGStyKxjIgrlH7KXefS0CkSS8tlrR0yDWiNRUkeYsNuivIgnV2rgep6QCmQL75-QpBTd";
        $msg = array
            (
                'body'  => "Testing Testing",
                'title' => "Hi, From Raj",
                'receiver' => 'erw',
                'icon'  => "https://image.flaticon.com/icons/png/512/270/270014.png",/*Default Icon*/
                'sound' => 'Default'/*Default sound*/
            );

        $fields = array
                (
                    'to'        => $token,
                    'notification'  => $msg
                );

        $headers = array
                (
                    'Authorization: key=' . $from,
                    'Content-Type: application/json'
                );
        //#Send Reponse To FireBase Server 
        $ch = curl_init();
        curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
        curl_setopt( $ch,CURLOPT_POST, true );
        curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
        $result = curl_exec($ch );
        dd($result);
        curl_close( $ch );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function calculate_orders_min_discounts(Request $request, $data = array())
    {
        $posted_data = array();
        $posted_data['min_disc_qualify'] = true; // means bulk products
        $posted_data['within_time_limit'] = date("Y-m-d");
        $posted_data['product_type'] = 2; // means bulk products
        $posted_data['orders_join'] = true;
        $posted_data['with_data'] = true;
        $posted_data['prod_disc'] = 1; // 1 equal to false, means no min and max apply yet now
        $posted_data['product_orders_join'] = true;
        $posted_data['product_orders_join_all_columns'] = true;
        
        if ( isset($data['order_id']) && $data['order_id'] )
            $posted_data['order_id'] = $data['order_id'];
        if ( isset($data['product_id']) && $data['product_id'] )
            $posted_data['product_id'] = $data['product_id'];

        // $posted_data['groupBy_value'] = 'order_products.product_id';

        $admin_shipping_earn = 0;
        $supplier_shipping_earn = 0;

        $update_prod_order = array();
        $products_info = Product::getProducts($posted_data);

        if ( count($products_info) > 0 ) {
            foreach ($products_info as $key => $value) {       
                $arr = array();
                $arr['order_prod_id'] = $value['order_prod_id'];
                $arr['order_id'] = $value['orders_id'];

                if ($value['order_prod_adm_aftr_reedem'] != 0)
                    $before_disc_earning = $value['order_prod_adm_aftr_reedem'] + $value['order_prod_sup_aftr_reedem'];
                else 
                    $before_disc_earning = $value['order_prod_admin_earn'] + $value['order_prod_supplier_earn'];
                    
                    // $before_disc_earning = $value['total_admin'] + $value['total_supplier'];

                $after_discount = round( ((100-$value['min_discount'])/100) * $before_disc_earning,2);

                $arr['adm_aftr_disc'] = round ($after_discount * ($value->category->commission / 100) , 2);
                $arr['sup_aftr_disc'] = round ($after_discount * ((100-$value->category->commission) / 100) , 2);

                if ($value['orders_shipping_cost'] > 0) {
                    $shipping_cost = $value['orders_shipping_cost'];

                    $admin_shipping_earn = $shipping_cost > 0 ? round( ($value['orders_admin_avg'] * $shipping_cost) / 100 , 2) : 0;
                    $supplier_shipping_earn = $shipping_cost > 0 ? round( ($value['orders_supplier_avg'] * $shipping_cost) / 100 , 2) : 0;
                }
                else {
                    $admin_shipping_earn = 0;
                    $supplier_shipping_earn = 0;
                }

                $after_disc_earning = $arr['adm_aftr_disc'] + $arr['sup_aftr_disc'];

                $arr['discount'] = ($before_disc_earning - $after_disc_earning) > 0 ? $before_disc_earning - $after_disc_earning : 0;
                $arr['prod_disc'] = 2; // 2 is for minimum discount.
                $update_prod_order[] = $arr;
            }
        }

        foreach ($update_prod_order as $key => $value) {
            OrderProduct::saveUpdateOrderProduct([
                'update_id'      => $value['order_prod_id'],
                'adm_aftr_disc'  => $value['adm_aftr_disc'],
                'sup_aftr_disc'  => $value['sup_aftr_disc'],
                'discount'       => $value['discount'],
                'total_admin'    => $value['adm_aftr_disc'],
                'total_supplier' => $value['sup_aftr_disc'],
                'prod_disc'      => $value['prod_disc'],
            ]);
        }

        $orders_data = array_reduce(
            $update_prod_order,
            function ($carry, $item) {
                $orderId = $item['order_id'];
                if (array_key_exists($orderId, $carry)) {
                    $preItem = $carry[$item['order_id']];
                    $carry[$item['order_id']] = [
                        'order_id'       => $preItem['order_id'],
                        'order_prod_ids'    => array_merge($preItem['order_prod_ids'], [ $item['order_prod_id'] ]),
                        'adm_aftr_disc'  => $preItem['adm_aftr_disc'] + $item['adm_aftr_disc'],
                        'sup_aftr_disc'  => $preItem['sup_aftr_disc'] + $item['sup_aftr_disc'],
                        'discount'       => $preItem['discount'] + $item['discount'],
                        'prod_disc'      => $preItem['prod_disc'] + $item['prod_disc']
                    ];
                }
                else {
                    $carry[$item['order_id']] = [
                        'order_id'      => $item['order_id'],
                        'order_prod_ids'   => [ $item['order_prod_id'] ],
                        'adm_aftr_disc' => $item['adm_aftr_disc'],
                        'sup_aftr_disc' => $item['sup_aftr_disc'],
                        'discount'      => $item['discount'],
                        'prod_disc'     => $item['prod_disc']
                    ];
                }
                return $carry;
            },
            []
        );

        if ( count($orders_data) > 0 ) {
            foreach($orders_data as $key => $value) {
                $odr_data = Order::find($value['order_id']);

                if ($odr_data) {
                    $odr_data->discount_bulk += $value['discount'];
                    $odr_data->grand_total -= $value['discount'];
                    $odr_data->admin_gross = ($value['adm_aftr_disc'] + $admin_shipping_earn);
                    $odr_data->supplier_gross = ($value['sup_aftr_disc'] + $supplier_shipping_earn);
                    $odr_data->save();
                }
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function calculate_orders_max_discounts(Request $request, $posted_data = array())
    {
        $posted_data = array();
        $posted_data['max_disc_qualify'] = true; // means bulk products
        $posted_data['within_time_limit'] = date("Y-m-d");
        $posted_data['product_type'] = 2; // means bulk products
        $posted_data['orders_join'] = true;
        $posted_data['with_data'] = true;
        $posted_data['prod_disc'] = 2; // 2 equal to min, means get those products that already qualify for minimum discounts
        $posted_data['product_orders_join'] = true;
        $posted_data['product_orders_join_all_columns'] = true;
        // $posted_data['groupBy_value'] = 'order_products.product_id';

        $admin_shipping_earn = 0;
        $supplier_shipping_earn = 0;
        
        $update_prod_order = array();
        $products_info = Product::getProducts($posted_data);
        
        if ( count($products_info) > 0 ) {
            foreach ($products_info as $key => $value) {       
                $arr = array();
                $arr['order_prod_id'] = $value['order_prod_id'];
                $arr['order_id'] = $value['orders_id'];

                if ($value['order_prod_adm_aftr_reedem'] != 0)
                    $before_disc_earning = $value['order_prod_adm_aftr_reedem'] + $value['order_prod_sup_aftr_reedem'];
                else 
                    $before_disc_earning = $value['order_prod_admin_earn'] + $value['order_prod_supplier_earn'];

                $after_discount = round( ((100-$value['max_discount'])/100) * $before_disc_earning,2);

                $arr['adm_aftr_disc'] = round ($after_discount * ($value->category->commission / 100) , 2);
                $arr['sup_aftr_disc'] = round ($after_discount * ((100-$value->category->commission) / 100) , 2);

                if ($value['orders_shipping_cost'] > 0) {
                    $shipping_cost = $value['orders_shipping_cost'];

                    $admin_shipping_earn = $shipping_cost > 0 ? round( ($value['orders_admin_avg'] * $shipping_cost) / 100 , 2) : 0;
                    $supplier_shipping_earn = $shipping_cost > 0 ? round( ($value['orders_supplier_avg'] * $shipping_cost) / 100 , 2) : 0;
                }
                else {
                    $admin_shipping_earn = 0;
                    $supplier_shipping_earn = 0;
                }

                $after_disc_earning = $arr['adm_aftr_disc'] + $arr['sup_aftr_disc'];

                $arr['discount'] = ($before_disc_earning - $after_disc_earning) > 0 ? $before_disc_earning - $after_disc_earning : 0;
                $arr['prod_disc'] = 3; // 3 is for maximum discount.
                $update_prod_order[] = $arr;
            }
        }

        foreach ($update_prod_order as $key => $value) {
            OrderProduct::saveUpdateOrderProduct([
                'update_id'      => $value['order_prod_id'],
                'adm_aftr_disc'  => $value['adm_aftr_disc'],
                'sup_aftr_disc'  => $value['sup_aftr_disc'],
                'discount'       => $value['discount'],
                'total_admin'    => $value['adm_aftr_disc'],
                'total_supplier' => $value['sup_aftr_disc'],
                'prod_disc'      => $value['prod_disc'],
            ]);
        }

        $orders_data = array_reduce(
            $update_prod_order,
            function ($carry, $item) {
                $orderId = $item['order_id'];
                if (array_key_exists($orderId, $carry)) {
                    $preItem = $carry[$item['order_id']];
                    $carry[$item['order_id']] = [
                        'order_id'       => $preItem['order_id'],
                        'order_prod_ids'    => array_merge($preItem['order_prod_ids'], [ $item['order_prod_id'] ]),
                        'adm_aftr_disc'  => $preItem['adm_aftr_disc'] + $item['adm_aftr_disc'],
                        'sup_aftr_disc'  => $preItem['sup_aftr_disc'] + $item['sup_aftr_disc'],
                        'discount'       => $preItem['discount'] + $item['discount'],
                        'prod_disc'      => $preItem['prod_disc'] + $item['prod_disc']
                    ];
                }
                else {
                    $carry[$item['order_id']] = [
                        'order_id'      => $item['order_id'],
                        'order_prod_ids'   => [ $item['order_prod_id'] ],
                        'adm_aftr_disc' => $item['adm_aftr_disc'],
                        'sup_aftr_disc' => $item['sup_aftr_disc'],
                        'discount'      => $item['discount'],
                        'prod_disc'     => $item['prod_disc']
                    ];
                }
                return $carry;
            },
            []
        );

        if ( count($orders_data) > 0 ) {
            foreach($orders_data as $key => $value) {
                $odr_data = Order::find($value['order_id']);

                if ($odr_data) {
                    $odr_data->discount_bulk = $value['discount'];
                    $odr_data->grand_total = $odr_data->total - ($value['discount'] + $odr_data->discount_redeem);
                    $odr_data->admin_gross = $value['adm_aftr_disc'];
                    $odr_data->supplier_gross = $value['sup_aftr_disc'];
                    $odr_data->save();
                }
            }
        }
    }
}