<?php

   /**
    *  @author  DANISH HUSSAIN <danishhussain9525@hotmail.com>
    *  @link    Author Website: https://danishhussain.w3spaces.com/
    *  @since   2020-03-01
   **/

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShortCodes extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table = 'short_codes';
    
    public function getShortCodes($posted_data = array()) {
        
        $query = ShortCodes::latest();

        if(isset($posted_data) && count($posted_data)>0){
            if(isset($posted_data['id'])){
                $query = $query->where('short_codes.id', $posted_data['id']);
            }
            if(isset($posted_data['title'])){
                $query = $query->where('short_codes.title', 'like', '%' . $posted_data['title'] . '%');
            }
        }

        // $query->join('user_assets_categories', 'user_assets_categories.id', '=', 'user_assets.asset_type');
        // $query->select('user_assets.*', 'user_assets_categories.title', 'user_assets_categories.type', 'user_assets_categories.sides');
        
        $query->getQuery()->orders = null;
        if(isset($posted_data['orderBy_name'])){
            $query->orderBy($posted_data['orderBy_name'], $posted_data['orderBy_value']);
        }else{
            $query->orderBy('short_codes.id', 'DESC');
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
    

    public function saveUpdateShortCodes($posted_data = array()) {
        if(isset($posted_data['update_id'])){
            $data = ShortCodes::find($posted_data['update_id']);
        }else{
            $data = new ShortCodes;
        }

        if(isset($posted_data['title'])){
            $data->title = $posted_data['title'];
        }

        $data->save();
        $data = ShortCodes::getShortCodes(['id' => $data->id])->first();
        return $data;
    }

    public function deleteShortCodes($id=0) {
        $data = ShortCodes::find($id);
        
        if ($data)
            return $data->delete();
        else
            return false;
    }
}