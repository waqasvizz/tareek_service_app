<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserMultipleAddresse extends Model
{
    use HasFactory;

    public function getUserMultipleAddresse($posted_data = array())
    {
        $query = UserMultipleAddresse::latest();

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



    public function saveUpdateUserMultipleAddresse($posted_data = array())
    {
        if (isset($posted_data['update_id'])) {
            $data = UserMultipleAddresse::find($posted_data['update_id']);
        } else {
            $data = new UserMultipleAddresse;
        }

        if (isset($posted_data['user_id'])) {
            $data->user_id = $posted_data['user_id'];
        }
        if (isset($posted_data['title'])) {
            $data->title = $posted_data['title'];
        }
        if (isset($posted_data['address'])) {
            $data->address = $posted_data['address'];
        }
        if (isset($posted_data['latitude'])) {
            $data->latitude = $posted_data['latitude'];
        }
        if (isset($posted_data['longitude'])) {
            $data->longitude = $posted_data['longitude'];
        }

        $data->save();
        return $data;
    }


    public function deleteUserMultipleAddresse($id) {
        $data = UserMultipleAddresse::find($id);
        if (isset($data->id) )
            return $data->delete();
        else 
            return false;
    }
}   