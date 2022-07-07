<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController as BaseController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserBank;

class UserBankController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $request_data['paginate'] = 10;
        $request_data = $request->all();
        if (isset($request_data['per_page']))
            $request_data['paginate'] = $request_data['per_page'];
        
        $response = UserBank::getUserBank($request_data);
        $message = count($response) > 0 ? 'User Bank retrieved successfully.' : 'User Bank not found against your query.';

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
            'user_id'       => 'exists:users,id',
            'title'         => 'required|max:100',
            'iban'          => 'required|max:24|min:24',
        ]);

        if($validator->fails()){
            return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);   
        }
        
        if ( !(isset($request_data['user_id']) && $request_data['user_id']) )
            $request_data['user_id'] = \Auth::user()->id;
        else
            $request_data['user_id'] = $request_data['user_id'];

        $response = UserBank::saveUpdateUserBank($request_data);

        if ( isset($response->id) ){
            return $this->sendResponse($response, 'User Bank is successfully added.');
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
        $response = UserBank::find($id);
  
        if (is_null($response)) {
            $error_message['error'] = 'User Bank not found.';
            return $this->sendError($error_message['error'], $error_message);  
        }
   
        return $this->sendResponse($response, 'User Bank retrieved successfully.');
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
            'update_id'     => 'required|exists:user_banks,id',
            'user_id'       => 'required|exists:users,id',
            'title'         => 'required|max:100',
            'iban'          => 'required|max:24|min:24',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);   
        }

        $response = UserBank::saveUpdateUserBank($request_data);

        if ( isset($response->id) ){
            return $this->sendResponse($response, 'User Bank is successfully updated.');
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
        $response = UserBank::deleteUserBank($id);
        if($response) {
            return $this->sendResponse([], 'User Bank deleted successfully.');
        }
        else {
            $error_message['error'] = 'User Bank already deleted / Not found in database.';
            return $this->sendError($error_message['error'], $error_message);  
        }
    }
}