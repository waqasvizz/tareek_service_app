<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Crypt;

class UserStripeInformation extends Model
{
    use HasFactory;
    protected $table = 'user_stripe_informations';

    public function getSkLiveAttribute($value)
    {
        return  $value == null? $value:Crypt::decrypt($value);
    }

    public function setSkLiveAttribute($value)
    {
        $this->attributes['sk_live'] = Crypt::encrypt($value);
    }

    public function getPkLiveAttribute($value)
    {
        return  $value == null? $value:Crypt::decrypt($value);
    }

    public function setPkLiveAttribute($value)
    {
        $this->attributes['pk_live'] = Crypt::encrypt($value);
    }
    
    public function getSkTestAttribute($value)
    {
        return  $value == null? $value:Crypt::decrypt($value);
    }

    public function setSkTestAttribute($value)
    {
        $this->attributes['sk_test'] = Crypt::encrypt($value);
    }

    public function getPkTestAttribute($value)
    {
        return  $value == null? $value:Crypt::decrypt($value);
    }

    public function setPkTestAttribute($value)
    {
        $this->attributes['pk_test'] = Crypt::encrypt($value);
    }

    public function user()
    {
        return $this->belongsTo(User::class)->with('role');
    }

    public function getUserStripeInformation($posted_data = array())
    {
        $query = UserStripeInformation::latest()->with('user');

        if (isset($posted_data['id'])) {
            $query = $query->where('id', $posted_data['id']);
        }
        if (isset($posted_data['user_id'])) {
            $query = $query->where('user_id', $posted_data['user_id']);
        }
        if (isset($posted_data['stripe_mode'])) {
            $query = $query->where('stripe_mode', $posted_data['stripe_mode']);
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



    public function saveUpdateUserStripeInformation($posted_data = array())
    {
        if (isset($posted_data['update_id'])) {
            $data = UserStripeInformation::find($posted_data['update_id']);
        } else {
            $data = new UserStripeInformation;
        }

        if (isset($posted_data['user_id'])) {
            $data->user_id = $posted_data['user_id'];
        }
        if (isset($posted_data['stripe_mode'])) {
            $data->stripe_mode = $posted_data['stripe_mode'];
        }
        if (isset($posted_data['pk_test'])) {
            $data->pk_test = $posted_data['pk_test'];
        }
        if (isset($posted_data['sk_test'])) {
            $data->sk_test = $posted_data['sk_test'];
        }
        if (isset($posted_data['pk_live'])) {
            $data->pk_live = $posted_data['pk_live'];
        }
        if (isset($posted_data['sk_live'])) {
            $data->sk_live = $posted_data['sk_live'];
        }
        if (isset($posted_data['publishable_key'])) {
            $data->pk_live = $posted_data['publishable_key'];
        }
        if (isset($posted_data['secret_key'])) {
            $data->sk_live = $posted_data['secret_key'];
        }

        $data->save();
        return $data;
    }


    public function deleteUserStripeInformation($id) {
        $data = UserStripeInformation::find($id);
        if (isset($data->id) )
            return $data->delete();
        else 
            return false;
    }
}   