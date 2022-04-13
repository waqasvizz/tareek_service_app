<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Support extends Model
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo('App\Models\User')->with('role');
    }

    public function getSupports($posted_data = array())
    {
        $query = Support::latest()->with('user');

        if (isset($posted_data['user_id'])) {
            $query = $query->where('supports.user_id', $posted_data['user_id']);
        }

        if (isset($posted_data['meta_key'])) {
            $query = $query->where('supports.meta_key', $posted_data['meta_key']);
        }

        $query->select('supports.*');
        
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



    public function saveUpdateSupport($posted_data = array())
    {
        if (isset($posted_data['update_id'])) {
            $data = Support::find($posted_data['update_id']);
        } else {
            $data = new Support;
        }

        if (isset($posted_data['user_id'])) {
            $data->user_id = $posted_data['user_id'];
        }
        if (isset($posted_data['meta_key'])) {
            $data->meta_key = $posted_data['meta_key'];
        }
        if (isset($posted_data['meta_value'])) {
            $data->meta_value = $posted_data['meta_value'];
        }

        $data->save();
        return $data;
    }


    public function deleteSupport($id) {
        $data = Support::find($id);
        if ( isset($data->id) )
            return $data->delete();
        else 
            return false;
    }
}