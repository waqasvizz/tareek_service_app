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
            'update_id'       => 'required|exists:user_assets_categories,id',
            'asset_title'     => 'nullable',
            'asset_type'      => 'in:1,2',
            'asset_sides'     => 'in:1,2',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);   
        }

        $posted_data = array();
        $posted_data['update_id'] = $id;
        if (isset($request_data['asset_title']) && $request_data['asset_title'] != '')
            $posted_data['title'] = $request_data['asset_title'];
        if (isset($request_data['asset_type']) && $request_data['asset_type'] != '')
            $posted_data['type'] = $request_data['asset_type'];
        if (isset($request_data['asset_sides']) && $request_data['asset_sides'] != '')
            $posted_data['sides'] = $request_data['asset_sides'];

        $model_response = AssetType::saveUpdateAssetType($posted_data);

        if ( isset($model_response->id) ){
            return $this->sendResponse($model_response, 'Asset type is successfully updated.');
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
        if ($id != 0) {
            $request_data['asset_id'] = $id;
            $validator = \Validator::make($request_data, [
                'asset_id'    => 'required|exists:user_assets_categories,id',
            ],[
                'asset_id.exists' => 'You have selected a invalid asset type id.'
            ]);
     
            if($validator->fails()){
                return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);
            }

            $response = AssetType::deleteAssetType($id);
    
            if($response)
                return $this->sendResponse([], 'The asset type is successfully deleted.');
            else {
                $error_message['error'] = 'Something went wrong during deletion.';
                return $this->sendError($error_message['error'], $error_message);  
            }
        }
        else {
            $error_message['error'] = 'The requested data is already deleted / not found in database.';
            return $this->sendError($error_message['error'], $error_message);
        }
    }
}