<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointCategorie extends Model
{
    use HasFactory;

    public function getPointCategorie($posted_data = array())
    {
        $query = PointCategorie::latest();

        if (isset($posted_data['id'])) {
            $query = $query->where('id', $posted_data['id']);
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



    public function saveUpdatePointCategorie($posted_data = array())
    {
        if (isset($posted_data['update_id'])) {
            $data = PointCategorie::find($posted_data['update_id']);
        } else {
            $data = new PointCategorie;
        } 
        
        if (isset($posted_data['point_name'])) {
            $data->point_name = $posted_data['point_name'];
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

        $data->save();
        return $data;
    }


    public function deletePointCategorie($id) {
        $data = PointCategorie::find($id);
        if (isset($data->id) )
            return $data->delete();
        else 
            return false;
    }
}   