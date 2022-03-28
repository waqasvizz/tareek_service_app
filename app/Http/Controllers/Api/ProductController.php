<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController as BaseController;
use Validator;
use App\Models\Product;
// use App\Http\Resources\Product as ProductResource;

class ProductController extends BaseController
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

        if (isset($params['product_id']))
            $posted_data['id'] = $params['product_id'];
        if (isset($params['product_name']))
            $posted_data['product_name'] = $params['product_name'];
        if (isset($params['per_page']))
            $posted_data['paginate'] = $params['per_page'];
        
        $products = Product::getProducts($posted_data);
        $message = count($products) > 0 ? 'Products retrieved successfully.' : 'Products not found against your query.';

        return $this->sendResponse($products, $message);
        
        // $posted_data['count'] = true;
        // $count = Product::getProducts($posted_data);
    
        // return $this->sendResponse($products, 'Products retrieved successfully.', $count);
        // return $this->sendResponse(ProductResource::collection($products), 'Products retrieved successfully.', $count);
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
            'product_title'       => 'required',
            'product_price'       => 'required',
            'product_category'    => 'required',
            'product_location'    => 'required',
            'product_lat'         => 'required',
            'product_long'        => 'required',
            'product_description' => 'required',
            'product_contact'     => 'required',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
        
        $img_data = array();
        if (isset($request->product_image)) {
            
            $allowedfileExtension = ['jpg','jpeg','png'];
            $extension = $request->product_image->getClientOriginalExtension();

            $check = in_array($extension, $allowedfileExtension);
            if($check) {
                $response = upload_files_to_storage($request, $request->product_image, 'product_image');

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
            return $this->sendError('Error.', ['error'=>'Product Image is not found']);
        }

        $category = Product::saveUpdateProduct([
            'product_title'       => $request_data['product_title'],
            'product_price'       => $request_data['product_price'],
            'product_category'    => $request_data['product_category'],
            'product_location'    => $request_data['product_location'],
            'product_lat'         => $request_data['product_lat'],
            'product_long'        => $request_data['product_long'],
            'product_description' => $request_data['product_description'],
            'product_contact'     => $request_data['product_contact'],
            'product_img'         => $img_data['file_path'],
        ]);

        if ( isset($category->id) )
            return $this->sendResponse($category, 'Product is successfully added.');
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
        $product = Product::find($id);
  
        if (is_null($product)) {
            return $this->sendError('Product not found.');
        }
   
        return $this->sendResponse($product, 'Product retrieved successfully.');
        // return $this->sendResponse(new ProductResource($product), 'Product retrieved successfully.');
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function update(Request $request, Product $product)
    public function update(Request $request, $id)
    {
        $request_data = $request->all();
         
        $post_data = array();
        $post_data['detail'] = true;
        $post_data['id'] = isset($id) ? $id : 0;
        $product_record = Product::getProducts($post_data);
        if(!$product_record){
            return $this->sendError('This Product cannot found in database');
        }

        // if( isset($request_data['category_type']) && $request_data['category_type'] != 1 && $request_data['category_type'] != 2 ){
        //     $error_message['category_type'] = 'You entered the invalid category type.';
        //     return $this->sendError('Validation Error.', $error_message);
        // }

        $img_data = array();
        if (isset($request->product_image)) {
            
            $allowedfileExtension = ['jpg','jpeg','png'];
            $extension = $request->product_image->getClientOriginalExtension();

            $check = in_array($extension, $allowedfileExtension);
            if($check) {
                
                if (isset($product_record->product_img) && $product_record->product_img != '')
                    $res = delete_files_from_storage($product_record->product_img);

                if ($res['action']) {
                    $response = upload_files_to_storage($request, $request->product_image, 'product_image');
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

        $category = Product::saveUpdateProduct([
            'update_id'           => $id,
            'product_title'       => $request_data['product_title'],
            'product_price'       => $request_data['product_price'],
            'product_category'    => $request_data['product_category'],
            'product_location'    => $request_data['product_location'],
            'product_lat'         => $request_data['product_lat'],
            'product_long'        => $request_data['product_long'],
            'product_description' => $request_data['product_description'],
            'product_contact'     => $request_data['product_contact'],
            'product_img'         => $img_data['file_path'],
        ]);

        if ( isset($category->id) )
            return $this->sendResponse($category, 'Product is successfully added.');
        else
            return $this->sendError('Not Found.', ['error'=>'Somthing went wrong during query']);
    }
   
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function destroy(Product $product)
    public function destroy($id)
    {
        if(Product::find($id)){
            $filepath = Product::find($id)->product_img;
            delete_files_from_storage($filepath);
            Product::deleteProduct($id);
            return $this->sendResponse([], 'Product deleted successfully.');
        }else{
            return $this->sendError('Product already deleted.');
        } 
    }
}