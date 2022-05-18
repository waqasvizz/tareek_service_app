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

    public function getAssetType($posted_data = array())
    {
        $query = AssetType::latest();

        if (isset($posted_data['id'])) {
            $query = $query->where('user_assets_categories.id', $posted_data['id']);
        }
        if (isset($posted_data['type'])) {
            $query = $query->where('user_assets_categories.type', $posted_data['type']);
        }
        // if (isset($posted_data['category_title'])) {
        //     $query = $query->where('user_assets_categories.category_title', 'like', '%' . $posted_data['category_title'] . '%');
        // }

        $query->select('user_assets_categories.*');
        
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
        return $data;
    }


    public function deleteAssetType($id) {
        $data = AssetType::find($id);
            
        if (isset($data->category_image))
            delete_files_from_storage($data->category_image);

        if ( isset($data->id) )
            return $data->delete();
        else 
            return false;
    }
}