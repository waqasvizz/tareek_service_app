<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDeliveryOption extends Model
{
    use HasFactory;

    public function getUserDeliveryOption($posted_data = array())
    {
        $query = UserDeliveryOption::latest();

        if (isset($posted_data['id'])) {
            $query = $query->where('id', $posted_data['id']);
        }
        if (isset($posted_data['user_id'])) {
            $query = $query->where('user_id', $posted_data['user_id']);
        }
        if (isset($posted_data['status'])) {
            $query = $query->where('status', $posted_data['status']);
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
            } else {
                $result = $query->get();
            }
        }
        return $result;
    }



    public function saveUpdateUserDeliveryOption($posted_data = array())
    {
        if (isset($posted_data['update_id'])) {
            $data = UserDeliveryOption::find($posted_data['update_id']);
        } else {
            $data = new UserDeliveryOption;
        }

        if (isset($posted_data['user_id'])) {
            $data->user_id = $posted_data['user_id'];
        }
        if (isset($posted_data['title'])) {
            $data->title = $posted_data['title'];
        }
        if (isset($posted_data['status'])) {
            $data->status = $posted_data['status'];
        }
        if (isset($posted_data['amount'])) {
            $data->amount = $posted_data['amount'];
        }

        $data->save();
        return $data;
    }


    public function deleteUserDeliveryOption($id) {
        $data = UserDeliveryOption::find($id);
        if (isset($data->id) )
            return $data->delete();
        else 
            return false;
    }
}   