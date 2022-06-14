<?php

   /**
    *  @author  DANISH HUSSAIN <danishhussain9525@hotmail.com>
    *  @link    Author Website: https://danishhussain.w3spaces.com/
    *  @since   2020-03-01
   **/

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailMessage extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table = 'email_messages';
    
    public function getEmailMessage($posted_data = array()) {
        
        $query = EmailMessage::latest();

        if(isset($posted_data) && count($posted_data)>0){
            if(isset($posted_data['id'])){
                $query = $query->where('email_messages.id', $posted_data['id']);
            }
            if(isset($posted_data['subject'])){
                $query = $query->where('email_messages.subject', 'like', '%' . $posted_data['subject'] . '%');
            }
            if(isset($posted_data['body'])){
                $query = $query->where('email_messages.body', 'like', '%' . $posted_data['body'] . '%');
            }
        }

        // $query->join('user_assets_categories', 'user_assets_categories.id', '=', 'user_assets.asset_type');
        // $query->select('user_assets.*', 'user_assets_categories.title', 'user_assets_categories.type', 'user_assets_categories.sides');
        
        $query->getQuery()->orders = null;
        if(isset($posted_data['orderBy_name'])){
            $query->orderBy($posted_data['orderBy_name'], $posted_data['orderBy_value']);
        }else{
            $query->orderBy('email_messages.id', 'DESC');
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
    

    public function saveUpdateEmailMessage($posted_data = array()) {
        if(isset($posted_data['update_id'])){
            $data = EmailMessage::find($posted_data['update_id']);
        }else{
            $data = new EmailMessage;
        }

        if(isset($posted_data['subject'])){
            $data->subject = $posted_data['subject'];
        }
        if(isset($posted_data['body'])){
            $data->body = $posted_data['body'];
        }

        $data->save();
        $data = EmailMessage::getEmailMessage(['id' => $data->id])->first();
        return $data;
    }

    public function deleteEmailMessage($id=0) {
        $data = EmailMessage::find($id);
        
        if ($data)
            return $data->delete();
        else
            return false;
    }
}