<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CountriesMetadata extends Model
{
    protected $table = 'countries_metadatas';
    use HasFactory;

    public function getCountriesMetadata($posted_data = array())
    {
        $query = CountriesMetadata::latest();

        if (isset($posted_data['id'])) {
            $query = $query->where('id', $posted_data['id']);
        }
        if (isset($posted_data['iso_code'])) {
            $query = $query->where('iso_code', $posted_data['iso_code']);
        }
        if (isset($posted_data['name'])) {
            $query = $query->where('name', strtolower($posted_data['name']));
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
                $result = $query->toArray();
            } else {
                $result = $query->get();
            }
        }
        return $result;
    }



    public function saveUpdateCountriesMetadata($posted_data = array())
    {
        if (isset($posted_data['update_id'])) {
            $data = CountriesMetadata::find($posted_data['update_id']);
        } else {
            $data = new CountriesMetadata;
        }

        if (isset($posted_data['name'])) {
            $data->name = $posted_data['name'];
        }
        if (isset($posted_data['code'])) {
            $data->code = $posted_data['code'];
        }
        if (isset($posted_data['iso_code'])) {
            $data->iso_code = $posted_data['iso_code'];
        }
        if (isset($posted_data['state_required'])) {
            $data->state_required = $posted_data['state_required'];
        }
        if (isset($posted_data['postcode_required'])) {
            $data->postcode_required = $posted_data['postcode_required'];
        }
        if (isset($posted_data['intl_calling_number'])) {
            $data->intl_calling_number = $posted_data['intl_calling_number'];
        }

        $data->save();
        return $data;
    }


    public function deleteCountriesMetadata($id) {
        $data = CountriesMetadata::find($id);
        if (isset($data->id) )
            return $data->delete();
        else 
            return false;
    }
}   