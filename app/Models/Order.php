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
        return $this->belongsTo('App\Models\User', 'receiver_id')->select(['id', 'role_id', 'name', 'email', 'phone_number', 'profile_image']);
    }

    public function getOrder($posted_data = array())
    {
        $query = Order::latest()->with('senderDetails')->with('receiverDetails')->with('user_multiple_address')->with('user_delivery_option')->with('user_card')->with('order_product')->with('order_service');

        if (isset($posted_data['id'])) {
            $query = $query->where('id', $posted_data['id']);
        }
        if (isset($posted_data['name'])) {
            $query = $query->where('name', 'like', '%' . $posted_data['name'] . '%');
        }
        if (isset($posted_data['sender_id'])) {
            $query = $query->where('sender_id', $posted_data['sender_id']);
        }
        if (isset($posted_data['receiver_id'])) {
            $query = $query->where('receiver_id', $posted_data['receiver_id']);
        }
        if (isset($posted_data['user_multiple_address_id'])) {
            $query = $query->where('user_multiple_address_id', $posted_data['user_multiple_address_id']);
        }
        if (isset($posted_data['user_delivery_option_id'])) {
            $query = $query->where('user_delivery_option_id', $posted_data['user_delivery_option_id']);
        }
        if (isset($posted_data['user_card_id'])) {
            $query = $query->where('user_card_id', $posted_data['user_card_id']);
        }
        if (isset($posted_data['order_type'])) {
            $query = $query->where('order_type', $posted_data['order_type']);
        }
        if (isset($posted_data['order_status'])) {
            $query = $query->where('order_status', $posted_data['order_status']);
        }

        $query->select('*');
        
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



    public function saveUpdateOrder($posted_data = array())
    {
        if (isset($posted_data['update_id'])) {
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