<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;
    
    public function getServiceImageAttribute($value)
    {
        return $value;
        // return url('/')."/".$value;
        // return public_path()."/".$value;
    }
    
    public function user()
    {
        return $this->belongsTo('App\Models\User')->with('role');
    }
    
    public function category()
    {
        return $this->belongsTo('App\Models\Category','category_id');
    }
    
    public function UserWeekDays()
    {
        return $this->hasMany('App\Models\UserWeekDay')->with('WeekDay');
    }

    public function getServices($posted_data = array())
    {
        $query = Service::latest()->with('user')->with('category')->with('UserWeekDays');

        if (isset($posted_data['id'])) {
            $query = $query->where('services.id', $posted_data['id']);
        }
        if (isset($posted_data['service_name'])) {
            $query = $query->where('services.title', 'like', '%' . $posted_data['service_name'] . '%');
        }

        $query->select('services.*');
        
        $query->getQuery()->orders = null;
        if (isset($posted_data['orderBy_name'])) {
            $query->orderBy($posted_data['orderBy_name'], $posted_data['orderBy_value']);
        } else {
            $query->orderBy('id', 'ASC');
        }
        
        if (isset($posted_data['paginate'])) {
            $result = $query->paginate($posted_data['paginate']);
        }
        else {
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

    public function saveUpdateService($posted_data = array())
    {
        if (isset($posted_data['update_id'])) {
            $data = Service::find($posted_data['update_id']);
        } else {
            $data = new Service;
        }

        if (isset($posted_data['user_id'])) {
            $data->user_id = $posted_data['user_id'];
        }
        if (isset($posted_data['service_title'])) {
            $data->title = $posted_data['service_title'];
        }
        if (isset($posted_data['service_price'])) {
            $data->price = $posted_data['service_price'];
        }
        if (isset($posted_data['service_category'])) {
            $data->category_id = $posted_data['service_category'];
        }
        if (isset($posted_data['service_location'])) {
            $data->location = $posted_data['service_location'];
        }
        if (isset($posted_data['service_lat'])) {
            $data->lat = $posted_data['service_lat'];
        }
        if (isset($posted_data['service_long'])) {
            $data->long = $posted_data['service_long'];
        }
        if (isset($posted_data['service_description'])) {
            $data->description = $posted_data['service_description'];
        }
        if (isset($posted_data['service_contact'])) {
            $data->contact = $posted_data['service_contact'];
        }
        if (isset($posted_data['service_img'])) {
            $data->service_img = $posted_data['service_img'];
        }
        if (isset($posted_data['avg_rating'])) {
            $data->avg_rating = $posted_data['avg_rating'];
        }

        $data->save();
        // $data = Service::getServices([
        //     'id' => $data->id,
        //     'detail' => true
        // ]);
        return $data;
    }

    public function deleteService($id=0)
    {
        $data = Service::find($id);
        return $data->delete();
    }
}