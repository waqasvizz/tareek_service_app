<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserWeekDay extends Model
{
    use HasFactory;


    public function Service(){
        return $this->belongsTo(Service::Class);
    }

    public function WeekDay(){
        return $this->belongsTo(WeekDay::Class);
    }
    
    public function getUserWeekDay($posted_data = array())
    {
        $query = UserWeekDay::latest()->with('Service')->with('WeekDay');

        if (isset($posted_data['id'])) {
            $query = $query->where('id', $posted_data['id']);
        }

        if (isset($posted_data['service_id'])) {
            $query = $query->where('service_id', $posted_data['service_id']);
        }

        if (isset($posted_data['week_day_id'])) {
            $query = $query->where('week_day_id', $posted_data['week_day_id']);
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



    public function saveUpdateUserWeekDay($posted_data = array())
    {
        if (isset($posted_data['update_id'])) {
            $data = UserWeekDay::find($posted_data['update_id']);
        } else {
            $data = new UserWeekDay;
        }

        if (isset($posted_data['service_id'])) {
            $data->service_id = $posted_data['service_id'];
        }

        if (isset($posted_data['week_day_id'])) {
            $data->week_day_id = $posted_data['week_day_id'];
        }

        if (isset($posted_data['start_time'])) {
            $data->start_time = $posted_data['start_time'];
        }

        if (isset($posted_data['end_time'])) {
            $data->end_time = $posted_data['end_time'];
        }

        $data->save();
        return $data;
    }


    public function deleteUserWeekDay($id) {
        $data = UserWeekDay::find($id);
        if (isset($data->id) )
            return $data->delete();
        else 
            return false;
    }
}