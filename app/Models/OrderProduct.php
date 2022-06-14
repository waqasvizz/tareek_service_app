<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderProduct extends Model
{
    use HasFactory;

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class)->with('category');
    }

    public function getOrderProduct($posted_data = array())
    {
        $query = OrderProduct::latest()->with('order')->with('product');

        if (isset($posted_data['id'])) {
            $query = $query->where('id', $posted_data['id']);
        }
        if (isset($posted_data['product_id'])) {
            $query = $query->where('product_id', $posted_data['product_id']);
        }
        if (isset($posted_data['order_id'])) {
            $query = $query->where('order_id', $posted_data['order_id']);
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



    public function saveUpdateOrderProduct($posted_data = array())
    {
        if (isset($posted_data['update_id'])) {
            $data = OrderProduct::find($posted_data['update_id']);
        } else {
            $data = new OrderProduct;
        }

        if (isset($posted_data['order_id'])) {
            $data->order_id = $posted_data['order_id'];
        }
        if (isset($posted_data['product_id'])) {
            $data->product_id = $posted_data['product_id'];
        }
        if (isset($posted_data['quantity'])) {
            $data->quantity = $posted_data['quantity'];
        }
        if (isset($posted_data['price'])) {
            $data->price = $posted_data['price'];
        }
        if (isset($posted_data['discount'])) {
            $data->discount = $posted_data['discount'];
        }
        if (isset($posted_data['net_price'])) {
            $data->net_price = $posted_data['net_price'];
        }
        if (isset($posted_data['calculated'])) {
            $data->calculated = $posted_data['calculated'];
        }
        if (isset($posted_data['admin_earn'])) {
            $data->admin_earn = $posted_data['admin_earn'];
        }
        if (isset($posted_data['supplier_earn'])) {
            $data->supplier_earn = $posted_data['supplier_earn'];
        }

        $data->save();
        return $data;
    }


    public function deleteOrderProduct($id) {
        $data = OrderProduct::find($id);
        if (isset($data->id) )
            return $data->delete();
        else 
            return false;
    }
}   