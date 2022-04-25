<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController as BaseController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ServiceReview;
use App\Models\Service;

class ServiceReviewController extends BaseController
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
        
        $response = ServiceReview::getServiceReview($request_data);
        $message = count($response) > 0 ? 'Service review retrieved successfully.' : 'Service review not found against your query.';

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
            'service_id' => 'required|exists:services,id',
            'order_id' => 'required|exists:orders,id',
            'sender_id' => 'required|exists:users,id',
            'receiver_id' => 'required|exists:users,id',
            'stars'    => 'required',
            'description'    => 'required',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);   
        }
        
        $check_request_data['service_id'] = $request_data['service_id'];
        $check_request_data['order_id'] = $request_data['order_id'];
        $check_request_data['sender_id'] = $request_data['sender_id'];
        $check_request_data['detail'] = true;
        $checkServiceReview = ServiceReview::getServiceReview($check_request_data);
        if($checkServiceReview){
            return $this->sendResponse($checkServiceReview, 'You have already submitted service review.');
        }

        $request_data['user_id'] = \Auth::user()->id;
        $response = ServiceReview::saveUpdateServiceReview($request_data);
            

        if ( isset($response->id) ){
            $rating = ServiceReview::where('service_id', $request_data['service_id'])->sum('stars');
            $total_reviews = ServiceReview::where('service_id', $request_data['service_id'])->count();

            $avg_rating = round( ( ($rating/($total_reviews*5)) * 5 ) , 2 );

            Service::saveUpdateService([
                'update_id' => $request_data['service_id'],
                'avg_rating' => $avg_rating
            ]);

            return $this->sendResponse($response, 'Service review is successfully added.');
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
        $response = ServiceReview::find($id);
  
        if (is_null($response)) {
            $error_message['error'] = 'Service review not found.';
            return $this->sendError($error_message['error'], $error_message);  
        }
   
        return $this->sendResponse($response, 'Service review retrieved successfully.');
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
            'update_id' => 'exists:service_reviews,id',
            'service_id' => 'required|exists:services,id',
            'order_id' => 'required|exists:orders,id',
            'sender_id' => 'required|exists:users,id',
            'receiver_id' => 'required|exists:users,id',
            'stars'    => 'required',
            'description'    => 'required',
        ],[
            'update_id.exists' => 'Updated record not exists',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);   
        }

        $request_data['update_id'] = $id;
        $response = ServiceReview::saveUpdateServiceReview($request_data);

        if ( isset($response->id) ){
            $rating = ServiceReview::where('service_id', $request_data['service_id'])->sum('stars');
            $total_reviews = ServiceReview::where('service_id', $request_data['service_id'])->count();

            $avg_rating = round( ( ($rating/($total_reviews*5)) * 5 ) , 2 );

            Service::saveUpdateService([
                'update_id' => $request_data['service_id'],
                'avg_rating' => $avg_rating
            ]);

            return $this->sendResponse($response, 'Service review is successfully updated.');
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
        $response = ServiceReview::deleteServiceReview($id);
        if($response) {
            return $this->sendResponse([], 'Service review deleted successfully.');
        }
        else {
            $error_message['error'] = 'Service review already deleted / Not found in database.';
            return $this->sendError($error_message['error'], $error_message);  
        }
    }
}