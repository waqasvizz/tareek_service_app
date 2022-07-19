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
        if (isset($request_data['user_id']))
            $request_data['user_id'] = $request_data['user_id'];
        else {
            $error_message['error'] = 'User id is not provided in request data.';
            return $this->sendError($error_message['error'], $error_message);  
        }
        if (isset($request_data['per_page']))
            $request_data['paginate'] = $request_data['per_page'];

        $request_data['to_array'] = true;
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
            'card_number'    => 'required|max:16',
            'exp_month'    => 'required',
            'exp_year'    => 'required|max:4|min:4',
            'cvc_number'    => 'required|max:4|min:3',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);
        }

        $card_details = UserCard::getUserCard(['user_id' => \Auth::user()->id, 'detail' => true]);
        if ($card_details) {
            $error_message['error'] = 'A bank card already belongs to this user.';
            return $this->sendError($error_message['error'], $error_message);
        }
        else {
            $request_data['user_id'] = \Auth::user()->id;
        }
        
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
    public function update(Request $request, $id = 0)
    {
        $request_data = $request->all();
        $request_data['update_id'] = $id;
      
        $validator = \Validator::make($request_data, [
            'update_id'    => 'required|exists:user_cards,id',
            'card_name'    => 'required',
            'card_number'    => 'required|max:16',
            'exp_month'    => 'required',
            'exp_year'    => 'required|max:4|min:4',
            'cvc_number'    => 'required|max:4|min:3',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);   
        }

        $request_data['update_id'] = $id;
        $request_data['user_id'] = \Auth::user()->id;
        $request_data['to_array'] = true;

        $response = UserCard::saveUpdateUserCard($request_data);

        if ( isset($response[0]['id']) ){
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