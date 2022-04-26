<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    
    public function getProductImageAttribute($value)
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

    public function getProducts($posted_data = array())
    {
        $query = Product::latest()->with('user')->with('category');

        if (isset($posted_data['id'])) {
            $query = $query->where('products.id', $posted_data['id']);
        }
        if (isset($posted_data['user_id'])) {
            $query = $query->where('products.user_id', $posted_data['user_id']);
        }
        if (isset($posted_data['category_id'])) {
            $query = $query->where('products.category_id', $posted_data['category_id']);
        }
        if (isset($posted_data['product_name'])) {
            $query = $query->where('products.title', 'like', '%' . $posted_data['product_name'] . '%');
        }

        $query->select('products.*');
        
        $query->getQuery()->orders = null;
        if (isset($posted_data['orderBy_name'])) {
            $query->orderBy($posted_data['orderBy_name'], $posted_data['orderBy_value']);
        } else {
            $query->orderBy('id', 'DESC');
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

    public function saveUpdateProduct($posted_data = array())
    {
        if (isset($posted_data['update_id'])) {
            $data = Product::find($posted_data['update_id']);
        } else {
            $data = new Product;
        }
        
        if (isset($posted_data['user_id'])) {
            $data->user_id = $posted_data['user_id'];
        }
        if (isset($posted_data['product_title'])) {
            $data->title = $posted_data['product_title'];
        }
        if (isset($posted_data['product_price'])) {
            $data->price = $posted_data['product_price'];
        }
        if (isset($posted_data['product_category'])) {
            $data->category_id = $posted_data['product_category'];
        }
        if (isset($posted_data['product_location'])) {
            $data->location = $posted_data['product_location'];
        }
        if (isset($posted_data['product_lat'])) {
            $data->lat = $posted_data['product_lat'];
        }
        if (isset($posted_data['product_long'])) {
            $data->long = $posted_data['product_long'];
        }
        if (isset($posted_data['product_description'])) {
            $data->description = $posted_data['product_description'];
        }
        if (isset($posted_data['product_contact'])) {
            $data->contact = $posted_data['product_contact'];
        }
        if (isset($posted_data['product_img'])) {
            $data->product_img = $posted_data['product_img'];
        }
        if (isset($posted_data['avg_rating'])) {
            $data->avg_rating = $posted_data['avg_rating'];
        }

        $data->save();
        return $data;
    }

    public function deleteProduct($id=0)
    {
        $data = Product::find($id);
        return $data->delete();
    }
}