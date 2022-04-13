<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController as BaseController;
use Validator;
use App\Models\Notification;
use App\Models\User;

class NotificationController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $params = $request->all();
        if ( isset($params['filter']) && $params['filter'] == 'today')
            $params['one_day_time'] = date("Y-m-d");
        if ( isset($params['filter']) && $params['filter'] == 'last-day')
            $params['last_day_time'] = date("Y-m-d", strtotime( '-1 days' ) );
        if ( isset($params['filter']) && $params['filter'] == 'seven-day')
            $params['last_seven_day_time'] = date("Y-m-d", strtotime( '-7 days' ) );

        $params['paginate'] = 10;

        $notification = Notification::getNotifications($params);
        $message = !empty($notification) ? 'Notifications retrieved successfully.' : 'Notifications not found against your query.';

        return $this->sendResponse($notification, $message);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request_data = $request->all(); 
   
        $validator = Validator::make($request_data, [
            'receiver_id' => 'required',
            'sender_id' => 'required',
            'text' => 'required',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);
        }

        $notification = Notification::saveUpdateNotification($request_data);

        $posted_data = array();
        $posted_data['detail'] = true;
        $posted_data['notification_id'] = $notification['id'];

        $notification_data = Notification::getNotifications($posted_data);

        return $this->sendResponse($notification_data, 'Notification posted successfully.');
    } 
   
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $notification = Notification::find($id);
  
        if (is_null($notification)) {
            $error_message['error'] = 'Notification is not found.';
            return $this->sendError($error_message['error'], $error_message);
        }
   
        return $this->sendResponse($notification, 'Notification retrieved successfully.');
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request_data = $request->all();
        $validator = Validator::make($request_data, [
            'receiver_id' => 'required',
            'sender_id' => 'required',
            'stars' => 'required',
            'description' => 'required'
        ]);
   
        if($validator->fails()){
            return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);       
        }

        $posted_data = array();
        $posted_data['detail'] = true;
        $posted_data['id'] = $id;
        $notification = Notification::getNotifications($posted_data);
        if(!$notification){
            $error_message['error'] = 'Notification is not found.';
            return $this->sendError($error_message['error'], $error_message);
        }
        
        $request_data['update_id'] = $id;
        $notification = Notification::saveUpdateNotification($request_data);

        return $this->sendResponse($notification, 'Notification updated successfully.');
    }
   
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(Notification::find($id)){
            Notification::deleteNotification($id); 
            return $this->sendResponse([], 'Notification deleted successfully.');
        }else{
            $error_message['error'] = 'Notification is already deleted.';
            return $this->sendError($error_message['error'], $error_message);
        }
    }
}