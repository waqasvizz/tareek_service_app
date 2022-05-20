<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController as BaseController;
use App\Models\AssetType;
use App\Models\User;
use App\Models\Notification;
use App\Models\FCM_Token;

class AssetTypesController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $params = $request->all();

        $posted_data =  $params;
        $posted_data['paginate'] = 10;

        if (isset($params['asset_id']))
            $posted_data['id'] = $params['asset_id'];
        if (isset($params['asset_type'])) {
            if ($params['asset_type'] == 1)
                $params['asset_type'] = 'Document';
            else if ($params['asset_type'] == 2)
                $params['asset_type'] = 'Image';
                
            $posted_data['type'] = $params['asset_type'];
        }
        if (isset($params['per_page']))
            $posted_data['paginate'] = $params['per_page'];
        
        $services = AssetType::getAssetType($posted_data);
        $message = count($services) > 0 ? 'AssetType retrieved successfully.' : 'AssetType not found against your query.';

        return $this->sendResponse($services, $message);
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
            'asset_title'     => 'required',
            'asset_type'      => 'required',
            'asset_sides'     => 'required',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);   
        }

        if( isset($request_data['asset_type']) && $request_data['asset_type'] != 1 && $request_data['asset_type'] != 2 ){
            $error_message['error'] = 'You entered a invalid asset type.';
            return $this->sendError($error_message['error'], $error_message);  
        }

        if( isset($request_data['asset_sides']) && $request_data['asset_sides'] != 1 && $request_data['asset_sides'] != 2 ){
            $error_message['error'] = 'You entered a invalid asset sides.';
            return $this->sendError($error_message['error'], $error_message);
        }

        $posted_data = array();
        $posted_data['title'] = $request_data['asset_title'];
        $posted_data['type'] = $request_data['asset_type'];
        $posted_data['sides'] = $request_data['asset_type'] == 1 ? 1 : $request_data['asset_sides'];

        $model_response = AssetType::saveUpdateAssetType($posted_data);

        if ( isset($model_response->id) ){
            return $this->sendResponse($model_response, 'Asset type is successfully added.');
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
        $service = AssetType::find($id);
  
        if (is_null($service)) {
            $error_message['error'] = 'AssetType not found.';
            return $this->sendError($error_message['error'], $error_message);  
        }
   
        return $this->sendResponse($service, 'AssetType retrieved successfully.');
        // return $this->sendResponse(new AssetTypeResource($service), 'AssetType retrieved successfully.');
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function update(Request $request, AssetType $service)
    public function update(Request $request, $id)
    {
        $request_data = $request->all(); 
        $request_data['update_id'] = $id;
   
        $validator = \Validator::make($request_data, [
            'update_id' => 'exists:categories,id',
            'category_title'    => 'required',
            'category_type'     => 'required',
            'commission'     => 'required',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);   
        }
        
        if( isset($request_data['category_type']) && $request_data['category_type'] != 1 && $request_data['category_type'] != 2 ){
            $error_message['error'] = 'You entered the invalid category type.';
            return $this->sendError($error_message['error'], $error_message);  
        }
        
        if (isset($request->category_image)) {
            
            $allowedfileExtension = ['jpg','jpeg','png'];
            $extension = $request->category_image->getClientOriginalExtension();

            $check = in_array($extension, $allowedfileExtension);
            if($check) {
                
                $res['action'] = true;
                if (isset($category_record->category_image) && $category_record->category_image != '')
                    $res = delete_files_from_storage($category_record->category_image);

                if ($res['action']) {
                    $response = upload_files_to_storage($request, $request->category_image, 'other_assets');
                    $request_data['category_image'] = $response['file_path'];
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

        $category = AssetType::saveUpdateAssetType($request_data);

        if ( isset($category->id) ){
            return $this->sendResponse($category, 'AssetType is successfully updated.');
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
    // public function destroy(AssetType $service)
    public function destroy($id)
    {
        $category_rec = AssetType::find($id);
        if($category_rec) {
            $filepath = $category_rec->category_image;
            delete_files_from_storage($filepath);
            $response = AssetType::deleteAssetType($id);
            return $this->sendResponse([], 'AssetType deleted successfully.');
        }
        else {
            $error_message['error'] = 'AssetType already deleted / Not found in database.';
            return $this->sendError($error_message['error'], $error_message);  
        }
    }
}