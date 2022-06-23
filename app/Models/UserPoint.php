<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPoint extends Model
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function point_category()
    {
        return $this->belongsTo(PointCategorie::class);
    }


    public function getUserPoint($posted_data = array())
    {
        $query = UserPoint::latest()->with('user')->with('point_category');

        if (isset($posted_data['id'])) {
            $query = $query->where('id', $posted_data['id']);
        }
        if (isset($posted_data['user_id'])) {
            $query = $query->where('user_id', $posted_data['user_id']);
        }
        if (isset($posted_data['point_categorie_id'])) {
            $query = $query->where('point_categorie_id', $posted_data['point_categorie_id']);
        }

        $query->select('*');
        
        $query->getQuery()->orders = null;
        if (isset($posted_data['orderBy_name'])) {
            $query->orderBy($posted_data['orderBy_name'], $posted_data['orderBy_value']);
        } else {
            $query->orderBy('id', 'DESC');
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



    public function saveUpdateUserPoint($posted_data = array())
    {
        if (isset($posted_data['update_id'])) {
            $data = UserPoint::find($posted_data['update_id']);
        } else {
            $data = new UserPoint;
        } 
        
        if (isset($posted_data['user_id'])) {
            $data->user_id = $posted_data['user_id'];
        }
        if (isset($posted_data['point_categorie_id'])) {
            $data->point_categorie_id = $posted_data['point_categorie_id'];
        }
        if (isset($posted_data['total_points'])) {
            $data->total_points = $posted_data['total_points'];
        }
        if (isset($posted_data['total_user_point_count'])) {
            $data->total_point_count = $posted_data['total_user_point_count'];
        }
        if (isset($posted_data['last_points'])) {
            $data->last_points = $posted_data['last_points'];
        }

        $data->save();
        return $data;
    }


    public function deleteUserPoint($id) {
        $data = UserPoint::find($id);
        if (isset($data->id) )
            return $data->delete();
        else 
            return false;
    }

    // *********************************
    // $totalorder = $request->get('totalorder');
    // UserPoint::assignUserPoint([
    //     'point_categorie_id' => 1,
    //     'totalorder' => $totalorder,
    // ]);
    // echo '<pre>';
    // print_r('test');
    // exit;        
    // *********************************
    public function assignUserPoint($posted_data = array()){

        if (isset($posted_data['point_categorie_id'])) {
            $point_categorie_id = $posted_data['point_categorie_id'];
        }
        if (isset($posted_data['totalprice'])) {
            $totalprice = $posted_data['totalprice'];
        }
        
        $PointCategorieDetail = PointCategorie::getPointCategorie([
            'id' => $point_categorie_id,
            'detail' => true,
        ]);
        
        if($PointCategorieDetail){
            $point_value = $PointCategorieDetail->point_value;
            $point_target = $PointCategorieDetail->point_target;
            $per_point_value = $PointCategorieDetail->per_point_value;
            $point_categorie_id = $PointCategorieDetail->id;
            $user_id = \Auth::user()->id;
            
            $getUserPoint = UserPoint::getUserPoint([
                'user_id' => $user_id,
                'point_categorie_id' => $point_categorie_id,
                'detail' => true,
            ]);
            $oldprice = 0;
            $old_total_points = 0;
            $old_total_user_point_count = 0;
            if($getUserPoint){
                $UserPointUpdate_id = $getUserPoint->id;
                $oldprice = $getUserPoint->last_points;
                $old_total_points = $getUserPoint->total_points;
                $old_total_user_point_count = $getUserPoint->total_point_count;
            }

            if($totalprice>=$point_target){

                $total_points = $old_total_points+$point_value;
                $total_user_point_count = 1 + $old_total_user_point_count;
                // $last_points = $oldprice + $totalprice;
                $last_points = $totalprice;

                $posted_data = array();
                $posted_data['user_id'] = $user_id;
                $posted_data['point_categorie_id'] = $point_categorie_id;
                $posted_data['total_points'] = $total_points;
                $posted_data['last_points'] = $last_points;
                $posted_data['total_user_point_count'] = $total_user_point_count;
                if(isset($UserPointUpdate_id)){
                    $posted_data['update_id'] = $UserPointUpdate_id;
                }
                $user_point_res = UserPoint::saveUpdateUserPoint($posted_data);

                $total_point_count = 1;
                $total_point_value = floor(($point_value*$total_point_count)*$per_point_value);

                $posted_data = array();
                $posted_data['user_point_id'] = $user_point_res->id;
                $posted_data['point_value'] = $point_value;
                $posted_data['point_target'] = $point_target;
                $posted_data['per_point_value'] = $per_point_value;
                $posted_data['total_point_count'] = $total_point_count;
                $posted_data['total_point_value'] = $total_point_value;
                UserPointLog::saveUpdateUserPointLog($posted_data);

                $user = User::find($user_id);
                $user->increment('total_point',$point_value);
                $user->increment('remaining_point',$point_value);
            }
        }
        return $point_value;
    }
    // public function assignUserPoint($posted_data = array()){

    //     if (isset($posted_data['point_categorie_id'])) {
    //         $point_categorie_id = $posted_data['point_categorie_id'];
    //     }
    //     if (isset($posted_data['totalorder'])) {
    //         $totalorder = $posted_data['totalorder'];
    //     }
        
    //     $PointCategorieDetail = PointCategorie::getPointCategorie([
    //         'id' => $point_categorie_id,
    //         'detail' => true,
    //     ]);
        
    //     if($PointCategorieDetail){
    //         $point_value = $PointCategorieDetail->point_value;
    //         $point_target = $PointCategorieDetail->point_target;
    //         $per_point_value = $PointCategorieDetail->per_point_value;
    //         $point_categorie_id = $PointCategorieDetail->id;
    //         $user_id = \Auth::user()->id;
            
    //         $getUserPoint = UserPoint::getUserPoint([
    //             'user_id' => $user_id,
    //             'point_categorie_id' => $point_categorie_id,
    //             'detail' => true,
    //         ]);
    //         $oldorder = 0;
    //         $old_total_points = 0;
    //         $old_total_user_point_count = 0;
    //         if($getUserPoint){
    //             $UserPointUpdate_id = $getUserPoint->id;
    //             $oldorder = $getUserPoint->last_points;
    //             $old_total_points = $getUserPoint->total_points;
    //             $old_total_user_point_count = $getUserPoint->total_point_count;
    //         }
    //         $oldorder = $totalorder - $oldorder;

    //         if($oldorder>=$point_target){
    //             $total_points = floor($oldorder/$point_target);
    //             $total_point_count = $total_points;
    //             $total_points = floor($total_points*$point_value);
    //             $total_point_value = $total_points;
    //             $total_points = $old_total_points+$total_points;
    //             $last_points = floor($totalorder/$point_target);
    //             $last_points = $last_points*$point_target;

    //             $total_user_point_count = $total_point_count + $old_total_user_point_count;

    //             $posted_data = array();
    //             $posted_data['user_id'] = $user_id;
    //             $posted_data['point_categorie_id'] = $point_categorie_id;
    //             $posted_data['total_points'] = $total_points;
    //             $posted_data['last_points'] = $last_points;
    //             $posted_data['total_user_point_count'] = $total_user_point_count;
    //             if(isset($UserPointUpdate_id)){
    //                 $posted_data['update_id'] = $UserPointUpdate_id;
    //             }
    //             $user_point_res = UserPoint::saveUpdateUserPoint($posted_data);

    //             $posted_data = array();
    //             $posted_data['user_point_id'] = $user_point_res->id;
    //             $posted_data['point_value'] = $point_value;
    //             $posted_data['point_target'] = $point_target;
    //             $posted_data['per_point_value'] = $per_point_value;
    //             $posted_data['total_point_count'] = $total_point_count;
    //             $posted_data['total_point_value'] = $total_point_value;
    //             UserPointLog::saveUpdateUserPointLog($posted_data);

    //             $user = User::find($user_id);
    //             $user->increment('total_point',$total_point_value);
    //             $user->increment('remaining_point',$total_point_value);
    //         }

    //     }
    // }
}   