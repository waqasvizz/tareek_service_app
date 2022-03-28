<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController as BaseController;
use Validator;
use App\Models\Service;
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
        $params = $request->all();

        $posted_data =  array();
        $posted_data['paginate'] = 10;

        if (isset($params['service_id']))
            $posted_data['id'] = $params['service_id'];
        if (isset($params['service_name']))
            $posted_data['service_name'] = $params['service_name'];
        if (isset($params['per_page']))
            $posted_data['paginate'] = $params['per_page'];
        
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
   
        $validator = Validator::make($request_data, [
            'service_title'       => 'required',
            'service_price'       => 'required',
            'service_category'    => 'required',
            'service_location'    => 'required',
            'service_lat'         => 'required',
            'service_long'        => 'required',
            'service_description' => 'required',
            'service_contact'     => 'required',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
        
        $img_data = array();
        if (isset($request->service_image)) {
            
            $allowedfileExtension = ['jpg','jpeg','png'];
            $extension = $request->service_image->getClientOriginalExtension();

            $check = in_array($extension, $allowedfileExtension);
            if($check) {
                $response = upload_files_to_storage($request, $request->service_image, 'service_image');

                if( isset($response['action']) && $response['action'] == true ) {
                    
                    $img_data['file_name'] = isset($response['file_name']) ? $response['file_name'] : "";
                    $img_data['file_path'] = isset($response['file_path']) ? $response['file_path'] : "";
                }
            }
            else {
                return response()->json(['invalid_file_format'], 422);
            }
        }
        else {
            return $this->sendError('Error.', ['error'=>'Service Image is not found']);
        }

        $category = Service::saveUpdateService([
            'service_title'       => $request_data['service_title'],
            'service_price'       => $request_data['service_price'],
            'service_category'    => $request_data['service_category'],
            'service_location'    => $request_data['service_location'],
            'service_lat'         => $request_data['service_lat'],
            'service_long'        => $request_data['service_long'],
            'service_description' => $request_data['service_description'],
            'service_contact'     => $request_data['service_contact'],
            'service_img'         => $img_data['file_path'],
        ]);

        if ( isset($category->id) )
            return $this->sendResponse($category, 'Service is successfully added.');
        else
            return $this->sendError('Not Found.', ['error'=>'Somthing went wrong during query']);
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
            return $this->sendError('Service not found.');
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
         
        $post_data = array();
        $post_data['detail'] = true;
        $post_data['id'] = isset($id) ? $id : 0;
        $service_record = Service::getServices($post_data);
        if(!$service_record){
            return $this->sendError('This Service cannot found in database');
        }
        
        // if( isset($request_data['category_type']) && $request_data['category_type'] != 1 && $request_data['category_type'] != 2 ){
        //     $error_message['category_type'] = 'You entered the invalid category type.';
        //     return $this->sendError('Validation Error.', $error_message);
        // }

        $img_data = array();
        if (isset($request->service_image)) {
            
            $allowedfileExtension = ['jpg','jpeg','png'];
            $extension = $request->service_image->getClientOriginalExtension();

            $check = in_array($extension, $allowedfileExtension);
            if($check) {
                
                if (isset($service_record->service_img) && $service_record->service_img != '')
                    $res = delete_files_from_storage($service_record->service_img);

                if ($res['action']) {
                    $response = upload_files_to_storage($request, $request->service_image, 'service_image');
                    if( isset($response['action']) && $response['action'] == true ) {
                        $img_data['file_name'] = isset($response['file_name']) ? $response['file_name'] : "";
                        $img_data['file_path'] = isset($response['file_path']) ? $response['file_path'] : "";
                    }
                }
                else {
                    return $this->sendError('Not Found.', ['error'=>'Somthing went wrong during image replacement.']);
                }
            }
            else {
                return response()->json(['invalid_file_format'], 422);
            }
        }

        $service = Service::saveUpdateService([
            'update_id'           => $id,
            'service_title'       => $request_data['service_title'],
            'service_price'       => $request_data['service_price'],
            'service_category'    => $request_data['service_category'],
            'service_location'    => $request_data['service_location'],
            'service_lat'         => $request_data['service_lat'],
            'service_long'        => $request_data['service_long'],
            'service_description' => $request_data['service_description'],
            'service_contact'     => $request_data['service_contact'],
            'service_img'         => $img_data['file_path'],
        ]);

        if ( isset($service->id) )
            return $this->sendResponse($service, 'Service is successfully added.');
        else
            return $this->sendError('Not Found.', ['error'=>'Somthing went wrong during query']);
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
            return $this->sendError('Service already deleted.');
        } 
    }
}