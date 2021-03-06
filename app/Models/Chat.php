<?php

   /**
    *  @author  DANISH HUSSAIN <danishhussain9525@hotmail.com>
    *  @link    Author Website: https://danishhussain.w3spaces.com/
    *  @since   2020-03-01
   **/

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class Chat extends Model
{
    use HasFactory;

    public function senderDetails()
    {
        return $this->belongsTo('App\Models\User', 'sender_id')
            ->with('role')
            ->select(['id', 'role_id', 'name', 'email', 'phone_number', 'profile_image']);
    }

    public function receiverDetails()
    {
        return $this->belongsTo('App\Models\User', 'receiver_id')
            ->with('role')
            ->select(['id', 'role_id', 'name', 'email', 'phone_number', 'profile_image']);
    }

    public function getChats($posted_data = array())
    {
        $columns = ['chats.*'];
        $select_columns = array_merge($columns, []);

        $query = Chat::latest();
       
        $query = $query->with('senderDetails')
                    ->with('receiverDetails');

        if (isset($posted_data['chat_id'])) {
            $query = $query->where('chats.id', $posted_data['chat_id']);
        }
        
        if (isset($posted_data['last_chat_all'])) {

            $last_chat_ids = DB::select(DB::raw("
                SELECT Max(id) AS last_id
                    FROM   chats
                WHERE
                    sender_id = ".$posted_data['sender_id']." OR receiver_id = ".$posted_data['sender_id']."
                GROUP BY
                    CONCAT(
                        LEAST( `chats`.`receiver_id`, `chats`.`sender_id` ),
                        '.',
                        GREATEST( `chats`.`receiver_id`, `chats`.`sender_id` )
                    )"
            ));

            $last_chat_ids = json_decode(json_encode($last_chat_ids), true);
            $query = $query->whereIn('chats.id', $last_chat_ids);
        }
        else {
            if ( isset($posted_data['sender_id']) && isset($posted_data['receiver_id']) ) {

                $filter = array(
                    $posted_data['sender_id'], $posted_data['receiver_id']
                );

                $query = $query->whereIn('chats.sender_id', $filter)
                    ->whereIn('chats.receiver_id', $filter);
            }

            if (isset($posted_data['text'])) {
                $filter = array(
                    $posted_data['sender_id']
                );
    
                // $query = $query->whereIn('chats.sender_id', $filter)
                //     ->whereIn('chats.receiver_id', $filter);

                $query = $query->where(function ($query) use ($posted_data, $filter) {
                    $query->where('chats.sender_id', $filter)
                        ->orWhere('chats.receiver_id', $filter);
                });
    
                $query = $query->where(function ($query) use ($posted_data) {
                    $query->where('chats.text', 'like', '%' . $posted_data['text'] . '%')
                        ->orWhere('sender.name', 'like', '%' . $posted_data['text'] . '%')
                        ->orWhere('receiver.name', 'like', '%' . $posted_data['text'] . '%');
                });
            }
            else {
                if (isset($posted_data['sender_id']) && !isset($posted_data['receiver_id']) ) {
                    $query = $query->where('chats.sender_id', $posted_data['sender_id']);
                }
                if (isset($posted_data['receiver_id']) && !isset($posted_data['sender_id']) ) {
                    $query = $query->where('chats.receiver_id', $posted_data['receiver_id']);
                }
            }
            // if (isset($posted_data['text'])) {
            //     $query = $query->where(function ($query) use ($posted_data) {
            //         $query->where('chats.text', 'like', '%' . $posted_data['text'] . '%')
            //             ->orWhere('users.name', 'like', '%' . $posted_data['text'] . '%');
            //     });
            // }

            // if (isset($posted_data['last_chat'])) {
            //     $posted_data['orderBy_name'] = 'id';
            //     $posted_data['orderBy_value'] = 'DESC';

            //     if(isset($posted_data['paginate'])) {
            //         unset($posted_data['paginate']);
            //     }

            //     $posted_data['detail'] = true;
            // }
        }

        if (isset($posted_data['last_chat'])) {
            $posted_data['orderBy_name'] = 'id';
            $posted_data['orderBy_value'] = 'DESC';

            if(isset($posted_data['paginate'])) {
                unset($posted_data['paginate']);
            }

            $posted_data['detail'] = true;
        }

        if(isset($posted_data['users_join'])){
            $query->join('users as sender', 'chats.sender_id', '=', 'sender.id');
            $columns = ['sender.name'];
            $select_columns = array_merge($select_columns, $columns);
        }

        if(isset($posted_data['users_join'])){
            $query->join('users as receiver', 'chats.receiver_id', '=', 'receiver.id');
            $columns = ['receiver.name'];
            $select_columns = array_merge($select_columns, $columns);
        }

        $query->select($select_columns);
        
        $query->getQuery()->orders = null;
        if (isset($posted_data['orderBy_name'])) {
            $query->orderBy($posted_data['orderBy_name'], $posted_data['orderBy_value']);
        } else {
            $query->orderBy('id', 'DESC');
        }

        if (isset($posted_data['groupBy_value'])) {
            // $query->groupBy($posted_data['groupBy_name'], $posted_data['groupBy_value']);
            $query->groupBy($posted_data['groupBy_value']);
        }

        if (isset($posted_data['print_query'])) {
            $result = $query->toSql();
            echo "<pre>";
            print_r($result);
            echo "</pre>";
            exit("@@@@");
        }
        
        if (isset($posted_data['paginate'])) {
            $result = $query->paginate($posted_data['paginate']);
        }
        else {
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

    public function saveUpdateChat($posted_data = array())
    {
        if (isset($posted_data['update_id'])) {
            $data = Chat::find($posted_data['update_id']);
        } else {
            $data = new Chat;
        }

        if (isset($posted_data['sender_id'])) {
            $data->sender_id = $posted_data['sender_id'];
        }
        if (isset($posted_data['receiver_id'])) {
            $data->receiver_id = $posted_data['receiver_id'];
        }
        if (isset($posted_data['text'])) {
            $data->text = $posted_data['text'];
        }
        if (isset($posted_data['seen_at'])) {
            $data->seen_at = $posted_data['seen_at'];
        }
        if (isset($posted_data['attachment_path'])) {
            $data->attachment_path = $posted_data['attachment_path'];
        }

        $data->save();
        return $data;
    }

    public function deleteChat($id=0)
    {
        $data = Chat::find($id);
        return $data->delete();
    }
}