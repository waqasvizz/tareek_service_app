<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Crypt;

class UserCard extends Model
{
    use HasFactory;

    public function getCardNameAttribute($value) {
        return $value == null ? $value : ucfirst($value);
    }

    public function setCardNameAttribute($value) {
        $this->attributes['card_name'] = $value;
    }

    public function getCardNumberAttribute($value) {
        if ($value != null || $value != "") {
            $value = Crypt::decrypt($value);
            return $value;
        }
        else return null;
    }

    public function setCardNumberAttribute($value) {
        $this->attributes['card_number'] = Crypt::encrypt($value);
    }

    public function getCvcNumberAttribute($value) {
        if ($value != null || $value != "") {
            $value = Crypt::decrypt($value);
            return $value;
        }
        else return null;
    }

    public function setCvcNumberAttribute($value) {
        $this->attributes['cvc_number'] = Crypt::encrypt($value);
    }

    public function getExpYearAttribute($value) {
        if ($value != null || $value != "") {
            $value = Crypt::decrypt($value);
            return $value;

            // if (UserCard::getMode() == "encrypted") $str_array = secureBankInfo($value, "year");
            // else $str_array = $value;
            // return $str_array;
        }
        else return null;
    }

    public function setExpYearAttribute($value) {
        $this->attributes['exp_year'] = Crypt::encrypt($value);
    }

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
            $query->orderBy('id', 'DESC');
        }

        if (isset($posted_data['paginate'])) {
            $result = $query->paginate($posted_data['paginate']);
        } else {
            if (isset($posted_data['detail'])) {
                $result = $query->first();
            } else if (isset($posted_data['count'])) {
                $result = $query->count();
            } else if (isset($posted_data['to_array'])) {
                $result = $query->get()->toArray();
            } else {
                $result = $query->get();
            }
        }

        if ( !(isset($posted_data['card_info']) && $posted_data['card_info'] == "decrypted") ) {
            if (array_key_exists('card_number', $result[0]))
                $result[0]['card_number'] = secureBankInfo($result[0]['card_number'], 'card');
            if (array_key_exists('cvc_number', $result[0]))
                $result[0]['cvc_number'] = secureBankInfo($result[0]['cvc_number'], 'cvc');
            if (array_key_exists('exp_year', $result[0]))
                $result[0]['exp_year'] = secureBankInfo($result[0]['exp_year'], 'year');
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
        $data = UserCard::getUserCard(['id' => $data->id, 'to_array' => true]);

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