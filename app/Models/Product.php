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

    public function getTotalOrdersAttribute()
    {
        return $this->hasMany(OrderProduct::class, 'product_id')->count();
    }

    public function getProducts($posted_data = array())
    {
        $columns = ['products.*'];
        $select_columns = array_merge($columns, []);

        $query = Product::latest();
        if (isset($posted_data['with_data']) && $posted_data['with_data']) {
            $query = $query->with('user')
                ->with('category');
        }

        if (isset($posted_data['id'])) {
            $query = $query->where('products.id', $posted_data['id']);
        }
        if (isset($posted_data['user_id'])) {
            $query = $query->where('products.user_id', $posted_data['user_id']);
        }
        if (isset($posted_data['product_type'])) {
            $query = $query->where('products.product_type', $posted_data['product_type']);
        }
        if (isset($posted_data['category_id'])) {
            $query = $query->where('products.category_id', $posted_data['category_id']);
        }
        if (isset($posted_data['product_name'])) {
            $query = $query->where('products.title', 'like', '%' . $posted_data['product_name'] . '%');
        }

        if(isset($posted_data['product_orders_join'])){
            $query->join('order_products', 'products.id', '=', 'order_products.product_id');
            $columns = ['order_products.price as order_price', 'order_products.id as order_id', 'order_products.prod_price as total_orders'];
            $select_columns = array_merge($select_columns, $columns);
        }

        if(isset($posted_data['orders_join'])){
            $query->join('orders', 'orders.id', '=', 'order_id');
            $columns = ['orders.id as orders_id', 'orders.sender_id as client_id', 'orders.order_status as order_status', 'orders.order_products as orders_order_products', 'orders.receiver_id as orders_receiver_id', 'orders.user_multiple_address_id as orders_user_multiple_address_id', 'orders.user_card_id as orders_user_card_id', 'orders.total as orders_total', 'orders.discount as orders_discount', 'orders.grand_total as orders_grand_total', 'orders.redeem_point as orders_redeem_point', 'orders.calculated as orders_calculated', 'orders.payment_status as orders_payment'];

            $select_columns = array_merge($select_columns, $columns);
        }

        $query->select($select_columns);

        if (isset($posted_data['groupBy_value'])) {
            $query->groupBy($posted_data['groupBy_value']);
        }
       
        $query->getQuery()->orders = null;
        if (isset($posted_data['orderBy_name'])) {
            $query->orderBy($posted_data['orderBy_name'], $posted_data['orderBy_value']);
        } else {
            $query->orderBy('products.id', 'DESC');
        }

        if (isset($posted_data['print_query'])) {
            $result = $query->toSql();
            echo "<pre>";
            print_r($result);
            echo "</pre>";
            exit("@@@@");
        }
        
        if (isset($posted_data['paginate'])) {
            $result = $query->paginate($posted_data['paginate']);
        }
        else {
            if (isset($posted_data['detail'])) {
                $result = $query->first();
            } else if (isset($posted_data['count'])) {
                $result = $query->count();
            } else if (isset($posted_data['to_array'])) {
                $result = $query->get()->ToArray();
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
        if (isset($posted_data['product_type'])) {
            $data->product_type = $posted_data['product_type'];
        }
        if (isset($posted_data['bulk_qty'])) {
            $data->bulk_qty = $posted_data['bulk_qty'];
        }
        if (isset($posted_data['min_qty'])) {
            $data->min_qty = $posted_data['min_qty'];
        }
        if (isset($posted_data['min_discount'])) {
            $data->min_discount = $posted_data['min_discount'];
        }
        if (isset($posted_data['max_qty'])) {
            $data->max_qty = $posted_data['max_qty'];
        }
        if (isset($posted_data['max_discount'])) {
            $data->max_discount = $posted_data['max_discount'];
        }
        if (isset($posted_data['time_limit'])) {
            $data->time_limit = $posted_data['time_limit'];
        }
        if (isset($posted_data['consume_qty'])) {
            $data->consume_qty = $posted_data['consume_qty'];
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