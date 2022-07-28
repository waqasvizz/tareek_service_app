<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController as BaseController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserStripeInformation;

class UserStripeInformationController extends BaseController
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
        
        $response = UserStripeInformation::getUserStripeInformation($request_data);

        // echo '<pre>';
        // print_r(\Crypt::decrypt($response[0]['pk_live']));
        // exit;
        
        $message = count($response) > 0 ? 'User stripe information retrieved successfully.' : 'User stripe information not found against your query.';

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
            'publishable_key'    => 'required',
            'secret_key'    => 'required',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);   
        }

        $request_data['user_id'] = \Auth::user()->id;
        try {
            $stripe = new \Stripe\StripeClient($request_data['secret_key']);
            $stripe->balance->retrieve();
            
        } catch (\Throwable $th) { 
            // echo $th->getMessage();
            $error_message['error'] = $th->getMessage();
            return $this->sendError($error_message['error'], $error_message);  
        }
        
        $check_keys = UserStripeInformation::getUserStripeInformation([
            'user_id' => $request_data['user_id'],
            'detail' => true,
        ]);
        if($check_keys){
            $request_data['update_id'] = $check_keys->id;
        }

        $response = UserStripeInformation::saveUpdateUserStripeInformation($request_data);

        if ( isset($response->id) ){
            if(isset($request_data['update_id'])){
                return $this->sendResponse($response, 'User stripe information is successfully updated.');
            }else{
                return $this->sendResponse($response, 'User stripe information is successfully added.');
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
        $response = UserStripeInformation::find($id);
  
        if (is_null($response)) {
            $error_message['error'] = 'User stripe information not found.';
            return $this->sendError($error_message['error'], $error_message);  
        }
   
        return $this->sendResponse($response, 'User stripe information retrieved successfully.');
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
            'update_id' => 'exists:user_stripe_informations,id',
            'publishable_key'    => 'required',
            'secret_key'    => 'required',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);   
        }

        try {
            $stripe = new \Stripe\StripeClient($request_data['secret_key']);
            $stripe->balance->retrieve();
            
        } catch (\Throwable $th) { 
            // echo $th->getMessage();
            $error_message['error'] = $th->getMessage();
            return $this->sendError($error_message['error'], $error_message);  
        }
        $response = UserStripeInformation::saveUpdateUserStripeInformation($request_data);


        if ( isset($response->id) ){
            return $this->sendResponse($response, 'User stripe information is successfully updated.');
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
        $response = UserStripeInformation::deleteUserStripeInformation($id);
        if($response) {
            return $this->sendResponse([], 'User stripe information deleted successfully.');
        }
        else {
            $error_message['error'] = 'User stripe information already deleted / Not found in database.';
            return $this->sendError($error_message['error'], $error_message);  
        }
    }
}