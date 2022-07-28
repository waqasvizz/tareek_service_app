<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController as BaseController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserDeliveryOption;

class UserDeliveryOptionController extends BaseController
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
        if (isset($request_data['per_page']))
            $request_data['paginate'] = $request_data['per_page'];
        
        $response = UserDeliveryOption::getUserDeliveryOption($request_data);
        $message = count($response) > 0 ? 'User delivery option retrieved successfully.' : 'User delivery option not found against your query.';

        return $this->sendResponse($response, $message);
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
   
        $validator = \Validator::make($request_data, [
            'title'    => 'required',
            'status'    => 'required',
            'amount' => $request->status == 1 ? 'required': 'nullable',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);   
        }

        $request_data['user_id'] = \Auth::user()->id;
        $response = UserDeliveryOption::saveUpdateUserDeliveryOption($request_data);

        if ( isset($response->id) ){
            return $this->sendResponse($response, 'User delivery option is successfully added.');
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
        $response = UserDeliveryOption::find($id);
  
        if (is_null($response)) {
            $error_message['error'] = 'User delivery option not found.';
            return $this->sendError($error_message['error'], $error_message);  
        }
   
        return $this->sendResponse($response, 'User delivery option retrieved successfully.');
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
        $request_data['update_id'] = $id;
   
        $validator = \Validator::make($request_data, [
            'update_id' => 'required|exists:user_delivery_options,id',
            'title'    => 'required',
            'status'    => 'required',
            'amount' => $request->status == 1 ? 'required': 'nullable',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);   
        }

        $response = UserDeliveryOption::saveUpdateUserDeliveryOption($request_data);

        if ( isset($response->id) ){
            return $this->sendResponse($response, 'User delivery option is successfully updated.');
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
        $response = UserDeliveryOption::deleteUserDeliveryOption($id);
        if($response) {
            return $this->sendResponse([], 'User delivery option deleted successfully.');
        }
        else {
            $error_message['error'] = 'User delivery option already deleted / Not found in database.';
            return $this->sendError($error_message['error'], $error_message);  
        }
    }
}