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
        return $this->hasMany(OrderProduct::class);
    }

    public function getOrder($posted_data = array())
    {
        $query = Order::latest()->with('user')->with('user_multiple_address')->with('user_delivery_option')->with('user_card')->with('order_product');

        if (isset($posted_data['id'])) {
            $query = $query->where('id', $posted_data['id']);
        }
        if (isset($posted_data['name'])) {
            $query = $query->where('name', 'like', '%' . $posted_data['name'] . '%');
        }
        if (isset($posted_data['user_id'])) {
            $query = $query->where('user_id', $posted_data['user_id']);
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
            $query->orderBy('id', 'ASC');
        }

        if (isset($posted_data['paginate'])) {
            $result = $query->paginate($posted_data['paginate']);
        } else {
            if (isset($posted_data['detail'])) {
                $result = $query->first();
            } else if (isset($posted_data['count'])) {
                $result = $query->count();
            } else {
                $result = $query->get();
            }
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
        if (isset($posted_data['user_id'])) {
            $data->user_id = $posted_data['user_id'];
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
        if (isset($posted_data['schedule_date'])) {
            $data->schedule_date = $posted_data['schedule_date'];
        }
        if (isset($posted_data['schedule_time'])) {
            $data->schedule_time = $posted_data['schedule_time'];
        }
        if (isset($posted_data['grand_total'])) {
            $data->grand_total = $posted_data['grand_total'];
        }

        $data->save();
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