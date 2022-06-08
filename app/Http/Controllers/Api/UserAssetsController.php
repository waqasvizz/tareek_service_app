<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController as BaseController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserAssets;
use App\Models\UserAssetRequest;
use App\Models\AssetType;

class UserAssetsController extends BaseController
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
        if (isset($request_data['id']))
            $request_data['id'] = $request_data['id'];
        if (isset($request_data['user_id']))
            $request_data['user_id'] = $request_data['user_id'];
        if (isset($request_data['asset_category']))
            $request_data['asset_type'] = $request_data['asset_category'];
        if (isset($request_data['per_page']))
            $request_data['paginate'] = $request_data['per_page'];
        
        $user_assets = UserAssets::getUserAssets($request_data)->toArray();
        $message = count($user_assets) > 0 ? 'User assets are retrieved successfully.' : 'User assets are not found against your query.';

        return $this->sendResponse($user_assets, $message);
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
        $allowedfileExtension = [];
        $validator = \Validator::make($request_data, [
            'user_id'           => 'required',
            'asset_category'    => 'required',
            'asset_mimetype'    => 'required',
            'asset_file'        => 'required',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);   
        }

        if( isset($request_data['asset_mimetype']) && $request_data['asset_mimetype'] != 1 && $request_data['asset_mimetype'] != 2 ){
            $error_message['error'] = 'You entered a invalid asset type.';
            return $this->sendError($error_message['error'], $error_message);  
        }

        if( isset($request_data['asset_mimetype']) && $request_data['asset_mimetype'] == 1 ) {
            $allowedfileExtension = ['pdf','docx', 'docs','jpg','jpeg','png'];
            $request_data['asset_view'] = 1;
        }
        else if( isset($request_data['asset_mimetype']) && $request_data['asset_mimetype'] == 2 ) {
            $allowedfileExtension = ['jpg','jpeg','png'];
        }

        if (isset($request->asset_file)) {
            $extension = strtolower($request->asset_file->getClientOriginalExtension());

            $check = in_array($extension, $allowedfileExtension);
            if($check) {
                $response = upload_files_to_storage($request, $request->asset_file, 'user_assets');
                if( isset($response['action']) && $response['action'] == true ) {
                    $request_data['filename'] = isset($response['file_name']) ? $response['file_name'] : "";
                    $request_data['filepath'] = isset($response['file_path']) ? $response['file_path'] : "";
                    $request_data['mimetypes'] = $request->asset_file->getClientMimeType();
                }
            }
            else {
                if( isset($request_data['asset_mimetype']) && $request_data['asset_mimetype'] == 1 )
                    $error_message['error'] = 'Invalid file format you can only pdf, docx, docs, jpg, jpeg and png file format.';
                else if( isset($request_data['asset_mimetype']) && $request_data['asset_mimetype'] == 2 )
                    $error_message['error'] = 'Invalid file format you can only add jpg, jpeg and png file format.';

                return $this->sendError($error_message['error'], $error_message);
            }
        }

        $request_data['asset_type'] = $request_data['asset_category'];
        $request_data['asset_status'] = 0;

        $model_response = UserAssets::saveUpdateUserAssets($request_data);

        if ( isset($model_response->id) ){
            return $this->sendResponse($model_response, 'User asset is successfully added.');
        }else{
            $error_message['error'] = 'Somthing went wrong during data posting.';
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
        $user_week_day = UserAssets::find($id);
  
        if (is_null($user_week_day)) {
            $error_message['error'] = 'User week day not found.';
            return $this->sendError($error_message['error'], $error_message);  
        }
   
        return $this->sendResponse($user_week_day, 'User week day retrieved successfully.');
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
            'update_id'         => 'required|exists:user_assets,id',
            'user_id'           => 'required',
            'asset_category'    => 'required',
            'asset_mimetype'    => 'required',
            'asset_file'        => 'required',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);   
        }

        if( isset($request_data['asset_mimetype']) && $request_data['asset_mimetype'] != 1 && $request_data['asset_mimetype'] != 2 ){
            $error_message['error'] = 'You entered a invalid asset type.';
            return $this->sendError($error_message['error'], $error_message);  
        }

        if( isset($request_data['asset_mimetype']) && $request_data['asset_mimetype'] == 1 ) {
            $allowedfileExtension = ['pdf','docx', 'docs'];
        }
        else if( isset($request_data['asset_mimetype']) && $request_data['asset_mimetype'] == 2 ) {
            $allowedfileExtension = ['jpg','jpeg','png'];
        }

        $posted_data = array();
        $posted_data['id'] = $id;
        $posted_data['detail'] = true;
        $model = UserAssets::getUserAssets($posted_data);

        $response = delete_files_from_storage($model->filepath);

        if (isset($request->asset_file)) {
            
            $extension = strtolower($request->asset_file->getClientOriginalExtension());

            $check = in_array($extension, $allowedfileExtension);
            if($check) {
                $response = upload_files_to_storage($request, $request->asset_file, 'user_assets');
                if( isset($response['action']) && $response['action'] == true ) {
                    $request_data['filename'] = isset($response['file_name']) ? $response['file_name'] : "";
                    $request_data['filepath'] = isset($response['file_path']) ? $response['file_path'] : "";
                    $request_data['mimetypes'] = $request->asset_file->getClientMimeType();
                }
            }
            else {
                if( isset($request_data['asset_mimetype']) && $request_data['asset_mimetype'] == 1 )
                    $error_message['error'] = 'Invalid file format you can only pdf, docx and docs file format.';
                else if( isset($request_data['asset_mimetype']) && $request_data['asset_mimetype'] == 2 )
                    $error_message['error'] = 'Invalid file format you can only add jpg,jpeg and png file format.';

                return $this->sendError($error_message['error'], $error_message);
            }
        }

        $request_data['asset_type'] = $request_data['asset_category'];
        $request_data['asset_status'] = 0;

        $model_response = UserAssets::saveUpdateUserAssets($request_data);

        if ( isset($model_response->id) ){
            return $this->sendResponse($model_response, 'User asset is successfully updated.');
        }else{
            $error_message['error'] = 'Somthing went wrong during data posting.';
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
        $posted_data = array();
        $posted_data['id'] = $id;
        $posted_data['detail'] = true;
        $model = UserAssets::getUserAssets($posted_data);
        $response = delete_files_from_storage(isset($model->filepath) ?? '');

        $response = UserAssets::deleteUserAssets($id);
        if($response) {
            return $this->sendResponse([], 'User asset is successfully deleted.');
        }
        else {
            $error_message['error'] = 'User asset is already deleted / Not found in database.';
            return $this->sendError($error_message['error'], $error_message);  
        }
    }

    public function get_request(Request $request)
    {
        $request_data = $request->all();
   
        $validator = \Validator::make($request_data, [
            'user_id'       => 'exists:users,id',
            'officer_id'    => 'exists:users,id',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);   
        }

        $posted_data = array();
        if ( isset($request_data['user_id']) && $request_data['user_id'] ) {
            $posted_data['user_id'] = $request_data['user_id'];
            $posted_data['detail'] = true;
        }
        if ( isset($request_data['officer_id']) && $request_data['officer_id'] )
            $posted_data['request_by'] = $request_data['officer_id'];
        if ( isset($request_data['request_status']) && $request_data['request_status'] )
            $posted_data['request_status'] = $request_data['request_status'];
            
        $model = UserAssetRequest::getUserAssetRequest($posted_data);

        if ( !empty($model) ){
            $model = $model->toArray();
            if ( count($model) > 0 ){
                return $this->sendResponse($model, 'The asset request is successfully found.');
            }
            else {
                $error_message['error'] = 'The asset request record is not found.';
                return $this->sendError($error_message['error'], $error_message);
            }
        }
        else {
            $error_message['error'] = 'The asset request record is not found.';
            return $this->sendError($error_message['error'], $error_message);
        }

    }

    public function request(Request $request)
    {
        $request_data = $request->all();
        $validator = \Validator::make($request_data, [
            'user_id'       => 'required|exists:users,id',
            'request_by'    => 'required|exists:users,id',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);   
        }

        $posted_data = array();
        $posted_data['user_id'] = $request_data['user_id'];
        $posted_data['request_by'] = $request_data['request_by'];
        $posted_data['detail'] = true;
        $model = UserAssetRequest::getUserAssetRequest($posted_data);

        if ( !isset($model->id) ){

            $posted_data = array();
            $posted_data['user_id'] = $request_data['user_id'];
            $posted_data['request_by'] = $request_data['request_by'];
            $posted_data['status'] = 1;
            $model = UserAssetRequest::saveUpdateUserAssetRequest($posted_data);

            if ( isset($model->id) ){
                return $this->sendResponse($model, 'Your request has been successfully posted.');
            }
            else {
                $error_message['error'] = 'Something went wrong during posting data.';
                return $this->sendError($error_message['error'], $error_message);  
            }
        }
        else {
            $error_message['error'] = 'Your request is already posted and in progress';
            return $this->sendError($error_message['error'], $error_message);  
        }
    }

    public function approve(Request $request)
    {
        $request_data = $request->all();
        $validator = \Validator::make($request_data, [
            'asset_id'        => 'required|exists:user_assets,id',
            'asset_status'    => 'required',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);   
        }

        $posted_data = array();
        $posted_data['update_id'] = $request_data['asset_id'];
        $posted_data['asset_status'] = $request_data['asset_status'];
        $model = UserAssets::saveUpdateUserAssets($posted_data);

        if ( isset($model->id) ){
            return $this->sendResponse($model, 'The document is successfully approved.');
        }
        else {
            $error_message['error'] = 'Something went wrong during posting data.';
            return $this->sendError($error_message['error'], $error_message);  
        }
    }

    public function request_update(Request $request)
    {
        $request_data = $request->all();
        $validator = \Validator::make($request_data, [
            'request_id'          => 'required|exists:user_assets_requests,id',
            'request_status'      => 'required',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);   
        }

        $posted_data = array();
        $posted_data['update_id'] = $request_data['request_id'];
        $posted_data['status'] = $request_data['request_status'];
        
        $model = UserAssetRequest::saveUpdateUserAssetRequest($posted_data);

        if ( isset($model->id) ){
            return $this->sendResponse($model, 'The assets request is successfully updated.');
        }
        else {
            $error_message['error'] = 'Something went wrong during posting data.';
            return $this->sendError($error_message['error'], $error_message);  
        }
    }
}