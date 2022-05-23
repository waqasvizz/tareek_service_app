<?php

   /**
    *  @author  DANISH HUSSAIN <danishhussain9525@hotmail.com>
    *  @link    Author Website: https://danishhussain.w3spaces.com/
    *  @since   2020-03-01
   **/

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAssets extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table = 'user_assets';

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id')
            ->with('role')
            ->select(['users.id', 'users.role_id', 'users.name', 'users.email', 'users.profile_image']);
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

    
    public function getUserAssets($posted_data = array()) {
        
        $query = UserAssets::latest()->with('user')->with('asset_category');

        if(isset($posted_data) && count($posted_data)>0){
            if(isset($posted_data['id'])){
                $query = $query->where('user_assets.id', $posted_data['id']);
            }
            if(isset($posted_data['user_id'])){
                $query = $query->where('user_assets.user_id', $posted_data['user_id']);
            }
            if(isset($posted_data['asset_type'])){
                $query = $query->where('user_assets.asset_type', $posted_data['asset_type']);
            }
            if(isset($posted_data['mimetypes'])){
                $query = $query->where('user_assets.mimetypes', $posted_data['mimetypes']);
            }
            if(isset($posted_data['asset_status'])){
                $query = $query->where('user_assets.asset_status', $posted_data['asset_status']);
            }
        }

        $query->join('user_assets_categories', 'user_assets_categories.id', '=', 'user_assets.asset_type');
        $query->select('user_assets.*', 'user_assets_categories.title', 'user_assets_categories.type', 'user_assets_categories.sides');
        
        $query->getQuery()->orders = null;
        if(isset($posted_data['orderBy_name'])){
            $query->orderBy($posted_data['orderBy_name'], $posted_data['orderBy_value']);
        }else{
            $query->orderBy('user_assets.id', 'DESC');
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
    

    public function saveUpdateUserAssets($posted_data = array()) {
        if(isset($posted_data['update_id'])){
            $data = UserAssets::find($posted_data['update_id']);
        }else{
            $data = new UserAssets;
        }

        if(isset($posted_data['user_id'])){
            $data->user_id = $posted_data['user_id'];
        }
        if(isset($posted_data['asset_type'])){
            $data->asset_type = $posted_data['asset_type'];
        }
        if(isset($posted_data['filename'])){
            $data->filename = $posted_data['filename'];
        }
        if(isset($posted_data['filepath'])){
            $data->filepath = $posted_data['filepath'];
        }
        if(isset($posted_data['mimetypes'])){
            $data->mimetypes = $posted_data['mimetypes'];
        }
        if(isset($posted_data['asset_status'])){
            $data->asset_status = $posted_data['asset_status'];
        }
        if(isset($posted_data['asset_view'])){
            $data->asset_view = $posted_data['asset_view'];
        }

        $data->save();
        $data = UserAssets::getUserAssets(['id' => $data->id])->first();
        return $data;
    }

    public function deleteUserAssets($id=0) {
        $data = UserAssets::find($id);

        if (isset($data->filepath))
            delete_files_from_storage($data->filepath);
        
        if ($data)
            return $data->delete();
        else
            return false;
    }
}