<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController as BaseController;
use App\Models\Support;
use Illuminate\Support\Facades\Auth;

class SupportController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $request_data = $request->all();
        $request_data['paginate'] = 10;

        if (isset($request_data['per_page']))
            $request_data['paginate'] = $request_data['per_page'];
        
        $supports = Support::getSupports($request_data);
        $message = count($supports) > 0 ? 'Supports retrieved successfully.' : 'Supports not found against your query.';

        return $this->sendResponse($supports, $message);
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
            'meta_key'     => 'required',
            'meta_value'     => 'required',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);   
        }


        $request_data['detail'] = true;
        $getSupports = Support::getSupports($request_data);
        if($getSupports){
            $request_data['update_id'] = $getSupports->id;
        }

        $request_data['user_id'] = \Auth::user()->id;
        $support = Support::saveUpdateSupport($request_data);

        if ( isset($support->id) ){
            if(isset($request_data['update_id'])){
                return $this->sendResponse($support, 'Support is successfully updated.');
            }else{
                return $this->sendResponse($support, 'Support is successfully added.');
            }
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
        $support = Support::find($id);
  
        if (is_null($support)) {
            $error_message['error'] = 'Support not found.';
            return $this->sendError($error_message['error'], $error_message);  
        }
   
        return $this->sendResponse($support, 'Support retrieved successfully.');
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
            'meta_key'     => 'required',
            'meta_value'     => 'required',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);   
        }
        $request_data['user_id'] = \Auth::user()->id;
        $request_data['update_id'] = $id;
        $support = Support::saveUpdateSupport($request_data);

        if ( isset($support->id) ){
            return $this->sendResponse($support, 'Support is successfully updated.');
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
        $response = Support::deleteSupport($id);
        if($response) {
            return $this->sendResponse([], 'Support deleted successfully.');
        }
        else {
            $error_message['error'] = 'Support already deleted / Not found in database.';
            return $this->sendError($error_message['error'], $error_message);  
        }
    }
}