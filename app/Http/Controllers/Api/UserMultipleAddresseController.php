<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController as BaseController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserMultipleAddresse;
use App\Models\CountriesMetadata;

class UserMultipleAddresseController extends BaseController
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
        
        $response = UserMultipleAddresse::getUserMultipleAddresse($request_data);
        $message = count($response) > 0 ? 'User address retrieved successfully.' : 'User Address not found against your query.';

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
            'address'    => 'required',
            'latitude'    => 'required',
            'longitude'    => 'required',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);   
        }

        if(isset($request_data['country']) || $request_data['country']){
                
            $country_arr = array();
            $country_arr['name'] = $request_data['country'];
            $country_arr['detail'] = true;
            $country_data = CountriesMetadata::getCountriesMetadata($country_arr);
            
            if ( !isset($country_data->id) ) {
                $error_message['error'] = 'Please enter the valid country name for the Supplier.';
                return $this->sendError($error_message['error'], $error_message);  
            }
        }

        $request_data['user_id'] = \Auth::user()->id;
        $response = UserMultipleAddresse::saveUpdateUserMultipleAddresse($request_data);

        if ( isset($response->id) ){
            return $this->sendResponse($response, 'User address is successfully added.');
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
        $response = UserMultipleAddresse::find($id);
  
        if (is_null($response)) {
            $error_message['error'] = 'User Address not found.';
            return $this->sendError($error_message['error'], $error_message);  
        }
   
        return $this->sendResponse($response, 'User address retrieved successfully.');
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
            'update_id'    => 'required|exists:user_multiple_addresses,id',
            'title'    => 'required',
            'address'    => 'required',
            'latitude'    => 'required',
            'longitude'    => 'required',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);   
        }

        if(isset($request_data['country']) || $request_data['country']){
                
            $country_arr = array();
            $country_arr['name'] = $request_data['country'];
            $country_arr['detail'] = true;
            $country_data = CountriesMetadata::getCountriesMetadata($country_arr);
            
            if ( !isset($country_data->id) ) {
                $error_message['error'] = 'Please enter the valid country name for the Supplier.';
                return $this->sendError($error_message['error'], $error_message);  
            }
        }

        $request_data['user_id'] = \Auth::user()->id;
        $request_data['update_id'] = $id;
        $response = UserMultipleAddresse::saveUpdateUserMultipleAddresse($request_data);

        if ( isset($response->id) ){
            return $this->sendResponse($response, 'User address is successfully updated.');
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
        $response = UserMultipleAddresse::deleteUserMultipleAddresse($id);
        if($response) {
            return $this->sendResponse([], 'User address deleted successfully.');
        }
        else {
            $error_message['error'] = 'User address already deleted / Not found in database.';
            return $this->sendError($error_message['error'], $error_message);  
        }
    }
}