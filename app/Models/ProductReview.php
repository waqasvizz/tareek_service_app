<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductReview extends Model
{
    use HasFactory;

    public function getProductReview($posted_data = array())
    {
        $query = ProductReview::latest();

        if (isset($posted_data['id'])) {
            $query = $query->where('id', $posted_data['id']);
        }
        if (isset($posted_data['product_id'])) {
            $query = $query->where('product_id', $posted_data['product_id']);
        }
        if (isset($posted_data['order_id'])) {
            $query = $query->where('order_id', $posted_data['order_id']);
        }
        if (isset($posted_data['sender_id'])) {
            $query = $query->where('sender_id', $posted_data['sender_id']);
        }
        if (isset($posted_data['receiver_id'])) {
            $query = $query->where('receiver_id', $posted_data['receiver_id']);
        }

        $query->select('*');
        
        $query->getQuery()->orders = null;
        if (isset($posted_data['orderBy_name'])) {
            $query->orderBy($posted_data['orderBy_name'], $posted_data['orderBy_value']);
        } else {
            $query->orderBy('id', 'desc');
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



    public function saveUpdateProductReview($posted_data = array())
    {
        if (isset($posted_data['update_id'])) {
            $data = ProductReview::find($posted_data['update_id']);
        } else {
            $data = new ProductReview;
        }

        if (isset($posted_data['product_id'])) {
            $data->product_id = $posted_data['product_id'];
        }
        if (isset($posted_data['order_id'])) {
            $data->order_id = $posted_data['order_id'];
        }
        if (isset($posted_data['sender_id'])) {
            $data->sender_id = $posted_data['sender_id'];
        }
        if (isset($posted_data['receiver_id'])) {
            $data->receiver_id = $posted_data['receiver_id'];
        }
        if (isset($posted_data['stars'])) {
            $data->stars = $posted_data['stars'];
        }
        if (isset($posted_data['description'])) {
            $data->description = $posted_data['description'];
        }

        $data->save();
        return $data;
    }


    public function deleteProductReview($id) {
        $data = ProductReview::find($id);
        if (isset($data->id) )
            return $data->delete();
        else 
            return false;
    }
}   