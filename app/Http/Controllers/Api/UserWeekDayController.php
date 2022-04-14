<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController as BaseController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\UserWeekDay;

class UserWeekDayController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $request_data = $request->all();
        // $request_data['paginate'] = 10;
        // if (isset($request_data['per_page']))
        //     $request_data['paginate'] = $request_data['per_page'];
        
        $user_week_day = UserWeekDay::getUserWeekDay($request_data);
        $message = count($user_week_day) > 0 ? 'User week days retrieved successfully.' : 'User week days not found against your query.';

        return $this->sendResponse($user_week_day, $message);
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
            'name'    => 'required',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);   
        }

        $user_week_day = UserWeekDay::saveUpdateUserWeekDay($request_data);

        if ( isset($user_week_day->id) ){
            return $this->sendResponse($user_week_day, 'User week day is successfully added.');
        }else{
            $error_message['error'] = 'Somthing went wrong during query.';
            return $this->sendError($error_message['error'], $error_message);  
        }
    } 
   
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user_week_day = UserWeekDay::find($id);
  
        if (is_null($user_week_day)) {
            $error_message['error'] = 'User week day not found.';
            return $this->sendError($error_message['error'], $error_message);  
        }
   
        return $this->sendResponse($user_week_day, 'User week day retrieved successfully.');
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
            'name'    => 'required',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);   
        }

        $request_data['update_id'] = $id;
        $user_week_day = UserWeekDay::saveUpdateUserWeekDay($request_data);

        if ( isset($user_week_day->id) ){
            return $this->sendResponse($user_week_day, 'User week day is successfully updated.');
        }else{
            $error_message['error'] = 'Somthing went wrong during query.';
            return $this->sendError($error_message['error'], $error_message);  
        }
    }
   
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $response = UserWeekDay::deleteUserWeekDay($id);
        if($response) {
            return $this->sendResponse([], 'User week day deleted successfully.');
        }
        else {
            $error_message['error'] = 'User week day already deleted / Not found in database.';
            return $this->sendError($error_message['error'], $error_message);  
        }
    }
}