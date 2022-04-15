<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCard extends Model
{
    use HasFactory;

    public function getUserCard($posted_data = array())
    {
        $query = UserCard::latest();

        if (isset($posted_data['id'])) {
            $query = $query->where('id', $posted_data['id']);
        }
        if (isset($posted_data['user_id'])) {
            $query = $query->where('user_id', $posted_data['user_id']);
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



    public function saveUpdateUserCard($posted_data = array())
    {
        if (isset($posted_data['update_id'])) {
            $data = UserCard::find($posted_data['update_id']);
        } else {
            $data = new UserCard;
        }

        if (isset($posted_data['user_id'])) {
            $data->user_id = $posted_data['user_id'];
        }
        if (isset($posted_data['card_name'])) {
            $data->card_name = $posted_data['card_name'];
        }
        if (isset($posted_data['card_number'])) {
            $data->card_number = $posted_data['card_number'];
        }
        if (isset($posted_data['exp_month'])) {
            $data->exp_month = $posted_data['exp_month'];
        }
        if (isset($posted_data['exp_year'])) {
            $data->exp_year = $posted_data['exp_year'];
        }
        if (isset($posted_data['cvc_number'])) {
            $data->cvc_number = $posted_data['cvc_number'];
        }

        $data->save();
        return $data;
    }


    public function deleteUserCard($id) {
        $data = UserCard::find($id);
        if (isset($data->id) )
            return $data->delete();
        else 
            return false;
    }
}   