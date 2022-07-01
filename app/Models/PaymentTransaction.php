<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    use HasFactory;

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class)->with('category');
    }

    public function orders_user_card_id()
    {
        return $this->belongsTo(UserCard::class, 'orders_user_card_id');
    }

    public function getPaymentTransaction($posted_data = array())
    {
        $columns = ['payment_transactions.*'];
        $select_columns = array_merge($columns, []);

        $query = PaymentTransaction::latest()
            // ->with('order')->with('service');
            ->with('orders_user_card_id');

        if (isset($posted_data['id'])) {
            $query = $query->where('payment_transactions.id', $posted_data['id']);
        }
        if (isset($posted_data['order_id'])) {
            $query = $query->where('payment_transactions.order_id', $posted_data['order_id']);
        }
        if (isset($posted_data['sender_user_id'])) {
            $query = $query->where('payment_transactions.sender_user_id', $posted_data['sender_user_id']);
        }
        if (isset($posted_data['receiver_user_id'])) {
            $query = $query->where('payment_transactions.receiver_user_id', $posted_data['receiver_user_id']);
        }
        if(isset($posted_data['filter_by_date'])){
            $query = $query->where('payment_transactions.created_at', '>=' ,$posted_data['filter_by_date']);
        }
        if(isset($posted_data['order_status'])){
            $query = $query->where('orders.order_status', $posted_data['order_status']);
        }
        if(isset($posted_data['order_type'])){
            $query = $query->where('orders.order_type', $posted_data['order_type']);
        }
        if(isset($posted_data['order_products'])){
            $query = $query->where('orders.order_products', $posted_data['order_products']);
        }

        if(isset($posted_data['orders_join'])){
            $query->rightjoin('orders', 'orders.id', '=', 'payment_transactions.order_id');
            
            $columns = ['orders.id as orders_id', 'orders.order_status as orders_order_status', 'orders.order_products as orders_order_products', 'orders.sender_id as orders_sender_id', 'orders.receiver_id as orders_receiver_id', 'orders.user_multiple_address_id as orders_user_multiple_address_id', 'orders.user_card_id as orders_user_card_id', 'orders.total as orders_total', 'orders.discount as orders_discount', 'orders.grand_total as orders_grand_total', 'orders.redeem_point as orders_redeem_point'];

            // $columns = ['orders.id as orders_id', 'orders.order_type as orders_order_type', 'orders.order_status as orders_order_status', 'orders.sender_id as orders_sender_id', 'orders.receiver_id as orders_receiver_id', 'orders.user_multiple_address_id as orders_user_multiple_address_id', 'orders.user_delivery_option_id as orders_user_delivery_option_id', 'orders.user_card_id as orders_user_card_id', 'orders.total as orders_total', 'orders.discount as orders_discount', 'orders.grand_total as orders_grand_total', 'orders.redeem_point as orders_redeem_point'];
            // $columns = ['orders.*'];

            $select_columns = array_merge($select_columns, $columns);
        }

        if(isset($posted_data['product_orders_join'])){
            $query->join('order_products', 'order_products.order_id', '=', 'orders.id');
            $columns = ['order_products.price as order_products_price', 'order_products.net_price as order_products_net_price'];
            $select_columns = array_merge($select_columns, $columns);
        }

        $query->select($select_columns);
        
        $query->getQuery()->orders = null;
        if (isset($posted_data['orderBy_name'])) {
            $query->orderBy($posted_data['orderBy_name'], $posted_data['orderBy_value']);
        } else {
            $query->orderBy('id', 'DESC');
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

        if (isset($posted_data['to_sql'])) {
            $result = $query->toSql();
            echo '<pre>';
            print_r($result);
            exit;
        }

        if (isset($posted_data['sumBy_column'])) {
            $result = $result->sum($posted_data['sumBy_columnName']);
        }
        
        return $result;
    }



    public function saveUpdatePaymentTransaction($posted_data = array())
    {
        if (isset($posted_data['update_id'])) {
            $data = PaymentTransaction::find($posted_data['update_id']);
        } else {
            $data = new PaymentTransaction;
        }

        if (isset($posted_data['order_id'])) {
            $data->order_id = $posted_data['order_id'];
        }
        if (isset($posted_data['sender_user_id'])) {
            $data->sender_user_id = $posted_data['sender_user_id'];
        }
        if (isset($posted_data['receiver_user_id'])) {
            $data->receiver_user_id = $posted_data['receiver_user_id'];
        }
        if (isset($posted_data['currency'])) {
            $data->currency = $posted_data['currency'];
        }
        if (isset($posted_data['total_amount_captured'])) {
            $data->total_amount_captured = $posted_data['total_amount_captured'];
        }
        if (isset($posted_data['admin_amount_captured'])) {
            $data->admin_amount_captured = $posted_data['admin_amount_captured'];
        }
        if (isset($posted_data['provider_amount_captured'])) {
            $data->provider_amount_captured = $posted_data['provider_amount_captured'];
        }
        if (isset($posted_data['admin_response_object'])) {
            $data->admin_response_object = $posted_data['admin_response_object'];
        }
        if (isset($posted_data['provider_response_object'])) {
            $data->provider_response_object = $posted_data['provider_response_object'];
        }

        $data->save();
        return $data;
    }


    public function deletePaymentTransaction($id) {
        $data = PaymentTransaction::find($id);
        if (isset($data->id) )
            return $data->delete();
        else 
            return false;
    }
}   