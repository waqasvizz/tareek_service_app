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

    public function getPaymentTransaction($posted_data = array())
    {
        $query = PaymentTransaction::latest()->with('order')->with('service');

        if (isset($posted_data['id'])) {
            $query = $query->where('id', $posted_data['id']);
        }
        if (isset($posted_data['order_id'])) {
            $query = $query->where('order_id', $posted_data['order_id']);
        }
        if (isset($posted_data['sender_user_id'])) {
            $query = $query->where('sender_user_id', $posted_data['sender_user_id']);
        }
        if (isset($posted_data['receiver_user_id'])) {
            $query = $query->where('receiver_user_id', $posted_data['receiver_user_id']);
        }
        if(isset($posted_data['filter_by_date'])){
            $query = $query->where('created_at', '>=' ,$posted_data['filter_by_date']);
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