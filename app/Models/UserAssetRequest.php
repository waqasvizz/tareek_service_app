<?php

   /**
    *  @author  DANISH HUSSAIN <danishhussain9525@hotmail.com>
    *  @link    Author Website: https://danishhussain.w3spaces.com/
    *  @since   2020-03-01
   **/

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAssetRequest extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table = 'user_assets_requests';

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id')
            ->with('role')
            ->select(['users.id', 'users.role_id', 'users.name', 'users.email', 'users.profile_image']);
    }

    public function requested_user()
    {
        return $this->belongsTo('App\Models\User', 'request_by')
            ->with('role')
            ->select(['users.id', 'users.role_id', 'users.name', 'users.email', 'users.profile_image']);
    }
    
    public function getUserAssetRequest($posted_data = array()) {
        
        $query = UserAssetRequest::latest()->with('user')->with('requested_user');

        if(isset($posted_data) && count($posted_data)>0){
            if(isset($posted_data['id'])){
                $query = $query->where('user_assets_requests.id', $posted_data['id']);
            }
            if(isset($posted_data['user_id'])){
                $query = $query->where('user_assets_requests.user_id', $posted_data['user_id']);
            }
            if(isset($posted_data['request_by'])){
                $query = $query->where('user_assets_requests.request_by', $posted_data['request_by']);
            }
            if(isset($posted_data['request_status'])){
                if ($posted_data['request_status'] == 1) $posted_data['status'] = 'Pending';
                else if ($posted_data['request_status'] == 2) $posted_data['status'] = 'Accept';
                else if ($posted_data['request_status'] == 3) $posted_data['status'] = 'Reject';
                unset($posted_data['request_status']);
                $query = $query->where('user_assets_requests.status', $posted_data['status']);
            }
        }

        // echo "Line no awwww@"."<br>";
        // echo "<pre>";
        // print_r($posted_data);
        // echo "</pre>";
        // exit("@@@@");
        
        // $query->join('fileponds', 'fileponds.id', '=', 'post_assets.filepond_id');
        // $query->select('post_assets.*', 'fileponds.filename', 'fileponds.filepath');
        
        $query->getQuery()->orders = null;
        if(isset($posted_data['orderBy_name'])){
            $query->orderBy($posted_data['orderBy_name'], $posted_data['orderBy_value']);
        }else{
            $query->orderBy('id', 'DESC');
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
    

    public function saveUpdateUserAssetRequest($posted_data = array()) {

        if(isset($posted_data['update_id'])){
            $data = UserAssetRequest::find($posted_data['update_id']);
        }else{
            $data = new UserAssetRequest;
        }

        if(isset($posted_data['user_id'])){
            $data->user_id = $posted_data['user_id'];
        }
        if(isset($posted_data['request_by'])){
            $data->request_by = $posted_data['request_by'];
        }
        if(isset($posted_data['status'])){
            if ($posted_data['status'] == 1) $posted_data['status'] = 'Pending';
            else if ($posted_data['status'] == 2) $posted_data['status'] = 'Accept';
            else if ($posted_data['status'] == 3) $posted_data['status'] = 'Reject';
            $data->status = $posted_data['status'];
        }

        $data->save();
        $data = UserAssetRequest::getUserAssetRequest(['id' => $data->id])->first();
        return $data;
    }

    public function deleteUserAssetRequest($id=0) {
        $data = UserAssetRequest::find($id);
        
        if ($data)
            return $data->delete();
        else
            return false;
    }
}