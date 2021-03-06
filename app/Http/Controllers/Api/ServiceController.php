<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController as BaseController;
use App\Models\Service;
use App\Models\UserWeekDay;
use App\Models\FCM_Token;
use App\Models\Notification;

// use App\Http\Resources\Service as ServiceResource;

class ServiceController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // $params = $request->all();

        $posted_data = $request->all();
        $posted_data['paginate'] = 10;

        if (isset($posted_data['service_id']))
            $posted_data['id'] = $posted_data['service_id'];
        if (isset($posted_data['service_name']))
            $posted_data['service_name'] = $posted_data['service_name'];
        if (isset($posted_data['per_page']))
            $posted_data['paginate'] = $posted_data['per_page'];
        
        $services = Service::getServices($posted_data);
        $message = count($services) > 0 ? 'Services retrieved successfully.' : 'Services not found against your query.';

        return $this->sendResponse($services, $message);
        
        // $posted_data['count'] = true;
        // $count = Service::getServices($posted_data);
    
        // return $this->sendResponse($services, 'Services retrieved successfully.', $count);
        // return $this->sendResponse(ServiceResource::collection($services), 'Services retrieved successfully.', $count);
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
            'service_title'       => 'required',
            'service_price'       => 'required',
            // 'service_category'    => 'required',
            'service_category' => 'required|exists:categories,id',
            'service_location'    => 'required',
            'service_lat'         => 'required',
            'service_long'        => 'required',
            'service_description' => 'required',
            'service_contact'     => 'required',
            'week_days'     => 'required',
            'start_time'     => 'required',
            'end_time'     => 'required',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);     
        }

        if (isset($request->service_image)) {
            
            $allowedfileExtension = ['jpg','jpeg','png'];
            $extension = strtolower($request->service_image->getClientOriginalExtension());

            $check = in_array($extension, $allowedfileExtension);
            if($check) {
                $response = upload_files_to_storage($request, $request->service_image, 'service_image');
                $request_data['service_img'] = $response['file_path'];

                if( isset($response['action']) && $response['action'] == true ) {
                    
                    $img_data['file_name'] = isset($response['file_name']) ? $response['file_name'] : "";
                    $img_data['file_path'] = isset($response['file_path']) ? $response['file_path'] : "";
                }
            }
            else {
                $error_message['error'] = 'Invalid file format you can only add jpg,jpeg and png file format.';
                return $this->sendError($error_message['error'], $error_message);
            }
        }
        else {
            $error_message['error'] = 'Service Image is not found.';
            return $this->sendError($error_message['error'], $error_message);
        }

        $request_data['user_id'] = auth()->user()->id;
        $service = Service::saveUpdateService($request_data);

        if ( isset($service->id) ){
            
            foreach ($request_data['week_days'] as $key => $value) {
                UserWeekDay::saveUpdateUserWeekDay([
                    'service_id' => $service->id,
                    'week_day_id' => $value,
                    'start_time' => $request_data['start_time'][$key],
                    'end_time' => $request_data['end_time'][$key],
                ]);
            }
            $service = Service::getServices([
                'id' => $service->id,
                'detail' => true
            ]);

            $notification_text = "A new service has been added into the app.";
            $user_id = \Auth::user()->id;
    
            $notification_params = array();
            $notification_params['sender'] = $user_id;
            $notification_params['receiver'] = 1;
            $notification_params['slugs'] = "new-service";
            $notification_params['notification_text'] = $notification_text;
            $notification_params['metadata'] = "service_id=$service->id";
            
            $response = Notification::saveUpdateNotification([
                'sender' => $notification_params['sender'],
                'receiver' => $notification_params['receiver'],
                'slugs' => $notification_params['slugs'],
                'notification_text' => $notification_params['notification_text'],
                'metadata' => $notification_params['metadata']
            ]);
    
            $firebase_devices = FCM_Token::getFCM_Tokens(['user_id' => $notification_params['receiver']])->toArray();
            $notification_params['registration_ids'] = array_column($firebase_devices, 'device_token');
    
            if ($response) {
    
                if ( isset($model_response['user']) )
                    unset($model_response['user']);
                if ( isset($model_response['post']) )
                    unset($model_response['post']);
    
                $notification = FCM_Token::sendFCM_Notification([
                    'title' => $notification_params['slugs'],
                    'body' => $notification_params['notification_text'],
                    'metadata' => $notification_params['metadata'],
                    'registration_ids' => $notification_params['registration_ids'],
                    'details' => $service
                ]);
            }
            
            return $this->sendResponse($service, 'Service is successfully added.');
        }
        else{
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
        $service = Service::find($id);
  
        if (is_null($service)) {
            $error_message['error'] = 'Service not found.';
            return $this->sendError($error_message['error'], $error_message);
        }
   
        return $this->sendResponse($service, 'Service retrieved successfully.');
        // return $this->sendResponse(new ServiceResource($service), 'Service retrieved successfully.');
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function update(Request $request, Service $service)
    public function update(Request $request, $id)
    {
        $request_data = $request->all();
        $request_data['update_id'] = $id;
   
        $validator = \Validator::make($request_data, [
            'update_id'    => 'required|exists:services,id',
            'service_category'    => 'required|exists:categories,id',
        ]);

        if($validator->fails()){
            return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);    
        }
        
        if (isset($request->service_image)) {
            
            $allowedfileExtension = ['jpg','jpeg','png'];
            $extension = strtolower($request->service_image->getClientOriginalExtension());

            $check = in_array($extension, $allowedfileExtension);
            if($check) {
                $res['action'] = true;
                if (isset($service_record->service_img) && $service_record->service_img != '')
                    $res = delete_files_from_storage($service_record->service_img);

                if ($res['action']) {
                    $response = upload_files_to_storage($request, $request->service_image, 'service_image');
                    $request_data['service_img'] = $response['file_path'];
                    if( isset($response['action']) && $response['action'] == true ) {
                        $img_data['file_name'] = isset($response['file_name']) ? $response['file_name'] : "";
                        $img_data['file_path'] = isset($response['file_path']) ? $response['file_path'] : "";
                    }
                }
                else {
                    $error_message['error'] = 'Somthing went wrong during image replacement.';
                    return $this->sendError($error_message['error'], $error_message);
                }
            }
            else {
                $error_message['error'] = 'Invalid file format you can only add jpg,jpeg and png file format.';
                return $this->sendError($error_message['error'], $error_message);
            }
        }

        $service = Service::saveUpdateService($request_data);

        if ( isset($service->id) ){
            $res_service = $service;

            $user_week_days_list = UserWeekDay::getUserWeekDay([
                'service_id' => $service->id
            ]);
            if($user_week_days_list){
                foreach ($user_week_days_list as $key => $value) {
                    UserWeekDay::deleteUserWeekDay($value->id);
                }
            }

            if($request_data['week_days']){
                foreach ($request_data['week_days'] as $key => $value) {
                    UserWeekDay::saveUpdateUserWeekDay([
                        'service_id' => $service->id,
                        'week_day_id' => $value,
                        'start_time' => $request_data['start_time'][$key],
                        'end_time' => $request_data['end_time'][$key],
                    ]);
                }
            }
            $res_service = Service::getServices([
                'id' => $service->id,
                'detail' => true
            ]);

            return $this->sendResponse($res_service, 'Service is successfully updated.');
        }
        else{
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
    // public function destroy(Service $service)
    public function destroy($id)
    {
        if(Service::find($id)){
            $filepath = Service::find($id)->service_img;
            delete_files_from_storage($filepath);
            Service::deleteService($id);
            return $this->sendResponse([], 'Service deleted successfully.');
        }else{
            $error_message['error'] = 'Service already deleted.';
            return $this->sendError($error_message['error'], $error_message);   
        } 
    }
}