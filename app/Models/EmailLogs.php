<?php

   /**
    *  @author  DANISH HUSSAIN <danishhussain9525@hotmail.com>
    *  @link    Author Website: https://danishhussain.w3spaces.com/
    *  @since   2020-03-01
   **/

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailLogs extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table = 'email_logs';
    
    public function getEmailLogs($posted_data = array()) {
        
        $query = EmailLogs::latest();

        if(isset($posted_data) && count($posted_data)>0){
            if(isset($posted_data['email_msg_id'])){
                $query = $query->where('email_logs.email_msg_id', $posted_data['email_msg_id']);
            }
            if(isset($posted_data['sender_id'])){
                $query = $query->where('email_logs.sender_id', $posted_data['sender_id']);
            }
            if(isset($posted_data['receiver_id'])){
                $query = $query->where('email_logs.receiver_id', $posted_data['receiver_id']);
            }
            if(isset($posted_data['email'])){
                $query = $query->where('email_logs.email', 'like', '%' . $posted_data['email'] . '%');
            }
            if(isset($posted_data['subject'])){
                $query = $query->where('email_logs.subject', 'like', '%' . $posted_data['subject'] . '%');
            }
            if(isset($posted_data['send_email_after'])){
                $query = $query->where('email_logs.send_email_after', $posted_data['send_email_after']);
            }
            if(isset($posted_data['send_at'])){
                $query = $query->where('email_logs.send_at', $posted_data['send_at']);
            }
            if(isset($posted_data['stop_at'])){
                $query = $query->where('email_logs.stop_at', $posted_data['stop_at']);
            }
            if(isset($posted_data['status'])){
                if($posted_data['status'] == 1) $posted_data['status'] = 'Pending';
                else if($posted_data['status'] == 2) $posted_data['status'] = 'Sent';
                else if($posted_data['status'] == 3) $posted_data['status'] = 'Stop';
                else if($posted_data['status'] == 4) $posted_data['status'] = 'Failed';
                $query = $query->where('email_logs.status', $posted_data['status']);
            }
            if(isset($posted_data['status_message'])){
                $query = $query->where('email_logs.status_message', 'like', '%' . $posted_data['status_message'] . '%');
            }
        }

        // $query->join('user_assets_categories', 'user_assets_categories.id', '=', 'user_assets.asset_type');
        // $query->select('user_assets.*', 'user_assets_categories.title', 'user_assets_categories.type', 'user_assets_categories.sides');
        
        $query->getQuery()->orders = null;
        if(isset($posted_data['orderBy_name'])){
            $query->orderBy($posted_data['orderBy_name'], $posted_data['orderBy_value']);
        }else{
            $query->orderBy('email_logs.id', 'DESC');
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
    

    public function saveUpdateEmailLogs($posted_data = array()) {
        if(isset($posted_data['update_id'])){
            $data = EmailLogs::find($posted_data['update_id']);
        }else{
            $data = new EmailLogs;
        }

        if(isset($posted_data['email_msg_id'])){
            $data->email_msg_id = $posted_data['email_msg_id'];
        }
        if(isset($posted_data['sender_id'])){
            $data->sender_id = $posted_data['sender_id'];
        }
        if(isset($posted_data['receiver_id'])){
            $data->receiver_id = $posted_data['receiver_id'];
        }
        if(isset($posted_data['email'])){
            $data->email = $posted_data['email'];
        }
        if(isset($posted_data['subject'])){
            $data->subject = $posted_data['subject'];
        }
        if(isset($posted_data['email_message'])){
            $data->email_message = $posted_data['email_message'];
        }
        if(isset($posted_data['send_email_after'])){
            $data->send_email_after = $posted_data['send_email_after'];
        }
        if(isset($posted_data['send_at'])){
            $data->send_at = $posted_data['send_at'];
        }
        if(isset($posted_data['stop_at'])){
            $data->stop_at = $posted_data['stop_at'];
        }
        if(isset($posted_data['status'])){
            $data->status = $posted_data['status'];
        }
        if(isset($posted_data['status_message'])){
            $data->status_message = $posted_data['status_message'];
        }

        $data->save();
        $data = EmailLogs::getEmailLogs(['id' => $data->id])->first();
        return $data;
    }

    public function deleteEmailLogs($id=0) {
        $data = EmailLogs::find($id);
        
        if ($data)
            return $data->delete();
        else
            return false;
    }
}