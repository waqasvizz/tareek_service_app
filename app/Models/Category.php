<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    // protected $fillable = [
    //     'user_id',
    //     'device_id',
    //     'device_token'
    // ];

    // db fields ==> id	service_id	customer_id	price	title	description	pay_with    status      created_at	updated_at	

    protected $table = 'categories';

    public function getCategories($posted_data = array())
    {
        $query = Category::latest();

        if (isset($posted_data['id'])) {
            $query = $query->where('categories.id', $posted_data['id']);
        }
        if (isset($posted_data['category_title'])) {
            $query = $query->where('categories.category_title', 'like', '%' . $posted_data['category_title'] . '%');
        }

        $query->select('categories.*');
        
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



    public function saveUpdateCategory($posted_data = array())
    {
        if (isset($posted_data['update_id'])) {
            $data = Category::find($posted_data['update_id']);
        } else {
            $data = new Category;
        }

        if (isset($posted_data['category_title'])) {
            $data->category_title = $posted_data['category_title'];
        }
        if (isset($posted_data['category_type'])) {
            $data->category_type = $posted_data['category_type'];
        }
        if (isset($posted_data['category_image'])) {
            $data->category_image = $posted_data['category_image'];
        }
        if (isset($posted_data['commission'])) {
            $data->commission = $posted_data['commission'];
        }

        $data->save();
        return $data;
    }


    public function deleteCategory($id) {
        $data = Category::find($id);
            
        if (isset($data->category_image))
            delete_files_from_storage($data->category_image);

        if ( isset($data->id) )
            return $data->delete();
        else 
            return false;
    }
}