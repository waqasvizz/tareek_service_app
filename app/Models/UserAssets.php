<?php

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

    // protected $fillable = [
    //     'user_id',
    //     'device_id',
    //     'device_token'
    // ];

    // db fields ==> id	post_id	filepond_id	asset_type	created_at	updated_at

    protected $table = 'user_assets';


    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    
    public function getUserAssets($posted_data = array()) {
        
        $query = UserAssets::latest();

        if(isset($posted_data) && count($posted_data)>0){
            if(isset($posted_data['id'])){
                $query = $query->where('post_assets.id', $posted_data['id']);
            }
            if(isset($posted_data['post_id'])){
                $query = $query->where('post_assets.post_id', $posted_data['post_id']);
            }
            if(isset($posted_data['filepond_id'])){
                $query = $query->where('post_assets.filepond_id', $posted_data['filepond_id']);
            }
            if(isset($posted_data['asset_type'])){
                $query = $query->where('post_assets.asset_type', $posted_data['asset_type']);
            }
        }
        
        $query->join('fileponds', 'fileponds.id', '=', 'post_assets.filepond_id');
        $query->select('post_assets.*', 'fileponds.filename', 'fileponds.filepath');
        
        // $query->getQuery()->orders = null;
        // if(isset($posted_data['orderBy_name'])){
        //     $query->orderBy($posted_data['orderBy_name'], $posted_data['orderBy_value']);
        // }else{
        //     $query->orderBy('id', 'DESC');
        // }

        
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
        if(isset($posted_data['field_name'])){
            $data->field_name = $posted_data['field_name'];
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
        $data->save();
        return $data->id;
    }

    public function deleteUserAssets($id=0) {
        $data = UserAssets::find($id);
        return $data->delete();
    }
}