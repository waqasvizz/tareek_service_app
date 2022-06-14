<?php

   /**
    *  @author  DANISH HUSSAIN <danishhussain9525@hotmail.com>
    *  @link    Author Website: https://danishhussain.w3spaces.com/
    *  @since   2020-03-01
   **/

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClearenceService extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table = 'clearence_services';

    public function user_asset_data()
    {
        return $this->belongsTo(UserAssets::class, 'user_asset_id');
    }
    
    public function getClearenceService($posted_data = array()) {
        
        $query = ClearenceService::latest()->with('user_asset_data');

        if(isset($posted_data) && count($posted_data)>0){
            if(isset($posted_data['id'])){
                $query = $query->where('clearence_services.id', $posted_data['id']);
            }
            if(isset($posted_data['order_id'])){
                $query = $query->where('clearence_services.order_id', $posted_data['order_id']);
            }
            if(isset($posted_data['user_asset_id'])){
                $query = $query->where('clearence_services.user_asset_id', $posted_data['user_asset_id']);
            }
        }

        // $query->join('user_assets_categories', 'user_assets_categories.id', '=', 'user_assets.asset_type');
        // $query->select('user_assets.*', 'user_assets_categories.title', 'user_assets_categories.type', 'user_assets_categories.sides');
        
        $query->getQuery()->orders = null;
        if(isset($posted_data['orderBy_name'])){
            $query->orderBy($posted_data['orderBy_name'], $posted_data['orderBy_value']);
        }else{
            $query->orderBy('clearence_services.id', 'DESC');
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
    

    public function saveUpdateClearenceService($posted_data = array()) {
        if(isset($posted_data['update_id'])){
            $data = ClearenceService::find($posted_data['update_id']);
        }else{
            $data = new ClearenceService;
        }

        if(isset($posted_data['order_id'])){
            $data->order_id = $posted_data['order_id'];
        }
        if(isset($posted_data['user_asset_id'])){
            $data->user_asset_id = $posted_data['user_asset_id'];
        }

        $data->save();
        $data = ClearenceService::getClearenceService(['id' => $data->id])->first();
        return $data;
    }

    public function deleteClearenceService($id=0) {
        $data = ClearenceService::find($id);
        
        if ($data)
            return $data->delete();
        else
            return false;
    }
}