<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController as BaseController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserAssets;
use App\Models\UserAssetRequest;
use App\Models\AssetType;
use App\Models\ProductPromos;
use App\Models\ServicePromos;

class PromoController extends BaseController
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

        $choice = 'product';
        if (isset($request_data['promo_type'])) {
            if ($request_data['promo_type'] == 'product') {
                $choice = 'product';
            }
            else if ($request_data['promo_type'] == 'service') {
                $choice = 'service';
            }
        }
        else {
            $error_message['error'] = 'The promo type must not be blank.';
            return $this->sendError($error_message['error'], $error_message);
        }

        if (isset($request_data['per_page']))
            $request_data['paginate'] = $request_data['per_page'];
        
        if ($choice == 'product') {
            $model_response = ProductPromos::getProductPromos($request_data)->toArray();
            $message = count($model_response) > 0 ? 'Product promos are retrieved successfully.' : 'Product promos are not found against your query.';
        }
        else if ($choice == 'service') {
            $model_response = ServicePromos::getServicePromos($request_data)->toArray();
            $message = count($model_response) > 0 ? 'Service promos are retrieved successfully.' : 'Service promos are not found against your query.';
        }

        return $this->sendResponse($model_response, $message);
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
            'title'         => 'required',
            'banner'        => 'required',
            'description'   => 'required',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);   
        }

        $choice = false;
        if( isset($request_data['product_id']) )
            $choice = true;

        if( isset($request_data['service_id']) )
            $choice = true;

        if (!$choice) {
            $error_message['error'] = 'The product id or service id not be blank.';
            return $this->sendError($error_message['error'], $error_message);  
        }

        if (isset($request->banner)) {
            $extension = $request->banner->getClientOriginalExtension();
            $allowedfileExtension = ['jpg','jpeg','png'];
            $check = in_array($extension, $allowedfileExtension);
            if($check) {
                $response = upload_files_to_storage($request, $request->banner, 'promo_banners');
                if( isset($response['action']) && $response['action'] == true ) {
                    $request_data['banner'] = isset($response['file_name']) ? $response['file_name'] : "";
                    $request_data['banner_path'] = isset($response['file_path']) ? $response['file_path'] : "";
                }
            }
            else {
                $error_message['error'] = 'Invalid file format you can only add jpg, jpeg and png file format.';
                return $this->sendError($error_message['error'], $error_message);
            }
        }

        $message = '';
        if ( isset($request_data['product_id']) ) {
            $message = 'Product promo is successfully added.';
            $model_response = ProductPromos::saveUpdateProductPromos($request_data);
        }
        else if ( isset($request_data['service_id']) ) {
            $message = 'Service promo is successfully added.';
            $model_response = ServicePromos::saveUpdateServicePromos($request_data);
        }

        if ( isset($model_response->id) ){
            return $this->sendResponse($model_response, $message);
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
            'promo_type'        => 'required',
        ]);
 
        if($validator->fails()){
            return $this->sendError('Please fill all the required fields.', ["error"=>$validator->errors()->first()]);   
        }

        $choice = false;
        if( isset($request_data['product_id']) )
            $choice = true;

        if( isset($request_data['service_id']) )
            $choice = true;

        if (!$choice) {
            $error_message['error'] = 'The product id or service id not be blank.';
            return $this->sendError($error_message['error'], $error_message);
        }

        $data['id'] = $id;
        $data['detail'] = true;

        if ( $request_data['promo_type'] == 'product' ) {
            // $message = 'Product promo is successfully added.';
            $model_response = ProductPromos::getProductPromos($data);
        }
        else if ( $request_data['promo_type'] == 'service' ) {
            // $message = 'Service promo is successfully added.';
            $model_response = ServicePromos::getServicePromos($data);
        }

        // echo "Line no deee@"."<br>";
        // echo "<pre>";
        // print_r($model_response);
        // echo "</pre>";
        // exit("@@@@");

        if ($model_response) {
            delete_files_from_storage($model_response->banner_path);
        }

        if (isset($request->banner)) {
            
            $extension = $request->banner->getClientOriginalExtension();
            $allowedfileExtension = ['jpg','jpeg','png'];
            $check = in_array($extension, $allowedfileExtension);
            if($check) {
                $response = upload_files_to_storage($request, $request->banner, 'promo_banners');
                if( isset($response['action']) && $response['action'] == true ) {
                    $request_data['banner'] = isset($response['file_name']) ? $response['file_name'] : "";
                    $request_data['banner_path'] = isset($response['file_path']) ? $response['file_path'] : "";
                }
            }
            else {
                $error_message['error'] = 'Invalid file format you can only add jpg,jpeg and png file format.';
                return $this->sendError($error_message['error'], $error_message);
            }
        }

        $message = '';
        if ( $request_data['promo_type'] == 'product' ) {
            $message = 'Product promo is successfully updated.';
            $model_response = ProductPromos::saveUpdateProductPromos($request_data);
        }
        else if ( $request_data['promo_type'] == 'service' ) {
            $message = 'Service promo is successfully updated.';
            $model_response = ServicePromos::saveUpdateServicePromos($request_data);
        }

        if ( isset($model_response->id) ){
            return $this->sendResponse($model_response, $message);
        }else{
            $error_message['error'] = 'Somthing went wrong during data posting.';
            return $this->sendError($error_message['error'], $error_message);
        }

        // if( isset($request_data['asset_mimetype']) && $request_data['asset_mimetype'] != 1 && $request_data['asset_mimetype'] != 2 ){
        //     $error_message['error'] = 'You entered a invalid asset type.';
        //     return $this->sendError($error_message['error'], $error_message);  
        // }

        // exit('aall okaa');
        

        // if( isset($request_data['asset_mimetype']) && $request_data['asset_mimetype'] == 1 ) {
        //     $allowedfileExtension = ['pdf','docx', 'docs'];
        // }
        // else if( isset($request_data['asset_mimetype']) && $request_data['asset_mimetype'] == 2 ) {
        //     $allowedfileExtension = ['jpg','jpeg','png'];
        // }

        // $posted_data = array();
        // $posted_data['id'] = $id;
        // $posted_data['detail'] = true;
        // $model = UserAssets::getUserAssets($posted_data);

       

        // $request_data['asset_type'] = $request_data['asset_category'];
        // $request_data['asset_status'] = 0;

        // $model_response = UserAssets::saveUpdateUserAssets($request_data);

        // if ( isset($model_response->id) ){
        //     return $this->sendResponse($model_response, 'User asset is successfully updated.');
        // }else{
        //     $error_message['error'] = 'Somthing went wrong during data posting.';
        //     return $this->sendError($error_message['error'], $error_message);  
        // }
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
}