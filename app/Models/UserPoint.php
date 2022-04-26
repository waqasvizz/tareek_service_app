<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPoint extends Model
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function point_category()
    {
        return $this->belongsTo(PointCategorie::class);
    }


    public function getUserPoint($posted_data = array())
    {
        $query = UserPoint::latest()->with('user')->with('point_category');

        if (isset($posted_data['id'])) {
            $query = $query->where('id', $posted_data['id']);
        }
        if (isset($posted_data['user_id'])) {
            $query = $query->where('user_id', $posted_data['user_id']);
        }
        if (isset($posted_data['point_categorie_id'])) {
            $query = $query->where('point_categorie_id', $posted_data['point_categorie_id']);
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



    public function saveUpdateUserPoint($posted_data = array())
    {
        if (isset($posted_data['update_id'])) {
            $data = UserPoint::find($posted_data['update_id']);
        } else {
            $data = new UserPoint;
        } 
        
        if (isset($posted_data['user_id'])) {
            $data->user_id = $posted_data['user_id'];
        }
        if (isset($posted_data['point_categorie_id'])) {
            $data->point_categorie_id = $posted_data['point_categorie_id'];
        }
        if (isset($posted_data['total_points'])) {
            $data->total_points = $posted_data['total_points'];
        }
        if (isset($posted_data['last_points'])) {
            $data->last_points = $posted_data['last_points'];
        }

        $data->save();
        return $data;
    }


    public function deleteUserPoint($id) {
        $data = UserPoint::find($id);
        if (isset($data->id) )
            return $data->delete();
        else 
            return false;
    }
}   