<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController as BaseController;
use App\Models\Category;

class CategoryController extends BaseController
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

        if (isset($params['category_id']))
            $posted_data['id'] = $params['category_id'];
        if (isset($params['category_name']))
            $posted_data['category_title'] = $params['category_name'];
        if (isset($params['per_page']))
            $posted_data['paginate'] = $params['per_page'];
        
        $services = Category::getCategories($posted_data);
        $message = count($services) > 0 ? 'Categories retrieved successfully.' : 'Categories not found against your query.';

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
                $response = upload_files_to_storage($request, $request->category_image, 'other_assets');
                $request_data['category_image'] = $response['file_path'];
                if( isset($response['action']) && $response['action'] == true ) {
                    $img_data['file_name'] = isset($response['file_name']) ? $response['file_name'] : "";
                    $img_data['file_path'] = isset($response['file_path']) ? $response['file_path'] : "";
                }
            }
            else {
                $error_message['error'] = 'Invalid file format you can only add jpg,jpeg and png file format.';
                return $this->sendError($error_message['error'], $error_message);
            }
        }else {
            $error_message['error'] = 'Please Upload Category Image.';
            return $this->sendError($error_message['error'], $error_message);  
        }

        $category = Category::saveUpdateCategory($request_data);

        if ( isset($category->id) ){
            return $this->sendResponse($category, 'Category is successfully added.');
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
        $service = Category::find($id);
  
        if (is_null($service)) {
            $error_message['error'] = 'Category not found.';
            return $this->sendError($error_message['error'], $error_message);  
        }
   
        return $this->sendResponse($service, 'Category retrieved successfully.');
        // return $this->sendResponse(new CategoryResource($service), 'Category retrieved successfully.');
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function update(Request $request, Category $service)
    public function update(Request $request, $id)
    {
        $request_data = $request->all(); 
   
        $post_data = array();
        $post_data['detail'] = true;
        $post_data['id'] = isset($id) ? $id : 0;
        $category_record = Category::getCategories($post_data);
        if(!$category_record){
            $error_message['error'] = 'This Category cannot found in database.';
            return $this->sendError($error_message['error'], $error_message);  
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

        $request_data['update_id'] = $id;
        $category = Category::saveUpdateCategory($request_data);

        if ( isset($category->id) ){
            return $this->sendResponse($category, 'Category is successfully updated.');
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
    // public function destroy(Category $service)
    public function destroy($id)
    {
        $category_rec = Category::find($id);
        if($category_rec) {
            $filepath = $category_rec->category_image;
            delete_files_from_storage($filepath);
            $response = Category::deleteCategory($id);
            return $this->sendResponse([], 'Category deleted successfully.');
        }
        else {
            $error_message['error'] = 'Category already deleted / Not found in database.';
            return $this->sendError($error_message['error'], $error_message);  
        }
    }
}