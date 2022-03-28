<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController as BaseController;
use Validator;
use App\Models\Category;
use App\Models\StorageAssets;
// use App\Http\Resources\Service as ServiceResource;

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

        $posted_data =  array();
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
        
        // $posted_data['count'] = true;
        // $count = Category::getCategories($posted_data);
    
        // return $this->sendResponse($services, 'Categories retrieved successfully.', $count);
        // return $this->sendResponse(CategoryResource::collection($services), 'Categories retrieved successfully.', $count);
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
            'category_title'    => 'required',
            'category_type'     => 'required',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }

        if( isset($request_data['category_type']) && $request_data['category_type'] != 1 && $request_data['category_type'] != 2 ){
            $error_message['category_type'] = 'You entered the invalid category type.';
            return $this->sendError('Validation Error.', $error_message);
        }

        $img_data = array();
        if (isset($request->category_image)) {
            
            $allowedfileExtension = ['jpg','jpeg','png'];
            $extension = $request->category_image->getClientOriginalExtension();

            $check = in_array($extension, $allowedfileExtension);
            if($check) {
                $response = upload_files_to_storage($request, $request->category_image, 'other_assets');

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
            return $this->sendError('Error.', ['error'=>'Category Image is not found']);
        }

        $category = Category::saveUpdateCategory([
            'category_title'    => $request_data['category_title'],
            'category_type'     => $request_data['category_type'],
            'category_image'    => $img_data['file_path']
        ]);

        if ( isset($category->id) )
            return $this->sendResponse($category, 'Category is successfully added.');
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
        $service = Category::find($id);
  
        if (is_null($service)) {
            return $this->sendError('Category not found.');
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
            return $this->sendError('This Category cannot found in database');
        }
        
        if( isset($request_data['category_type']) && $request_data['category_type'] != 1 && $request_data['category_type'] != 2 ){
            $error_message['category_type'] = 'You entered the invalid category type.';
            return $this->sendError('Validation Error.', $error_message);
        }

        $img_data = array();
        if (isset($request->category_image)) {
            
            $allowedfileExtension = ['jpg','jpeg','png'];
            $extension = $request->category_image->getClientOriginalExtension();

            $check = in_array($extension, $allowedfileExtension);
            if($check) {
                
                if (isset($category_record->category_image) && $category_record->category_image != '')
                    $res = delete_files_from_storage($category_record->category_image);

                if ($res['action']) {
                    $response = upload_files_to_storage($request, $request->category_image, 'other_assets');
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

        $category = Category::saveUpdateCategory([
            'update_id'         => $id,
            'category_title'    => $request_data['category_title'],
            'category_type'     => $request_data['category_type'],
            'category_image'    => $img_data['file_path']
        ]);

        if ( isset($category->id) )
            return $this->sendResponse($category, 'Category is successfully updated.');
        else
            return $this->sendError('Not Found.', ['error'=>'Somthing went wrong during query']);
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
        if(Category::find($id)) {
            $response = Category::deleteCategory($id);
            return $this->sendResponse($response, 'Category deleted successfully.');
        }
        else {
            return $this->sendError('Category already deleted / Not found in database.');
        }
    }
}