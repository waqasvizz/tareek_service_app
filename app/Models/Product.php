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
        if (isset($posted_data['bulk_qty'])) {
            $query = $query->where('products.bulk_qty', $posted_data['bulk_qty']);
        }
        if (isset($posted_data['consume_qty'])) {
            $query = $query->where('products.consume_qty', $posted_data['consume_qty']);
        }
        if (isset($posted_data['min_qty'])) {
            $query = $query->where('products.min_qty', $posted_data['min_qty']);
        }
        if (isset($posted_data['max_qty'])) {
            $query = $query->where('products.max_qty', $posted_data['max_qty']);
        }
        if (isset($posted_data['min_disc_qualify'])) {
            $query = $query->whereColumn('products.consume_qty', '>=', 'products.min_qty');
        }
        if (isset($posted_data['max_disc_qualify'])) {
            $query = $query->whereColumn('products.consume_qty', '>=', 'products.max_qty');
        }
        if (isset($posted_data['category_id'])) {
            $query = $query->where('products.category_id', $posted_data['category_id']);
        }
        if (isset($posted_data['within_time_limit'])) {
            $query = $query->where('products.time_limit', '>=', $posted_data['within_time_limit']);
        }
        if (isset($posted_data['product_name'])) {
            $query = $query->where('products.title', 'like', '%' . $posted_data['product_name'] . '%');
        }

        if(isset($posted_data['product_orders_join'])){
            $query->join('order_products', 'products.id', '=', 'order_products.product_id');
            
            $columns = ['order_products.price as order_price', 'order_products.id as order_id', 'order_products.prod_price as total_orders'];

            if (isset($posted_data['product_orders_join_all_columns'])) {
                $columns = ['order_products.id as order_prod_id', 'order_products.product_id as order_prod_product_id', 'order_products.quantity as order_prod_quantity', 'order_products.price as order_prod_price', 'order_products.prod_price as order_prod_prod_price', 'order_products.discount as order_prod_discount', 'order_products.admin_earn as order_prod_admin_earn', 'order_products.supplier_earn as order_prod_supplier_earn', 'order_products.adm_aftr_reedem as order_prod_adm_aftr_reedem', 'order_products.sup_aftr_reedem as order_prod_sup_aftr_reedem', 'order_products.adm_aftr_disc as order_prod_adm_aftr_disc', 'order_products.sup_aftr_disc as order_prod_sup_aftr_disc', 'order_products.total_admin as order_prod_total_admin', 'order_products.total_supplier as order_prod_total_supplier', 'order_products.prod_disc as order_prod_prod_disc', 'order_products.reedem_disc as order_prod_reedem_disc', 'order_products.calculated as order_prod_calculated'];
            }

            if (isset($posted_data['prod_disc'])) {
                $query = $query->where('order_products.prod_disc', $posted_data['prod_disc']);
            }
            if (isset($posted_data['order_id'])) {
                $query = $query->where('order_products.order_id', $posted_data['order_id']);
            }
            if (isset($posted_data['product_id'])) {
                $query = $query->where('order_products.product_id', $posted_data['product_id']);
            }
            $select_columns = array_merge($select_columns, $columns);
        }

        if(isset($posted_data['orders_join'])){
            $query->join('orders', 'orders.id', '=', 'order_id');
            $columns = ['orders.id as orders_id', 'orders.order_status as order_status', 'orders.order_products as orders_order_products', 'orders.calculated as orders_calculated', 'orders.payment_status as orders_payment', 'orders.sender_id as client_id', 'orders.receiver_id as orders_receiver_id', 'orders.user_multiple_address_id as orders_user_multiple_address_id', 'orders.user_delivery_option_id as orders_user_delivery_option_id', 'orders.user_card_id as orders_user_card_id', 'orders.total as orders_total', 'orders.shipping_cost as orders_shipping_cost', 'orders.discount_redeem as orders_discount_redeem', 'orders.discount_bulk as orders_discount_bulk', 'orders.grand_total as orders_grand_total', 'orders.admin_avg as orders_admin_avg', 'orders.supplier_avg as orders_supplier_avg', 'orders.redeem_point as orders_redeem_point'];

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
            echo "<br>";
            print_r($posted_data);
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
        if (isset($posted_data['product_weight'])) {
            $data->weight = $posted_data['product_weight'];
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