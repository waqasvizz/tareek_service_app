<?php

   /**
    *  @author  DANISH HUSSAIN <danishhussain9525@hotmail.com>
    *  @link    Author Website: https://danishhussain.w3spaces.com/
    *  @since   2020-03-01
   **/

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServicePromos extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table = 'promo_services';

    public function service()
    {
        return $this->belongsTo('App\Models\Service', 'service_id');
            // ->with('role')
            // ->select(['users.id', 'users.role_id', 'users.name', 'users.email', 'users.profile_image']);
    }

    // public function asset_category()
    // {
    //     return $this->belongsTo('App\Models\AssetType', 'asset_type')
    //         ->select(['id as asset_type_id', 'title', 'type', 'sides']);
    //         // ->where('id', 10);
    // }
    
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    
    public function getServicePromos($posted_data = array()) {
        
        $query = ServicePromos::latest()->with('service');

        if(isset($posted_data) && count($posted_data)>0){
            if(isset($posted_data['id'])){
                $query = $query->where('promo_services.id', $posted_data['id']);
            }
            if(isset($posted_data['service_id']) && $posted_data['service_id'] > 0){
                $query = $query->where('promo_services.service_id', $posted_data['service_id']);
            }
        }

        // $query->join('user_assets_categories', 'user_assets_categories.id', '=', 'user_assets.asset_type');
        // $query->select('user_assets.*', 'user_assets_categories.title', 'user_assets_categories.type', 'user_assets_categories.sides');
        
        $query->getQuery()->orders = null;
        if(isset($posted_data['orderBy_name'])){
            $query->orderBy($posted_data['orderBy_name'], $posted_data['orderBy_value']);
        }else{
            $query->orderBy('promo_services.id', 'DESC');
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
    

    public function saveUpdateServicePromos($posted_data = array()) {
        if(isset($posted_data['update_id'])){
            $data = ServicePromos::find($posted_data['update_id']);
        }else{
            $data = new ServicePromos;
        }

        if(isset($posted_data['service_id'])){
            $data->service_id = $posted_data['service_id'];
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
        $data = ServicePromos::getServicePromos(['id' => $data->id])->first();
        return $data;
    }

    public function deleteServicePromos($id=0) {
        $data = ServicePromos::find($id);

        if (isset($data->banner_path))
            delete_files_from_storage($data->banner_path);
        
        if ($data)
            return $data->delete();
        else
            return false;
    }
}