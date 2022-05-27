<?php

   /**
    *  @author  DANISH HUSSAIN <danishhussain9525@hotmail.com>
    *  @link    Author Website: https://danishhussain.w3spaces.com/
    *  @since   2020-03-01
   **/

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetType extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table = 'user_assets_categories';

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id')
            ->with('role')
            ->select(['users.id', 'users.role_id', 'users.name', 'users.email', 'users.profile_image']);
    }

    public function getAssetType($posted_data = array())
    {
        $query = AssetType::latest()
                    ->with('user');

        if (isset($posted_data['id'])) {
            $query = $query->where('user_assets_categories.id', $posted_data['id']);
        }
        if (isset($posted_data['type'])) {
            $query = $query->where('user_assets_categories.type', $posted_data['type']);
        }
        // if (isset($posted_data['category_title'])) {
        //     $query = $query->where('user_assets_categories.category_title', 'like', '%' . $posted_data['category_title'] . '%');
        // }

        if(isset($posted_data['user_id'])){
            $query->join('user_assets', 'user_assets.asset_type', '=', 'user_assets_categories.id');
            $query = $query->where('user_assets.user_id', $posted_data['user_id']);
            $query->groupBy('user_assets.asset_type');
            $query->select('user_assets_categories.*', 'user_assets.user_id', 'user_assets.asset_type');
        }
        
        $query->getQuery()->orders = null;
        if (isset($posted_data['orderBy_name'])) {
            $query->orderBy($posted_data['orderBy_name'], $posted_data['orderBy_value']);
        } else {
            if(isset($posted_data['user_id']))
                $query->orderBy('user_assets.id', 'desc');
            else
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

    public function saveUpdateAssetType($posted_data = array())
    {
        if (isset($posted_data['update_id'])) {
            $data = AssetType::find($posted_data['update_id']);
        } else {
            $data = new AssetType;
        }

        if (isset($posted_data['title'])) {
            $data->title = $posted_data['title'];
        }
        if (isset($posted_data['type'])) {
            $data->type = $posted_data['type'];
        }
        if (isset($posted_data['sides'])) {
            $data->sides = $posted_data['sides'];
        }

        $data->save();
        $data = AssetType::getAssetType(['id' => $data->id])->first();
        return $data;
    }


    public function deleteAssetType($id) {
        $data = AssetType::find($id);

        if ( isset($data->id) )
            return $data->delete();
        else 
            return false;
    }
}