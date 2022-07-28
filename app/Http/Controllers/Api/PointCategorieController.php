<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController as BaseController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PointCategorie;
use App\Models\UserPoint;
use App\Models\UserPointLog;

class PointCategorieController extends BaseController
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
        
        $response = PointCategorie::getPointCategorie($request_data);
        $message = count($response) > 0 ? 'Point categories retrieved successfully.' : 'Point categories not found against your query.';

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
            'point_name'    => 'required',
            'point_value'    => 'required',
            'point_target'    => 'required',
            'per_point_value'    => 'required',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);   
        }

        $response = PointCategorie::saveUpdatePointCategorie($request_data);

        if ( isset($response->id) ){
            return $this->sendResponse($response, 'Point categories is successfully added.');
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
        $response = PointCategorie::find($id);
  
        if (is_null($response)) {
            $error_message['error'] = 'Point categories not found.';
            return $this->sendError($error_message['error'], $error_message);  
        }
   
        return $this->sendResponse($response, 'Point categories retrieved successfully.');
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
            'point_name'    => 'required',
            'point_value'    => 'required',
            'point_target'    => 'required',
            'per_point_value'    => 'required',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);   
        }

        $request_data['update_id'] = $id;
        $response = PointCategorie::saveUpdatePointCategorie($request_data);

        if ( isset($response->id) ){
            return $this->sendResponse($response, 'Point categories is successfully updated.');
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
        $response = PointCategorie::deletePointCategorie($id);
        if($response) {
            return $this->sendResponse([], 'Point categories deleted successfully.');
        }
        else {
            $error_message['error'] = 'Point categories already deleted / Not found in database.';
            return $this->sendError($error_message['error'], $error_message);  
        }
    }
}