<?php

   /**
    *  @author  DANISH HUSSAIN <danishhussain9525@hotmail.com>
    *  @link    Author Website: https://danishhussain.w3spaces.com/
    *  @since   2020-03-01
   **/

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductPromos extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table = 'promo_products';

    public function product()
    {
        return $this->belongsTo('App\Models\Product', 'product_id');
            // ->with('role')
            // ->select(['users.id', 'users.role_id', 'users.name', 'users.email', 'users.profile_image']);
    }

    public function asset_category()
    {
        return $this->belongsTo('App\Models\AssetType', 'asset_type')
            ->select(['id as asset_type_id', 'title', 'type', 'sides']);
            // ->where('id', 10);
    }
    
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    
    public function getProductPromos($posted_data = array()) {
        
        $query = ProductPromos::latest()->with('product');

        if(isset($posted_data) && count($posted_data)>0){
            if(isset($posted_data['id'])){
                $query = $query->where('promo_products.id', $posted_data['id']);
            }
            if(isset($posted_data['user_id'])){
                $query = $query->where('promo_products.user_id', $posted_data['user_id']);
            }
            if(isset($posted_data['asset_type'])){
                $query = $query->where('promo_products.asset_type', $posted_data['asset_type']);
            }
            if(isset($posted_data['mimetypes'])){
                $query = $query->where('promo_products.mimetypes', $posted_data['mimetypes']);
            }
            if(isset($posted_data['asset_status'])){
                $query = $query->where('promo_products.asset_status', $posted_data['asset_status']);
            }
        }

        // $query->join('user_assets_categories', 'user_assets_categories.id', '=', 'user_assets.asset_type');
        // $query->select('user_assets.*', 'user_assets_categories.title', 'user_assets_categories.type', 'user_assets_categories.sides');
        
        $query->getQuery()->orders = null;
        if(isset($posted_data['orderBy_name'])){
            $query->orderBy($posted_data['orderBy_name'], $posted_data['orderBy_value']);
        }else{
            $query->orderBy('promo_products.id', 'DESC');
        }

        
        if(isset($posted_data['paginate'])){
            $result = $query->paginate($posted_data['paginate']);
        }else{
            if(isset($posted_data['detail'])){
                $result = $query->first();
            }else{
                $result = $query->get();
            }
            // $result = $query->toSql();
        }
        
        return $result;
    }
    

    public function saveUpdateProductPromos($posted_data = array()) {
        if(isset($posted_data['update_id'])){
            $data = ProductPromos::find($posted_data['update_id']);
        }else{
            $data = new ProductPromos;
        }

        if(isset($posted_data['product_id'])){
            $data->product_id = $posted_data['product_id'];
        }
        if(isset($posted_data['title'])){
            $data->title = $posted_data['title'];
        }
        if(isset($posted_data['banner'])){
            $data->banner = $posted_data['banner'];
        }
        if(isset($posted_data['banner_path'])){
            $data->banner_path = $posted_data['banner_path'];
        }
        if(isset($posted_data['description'])){
            $data->description = $posted_data['description'];
        }

        $data->save();
        $data = ProductPromos::getProductPromos(['id' => $data->id])->first();
        return $data;
    }

    public function deleteProductPromos($id=0) {
        $data = ProductPromos::find($id);

        if (isset($data->filepath))
            delete_files_from_storage($data->filepath);
        
        if ($data)
            return $data->delete();
        else
            return false;
    }
}