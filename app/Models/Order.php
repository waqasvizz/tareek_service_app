<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class Order extends Model
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class)->with('role');
    }

    public function user_multiple_address()
    {
        return $this->belongsTo(UserMultipleAddresse::class);
    }

    public function user_delivery_option()
    {
        return $this->belongsTo(UserDeliveryOption::class);
    }

    // public function user_card()
    // {
    //     return $this->belongsTo(UserCard::class);
    // }

    public function order_product()
    {
        return $this->hasMany(OrderProduct::class)->with('product');
    }

    public function order_service()
    {
        return $this->hasMany(OrderService::class)->with('service');
    }

    public function clearence_documents()
    {
        return $this->hasMany(ClearenceService::class)->with('user_asset_data');
    }

    public function senderDetails()
    {
        return $this->belongsTo('App\Models\User', 'sender_id')->select(['id', 'role_id', 'name', 'email', 'phone_number', 'profile_image'])->with('user_bank');
    }

    public function receiverDetails()
    {
        return $this->belongsTo('App\Models\User', 'receiver_id')->select(['id', 'role_id', 'name', 'email', 'phone_number', 'profile_image', 'company_name'])->with('user_bank');
    }

    public function getOrder($posted_data = array())
    {
        $columns = ['orders.*'];
        $select_columns = array_merge($columns, []);

        $query = Order::latest()
            ->with('senderDetails')
            ->with('receiverDetails')
            ->with('user_multiple_address')
            ->with('user_delivery_option')
            // ->with('user_card')
            ->with('order_product')
            ->with('order_service')
            ->with('clearence_documents');

        if (isset($posted_data['without_with']) && $posted_data['without_with']) {
            $query = Order::latest();
        }

        if (isset($posted_data['id'])) {
            $query = $query->where('orders.id', $posted_data['id']);
        }
        if (isset($posted_data['orders_in'])) {
            $query = $query->whereIn('orders.id', $posted_data['orders_in']);
        }
        if (isset($posted_data['name'])) {
            $query = $query->where('orders.name', 'like', '%' . $posted_data['name'] . '%');
        }
        if (isset($posted_data['sender_id'])) {
            $query = $query->where('orders.sender_id', $posted_data['sender_id']);
        }
        if (isset($posted_data['receiver_id'])) {
            $query = $query->where('orders.receiver_id', $posted_data['receiver_id']);
        }
        if (isset($posted_data['user_multiple_address_id'])) {
            $query = $query->where('orders.user_multiple_address_id', $posted_data['user_multiple_address_id']);
        }
        if (isset($posted_data['user_delivery_option_id'])) {
            $query = $query->where('orders.user_delivery_option_id', $posted_data['user_delivery_option_id']);
        }
        if (isset($posted_data['user_card_id'])) {
            $query = $query->where('orders.user_card_id', $posted_data['user_card_id']);
        }
        if (isset($posted_data['order_type'])) {
            $query = $query->where('orders.order_type', $posted_data['order_type']);
        }
        if (isset($posted_data['supplier_payment'])) {
            if ($posted_data['supplier_payment'] == 'no') $posted_data['supplier_payment'] = 'Pending';
            else if ($posted_data['supplier_payment'] == 'yes') $posted_data['supplier_payment'] = 'Paid';
            $query = $query->where('orders.supplier_payment', $posted_data['supplier_payment']);
        }
        if (isset($posted_data['refund_req'])) {
            if ($posted_data['refund_req'] == 'no') $posted_data['refund_req'] = 'No';
            else if ($posted_data['refund_req'] == 'yes') $posted_data['refund_req'] = 'Yes';
            $query = $query->where('orders.refund_req', $posted_data['refund_req']);
        }
        if (isset($posted_data['refund_status'])) {
            if ($posted_data['refund_status'] == 1) $posted_data['refund_status'] = 'Not-Requested';
            else if ($posted_data['refund_status'] == 2) $posted_data['refund_status'] = 'Requested';
            else if ($posted_data['refund_status'] == 3) $posted_data['refund_status'] = 'Paid';
            else if ($posted_data['refund_status'] == 4) $posted_data['refund_status'] = 'Rejected';
            $query = $query->where('orders.refund_status', $posted_data['refund_status']);
        }
        if (isset($posted_data['payment_status'])) {
            if ($posted_data['payment_status'] == 'False') $posted_data['payment_status'] = 1;
            else if ($posted_data['payment_status'] == 'True') $posted_data['payment_status'] = 2;
            $query = $query->where('orders.payment_status', $posted_data['payment_status']);
        }

        /*
        if (isset($posted_data['refund_req_reason'])) {
            $data->refund_req_reason = $posted_data['refund_req_reason'];
        }
        if (isset($posted_data['refund_status_reason'])) {
            $data->refund_status_reason = $posted_data['refund_status_reason'];
        }
        */


        if (isset($posted_data['search_filter'])) {

            if (isset($posted_data['order_type']) && $posted_data['order_type'] == 'Product')
                $mode = 'Product';
            else if (isset($posted_data['order_type']) && $posted_data['order_type'] == 'Service')
                $mode = 'Service';
            else 
                $mode = '';

            $query = $query->where(function ($query) use ($posted_data, $mode) {
                    $query->where('sender.name', 'like', '%' . $posted_data['search_filter'] . '%')
                        ->orWhere('receiver.name', 'like', '%' . $posted_data['search_filter'] . '%');

                    if ($mode == 'Product')
                        $query->orWhere('products.title', 'like', '%' . $posted_data['search_filter'] . '%');
                    else if ($mode == 'Service')
                        $query->orWhere('services.title', 'like', '%' . $posted_data['search_filter'] . '%');
            });
        }

        if (isset($posted_data['order_status_not_in'])) {
            $query = $query->whereNotIn('orders.order_status', $posted_data['order_status_not_in']);
        }
        if (isset($posted_data['order_status'])) {
            if ($posted_data['order_status'] == 1) $posted_data['order_status'] = 'Pending';
            else if ($posted_data['order_status'] == 2) $posted_data['order_status'] = 'Request accepted';
            else if ($posted_data['order_status'] == 3) $posted_data['order_status'] = 'Request rejected';
            else if ($posted_data['order_status'] == 4) $posted_data['order_status'] = 'On the way';
            else if ($posted_data['order_status'] == 5) $posted_data['order_status'] = 'In-progress';
            else if ($posted_data['order_status'] == 6) $posted_data['order_status'] = 'Completed';
            $query = $query->where('orders.order_status', $posted_data['order_status']);
        }

        if(isset($posted_data['order_products_join'])) {
            $query->join('order_products', 'orders.id', '=', 'order_products.order_id');
            // $query->select('orders.*', 'order_products`.`id` AS `order_product_id', 'order_products`.`price` AS `order_product_price', 'products`.`product_type');
            $columns = ['order_products.id AS order_product_id', 'order_products.price AS order_product_price'];
            $select_columns = array_merge($select_columns, $columns);
        }

        if(isset($posted_data['products_join'])) {
            $query->join('products', 'products.id', '=', 'order_products.product_id');
            $columns = ['products.id AS pro_product_id', 'products.product_type AS pro_product_type', 'products.title AS pro_product_name'];
            
            if(isset($posted_data['order_have'])){
                $query = $query->where('products.product_type', $posted_data['order_have']);
            }
            if (isset($posted_data['product_id'])) {
                $query = $query->where('products.id', $posted_data['product_id']);
            }

            $select_columns = array_merge($select_columns, $columns);
        }

        if(isset($posted_data['sender_users_join'])) {
            $query->join('users as sender', 'orders.sender_id', '=', 'sender.id');
            $columns = ['sender.name AS sender_name'];
            $select_columns = array_merge($select_columns, $columns);
        }

        if(isset($posted_data['receiver_users_join'])) {
            $query->join('users as receiver', 'orders.receiver_id', '=', 'receiver.id');
            $columns = ['receiver.name AS receiver_name'];
            $select_columns = array_merge($select_columns, $columns);
        }
        
        if(isset($posted_data['order_services_join'])) {
            $query->join('order_services', 'order_services.order_id', '=', 'orders.id');
            $columns = ['order_services.service_price AS ser_service_price'];

            if(isset($posted_data['service_id'])){
                $query = $query->where('order_services.service_id', $posted_data['service_id']);
            }

            $select_columns = array_merge($select_columns, $columns);
        }

        if(isset($posted_data['services_join'])) {
            $query->join('services', 'services.id', '=', 'order_services.service_id');
            $columns = ['services.id AS ser_service_id', 'services.title AS ser_service_title'];
            $select_columns = array_merge($select_columns, $columns);
        }

        if(isset($posted_data['sumBy_multiple_column'])) {
            $sum_cols_query = "";
            foreach ($posted_data['sumBy_multiple_columnNames'] as $key => $item) {
                $sum_cols_query .= "sum({$key}) AS {$item}, ";
            }

            // this code will remove the last two characters from the string
            $sum_cols_query = substr($sum_cols_query, 0, -2);

            // $columns = [DB::raw('sum(admin_gross) AS admin_gross_sum')];

            if ($posted_data['show_only_sums'] == true)
                $select_columns = [];

            $columns = [DB::raw("{$sum_cols_query}")];
            $select_columns = array_merge($select_columns, $columns);
        }

        // $query->join('users', 'orders.id', '=', 'order_products.product_id');
        // $query->join('products', 'products.id', '=', 'order_products.product_id');

        // $query->groupBy('orders.id');

        // if (isset($posted_data['service_id'])) {


        // if (isset($posted_data['product_id'])) {
        //     $query = $query->where('order_products.product_id', $posted_data['product_id']);
            // $query->join('order_products', 'order_products.order_id', '=', 'orders.id');
        // }
        

        // echo "Line no deee@d"."<br>";
        // echo "<pre>";
        // print_r($select_columns);
        // echo "</pre>";
        // exit("@@@@");
        // $query->select('orders.*');

        if (isset($posted_data['groupBy_value'])) {
            $query->groupBy($posted_data['groupBy_value']);

            if (isset($posted_data['groupBy_with_sum'])) {

                $sum_cols_query = "";
                foreach ($posted_data['groupBy_with_sum'] as $key => $item) {
                    $sum_cols_query .= "sum({$key}) AS {$item}, ";
                }
    
                // this code will remove the last two characters from the string
                $sum_cols_query = substr($sum_cols_query, 0, -2);
    
                // if ($posted_data['show_only_sums'] == true)
                //     $select_columns = [];
    
                $columns = [DB::raw("{$sum_cols_query}")];
                $select_columns = array_merge($select_columns, $columns);

                // $query->selectRaw('sum(admin_gross) as admin_gross_sum')
                //     ->pluck('admin_gross_sum');

                // $columns = [DB::raw("{$sum_cols_query}")];
                // $select_columns = array_merge($select_columns, $columns);
            }
            // $query = $query->pluck('admin_gross_sum');
        }

        $query->select($select_columns);
        // else if (isset($posted_data['groupBy_with_sum'])) {
        //     // Document::groupBy('users_editor_id')
        //         ->selectRaw('sum(admin_gross) as sum, admin_gross_sum')
        //         ->pluck('sum','users_editor_id');
        // }
        
        $query->getQuery()->orders = null;
        if (isset($posted_data['orderBy_name'])) {
            $query->orderBy($posted_data['orderBy_name'], $posted_data['orderBy_value']);
        } else {
            $query->orderBy('orders.id', 'DESC');
        }

        if (isset($posted_data['paginate'])) {
            $result = $query->paginate($posted_data['paginate']);
        } else {
            if (isset($posted_data['detail'])) {
                $result = $query->first();
            } else if (isset($posted_data['count'])) {
                $result = $query->count();
            } else if (isset($posted_data['to_array'])) {
                $result = $query->get()->toArray();
            } else {
                $result = $query->get();
            }
        }
        
        if (isset($posted_data['print_query'])) {
            $result = $query->toSql();
            echo "Line no @"."<br>";
            echo "<pre>";
            print_r($posted_data);
            print_r($result);
            echo "</pre>";
            exit("@@@@");
        }

        if (isset($posted_data['sumBy_column'])) {
            $result = $result->sum($posted_data['sumBy_columnName']);
        }

        return $result;
    }


    public function saveUpdateOrder($posted_data = array())
    {
        if(isset($posted_data['update_bulk_statuses'])){
            if ($posted_data['update_bulk_statuses'] == 1) $posted_data['update_bulk_statuses'] = 'Pending';
            else if ($posted_data['update_bulk_statuses'] == 2) $posted_data['update_bulk_statuses'] = 'Request accepted';
            else if ($posted_data['update_bulk_statuses'] == 3) $posted_data['update_bulk_statuses'] = 'Request rejected';
            else if ($posted_data['update_bulk_statuses'] == 4) $posted_data['update_bulk_statuses'] = 'On the way';
            else if ($posted_data['update_bulk_statuses'] == 5) $posted_data['update_bulk_statuses'] = 'In-progress';
            else if ($posted_data['update_bulk_statuses'] == 6) $posted_data['update_bulk_statuses'] = 'Completed';

            $data = Order::whereIn('id', $posted_data['order_ids'])
                        ->update([
                            'order_status' => $posted_data['update_bulk_statuses'],
                            'calculated' => $posted_data['calculated'],
                            'rejection_message' => $posted_data['rejection_message'],
                        ]);
            return $data;
        }
        else if (isset($posted_data['update_id'])) {
            $data = Order::find($posted_data['update_id']);
        } else {
            $data = new Order;
        }

        if (isset($posted_data['order_type'])) {
            $data->order_type = $posted_data['order_type'];
        }
        if (isset($posted_data['order_status'])) {
            $data->order_status = $posted_data['order_status'];
        }
        if (isset($posted_data['order_products'])) {
            $data->order_products = $posted_data['order_products'];
        }
        if (isset($posted_data['calculated'])) {
            if ($posted_data['calculated'] == 1) $posted_data['calculated'] = 'False';
            else if ($posted_data['calculated'] == 2) $posted_data['calculated'] = 'True';
            $data->calculated = $posted_data['calculated'];
        }
        if (isset($posted_data['payment_status'])) {
            if ($posted_data['payment_status'] == 1) $posted_data['payment_status'] = 'False';
            else if ($posted_data['payment_status'] == 2) $posted_data['payment_status'] = 'True';
            $data->payment_status = $posted_data['payment_status'];
        }
        if (isset($posted_data['sender_id'])) {
            $data->sender_id = $posted_data['sender_id'];
        }
        if (isset($posted_data['receiver_id'])) {
            $data->receiver_id = $posted_data['receiver_id'];
        }
        if (isset($posted_data['user_multiple_address_id'])) {
            $data->user_multiple_address_id = $posted_data['user_multiple_address_id'];
        }
        if (isset($posted_data['user_delivery_option_id'])) {
            $data->user_delivery_option_id = $posted_data['user_delivery_option_id'];
        }
        if (isset($posted_data['user_card_id'])) {
            $data->user_card_id = $posted_data['user_card_id'];
        }
        if (isset($posted_data['total'])) {
            $data->total = $posted_data['total'];
        }
        if (isset($posted_data['shipping_cost'])) {
            $data->shipping_cost = $posted_data['shipping_cost'];
        }
        if (isset($posted_data['discount_redeem'])) {
            $data->discount_redeem = $posted_data['discount_redeem'];
        }
        if (isset($posted_data['discount_bulk'])) {
            $data->discount_bulk = $posted_data['discount_bulk'];
        }
        if (isset($posted_data['grand_total'])) {
            $data->grand_total = $posted_data['grand_total'];
        }
        if (isset($posted_data['admin_gross'])) {
            $data->admin_gross = $posted_data['admin_gross'];
        }
        if (isset($posted_data['supplier_gross'])) {
            $data->supplier_gross = $posted_data['supplier_gross'];
        }
        if (isset($posted_data['admin_avg'])) {
            $data->admin_avg = $posted_data['admin_avg'];
        }
        if (isset($posted_data['supplier_avg'])) {
            $data->supplier_avg = $posted_data['supplier_avg'];
        }
        if (isset($posted_data['redeem_point'])) {
            $data->redeem_point = $posted_data['redeem_point'];
        }
        if (isset($posted_data['rejection_message'])) {
            $data->rejection_message = $posted_data['rejection_message'];
        }
        if (isset($posted_data['points_earn'])) {
            $data->points_earn = $posted_data['points_earn'];
        }
        if (isset($posted_data['supplier_payment'])) {
            if ($posted_data['supplier_payment'] == 1) $posted_data['supplier_payment'] = 'Pending';
            else if ($posted_data['supplier_payment'] == 2) $posted_data['supplier_payment'] = 'Paid';
            $data->supplier_payment = $posted_data['supplier_payment'];
        }
        if (isset($posted_data['refund_req'])) {
            if ($posted_data['refund_req'] == 1) $posted_data['refund_req'] = 'No';
            else if ($posted_data['refund_req'] == 2) $posted_data['refund_req'] = 'Yes';
            $data->refund_req = $posted_data['refund_req'];
        }
        if (isset($posted_data['refund_req_reason'])) {
            $data->refund_req_reason = $posted_data['refund_req_reason'];
        }
        if (isset($posted_data['refund_status'])) {
            if ($posted_data['refund_status'] == 1) $posted_data['refund_status'] = 'Not-Requested';
            if ($posted_data['refund_status'] == 2) $posted_data['refund_status'] = 'Requested';
            else if ($posted_data['refund_status'] == 3) $posted_data['refund_status'] = 'Paid';
            else if ($posted_data['refund_status'] == 4) $posted_data['refund_status'] = 'Rejected';
            $data->refund_status = $posted_data['refund_status'];
        }
        if (isset($posted_data['refund_status_reason'])) {
            $data->refund_status_reason = $posted_data['refund_status_reason'];
        }
        
        $data->save();

        $filter = array();
        $filter['detail'] = true;
        $filter['id'] = $data->id;
        
        if (isset($posted_data['without_with']) && $posted_data['without_with']) {
            $filter['without_with'] = true;
        }

        $data = Order::getOrder($filter);
        return $data;
    }


    public function deleteOrder($id) {
        $data = Order::find($id);
        if (isset($data->id) )
            return $data->delete();
        else 
            return false;
    }
}   