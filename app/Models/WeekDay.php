<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeekDay extends Model
{
    use HasFactory;

    public function getWeekDay($posted_data = array())
    {
        $query = WeekDay::latest();

        if (isset($posted_data['id'])) {
            $query = $query->where('id', $posted_data['id']);
        }
        if (isset($posted_data['name'])) {
            $query = $query->where('name', 'like', '%' . $posted_data['name'] . '%');
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



    public function saveUpdateWeekDay($posted_data = array())
    {
        if (isset($posted_data['update_id'])) {
            $data = WeekDay::find($posted_data['update_id']);
        } else {
            $data = new WeekDay;
        }

        if (isset($posted_data['name'])) {
            $data->name = $posted_data['name'];
        }

        $data->save();
        return $data;
    }


    public function deleteWeekDay($id) {
        $data = WeekDay::find($id);
        if (isset($data->id) )
            return $data->delete();
        else 
            return false;
    }
}   