<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPointLog extends Model
{
    use HasFactory;

    public function user_point()
    {
        return $this->belongsTo(UserPoint::class);
    }

    public function getUserPointLog($posted_data = array())
    {
        $query = UserPointLog::latest()->with('user_point');

        if (isset($posted_data['id'])) {
            $query = $query->where('id', $posted_data['id']);
        }
        if (isset($posted_data['user_point_id'])) {
            $query = $query->where('user_point_id', $posted_data['user_point_id']);
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



    public function saveUpdateUserPointLog($posted_data = array())
    {
        if (isset($posted_data['update_id'])) {
            $data = UserPointLog::find($posted_data['update_id']);
        } else {
            $data = new UserPointLog;
        } 
        
        if (isset($posted_data['user_point_id'])) {
            $data->user_point_id = $posted_data['user_point_id'];
        }
        if (isset($posted_data['point_value'])) {
            $data->point_value = $posted_data['point_value'];
        }
        if (isset($posted_data['point_target'])) {
            $data->point_target = $posted_data['point_target'];
        }
        if (isset($posted_data['per_point_value'])) {
            $data->per_point_value = $posted_data['per_point_value'];
        }
        if (isset($posted_data['total_point_count'])) {
            $data->total_point_count = $posted_data['total_point_count'];
        }
        if (isset($posted_data['total_point_value'])) {
            $data->total_point_value = $posted_data['total_point_value'];
        }

        $data->save();
        return $data;
    }


    public function deleteUserPointLog($id) {
        $data = UserPointLog::find($id);
        if (isset($data->id) )
            return $data->delete();
        else 
            return false;
    }
}   