<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController as BaseController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserCard;

class UserCardController extends BaseController
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
        
        $response = UserCard::getUserCard($request_data);
        $message = count($response) > 0 ? 'User Card retrieved successfully.' : 'User Card not found against your query.';

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
            'card_name'    => 'required',
            'card_number'    => 'required',
            'exp_month'    => 'required',
            'exp_year'    => 'required',
            'cvc_number'    => 'required',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);   
        }

        $request_data['user_id'] = \Auth::user()->id;
        $response = UserCard::saveUpdateUserCard($request_data);

        if ( isset($response->id) ){
            return $this->sendResponse($response, 'User Card is successfully added.');
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
        $response = UserCard::find($id);
  
        if (is_null($response)) {
            $error_message['error'] = 'User Card not found.';
            return $this->sendError($error_message['error'], $error_message);  
        }
   
        return $this->sendResponse($response, 'User Card retrieved successfully.');
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
   
        $validator = \Validator::make($request_data, [
            'card_name'    => 'required',
            'card_number'    => 'required',
            'exp_month'    => 'required',
            'exp_year'    => 'required',
            'cvc_number'    => 'required',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);   
        }

        $request_data['update_id'] = $id;
        $response = UserCard::saveUpdateUserCard($request_data);

        if ( isset($response->id) ){
            return $this->sendResponse($response, 'User Card is successfully updated.');
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
        $response = UserCard::deleteUserCard($id);
        if($response) {
            return $this->sendResponse([], 'User Card deleted successfully.');
        }
        else {
            $error_message['error'] = 'User Card already deleted / Not found in database.';
            return $this->sendError($error_message['error'], $error_message);  
        }
    }
}