<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function user_card()
    {
        return $this->belongsTo(UserCard::class);
    }

    public function order_product()
    {
        return $this->hasMany(OrderProduct::class)->with('product');
    }

    public function order_service()
    {
        return $this->hasMany(OrderService::class)->with('service');
    }

    public function senderDetails()
    {
        return $this->belongsTo('App\Models\User', 'sender_id')->select(['id', 'role_id', 'name', 'email', 'phone_number', 'profile_image']);
    }

    public function receiverDetails()
    {
        return $this->belongsTo('App\Models\User', 'receiver_id')->select(['id', 'role_id', 'name', 'email', 'phone_number', 'profile_image', 'company_name']);
    }

    public function getOrder($posted_data = array())
    {
        $query = Order::latest()->with('senderDetails')->with('receiverDetails')->with('user_multiple_address')->with('user_delivery_option')->with('user_card')->with('order_product')->with('order_service');

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
        if (isset($posted_data['order_status'])) {
            if ($posted_data['order_status'] == 1) $posted_data['order_status'] = 'Pending';
            else if ($posted_data['order_status'] == 2) $posted_data['order_status'] = 'Request accepted';
            else if ($posted_data['order_status'] == 3) $posted_data['order_status'] = 'Request rejected';
            else if ($posted_data['order_status'] == 4) $posted_data['order_status'] = 'On the way';
            else if ($posted_data['order_status'] == 5) $posted_data['order_status'] = 'In-progress';
            else if ($posted_data['order_status'] == 6) $posted_data['order_status'] = 'Completed';
            $query = $query->where('orders.order_status', $posted_data['order_status']);
        }

        if(isset($posted_data['products_join'])){
            $query->join('order_products', 'orders.id', '=', 'order_products.order_id');
            $query->join('products', 'products.id', '=', 'order_products.product_id');
            $query = $query->where('product_type', $posted_data['products_join']);
            $query->groupBy('orders.id');
            $query->select('orders.*', 'order_products`.`id` AS `order_product_id', 'order_products`.`price` AS `order_product_price', 'products`.`product_type');
        }
        // else {
        //     $query->select('products.*');
        // }

        if (isset($posted_data['service_id'])) {
            $query = $query->where('order_services.service_id', $posted_data['service_id']);
            $query->join('order_services', 'order_services.order_id', '=', 'orders.id');
        }

        if (isset($posted_data['product_id'])) {
            $query = $query->where('order_products.product_id', $posted_data['product_id']);
            $query->join('order_products', 'order_products.order_id', '=', 'orders.id');
        }
        
        if (isset($posted_data['print_query'])) {
            $result = $query->toSql();
            echo "Line no @"."<br>";
            echo "<pre>";
            print_r($result);
            echo "</pre>";
            exit("@@@@");
        }

        $query->select('orders.*');
        
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
        if (isset($posted_data['grand_total'])) {
            $data->grand_total = $posted_data['grand_total'];
        }
        if (isset($posted_data['redeem_point'])) {
            $data->redeem_point = $posted_data['redeem_point'];
        }
        if (isset($posted_data['total'])) {
            $data->total = $posted_data['total'];
        }
        if (isset($posted_data['discount'])) {
            $data->discount = $posted_data['discount'];
        }
        if (isset($posted_data['rejection_message'])) {
            $data->rejection_message = $posted_data['rejection_message'];
        }

        
        $data->save();

        $data = Order::getOrder([
            'detail' => true,
            'id' => $data->id
        ]);

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