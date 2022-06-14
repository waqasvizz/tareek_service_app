<?php

if (! function_exists('is_image_exist')) {
    function is_image_exist($image_path = '') {
        
        $base_url = public_path().'/'.$image_path;
        $asset_url = config('app.url').'/';
        $image_url = $asset_url.$image_path;


        $default_img_name = 'default-thumbnail.jpg';
        
        if ( $image_path == '' || is_null($image_path) )
            return config('app.url').'/storage/default-images/'.$default_img_name;
        else if (file_exists($_SERVER['DOCUMENT_ROOT'].'/'.$image_path))
            return config('app.url').'/'.$image_path;
        else if (file_exists($base_url))
            return config('app.url').'/'.$image_path;
        else
            return config('app.url').'/storage/default-images/'.$default_img_name;
    }
}

if (! function_exists('get_gayment_name')) {
    function get_gayment_name($id = 0) {
        
        if ( $id == 1)
            return 'Cash on Delivery';
        else if ( $id == 2)
            return 'Paypal';
        else if ( $id == 3)
            return 'Stripe';
        else
            return 'Unknown';
    }
}

if (! function_exists('get_status_name')) {
    function get_status_name($id = 0) {
        
        if ( $id == 1)
            return 'Pending';
        else if ( $id == 2)
            return 'In-Progress';
        else if ( $id == 3)
            return 'Complete';
        else
            return 'Unknown';
    }
}

if (! function_exists('upload_files_to_storage')) {
    function upload_files_to_storage($request, $file_param, $path)
    {
        $response = array();

        $file_name = time().'_'.$file_param->getClientOriginalName();
        $file_name = preg_replace('/\s+/', '_', $file_name);
        $file_request = $file_param->storeAs($path, $file_name, ['disk' => 'public']);
        // $file_path = 'storage/'.$path.'/'.$file_name;
        $file_path = $path.'/'.$file_name;

        if( $file_param->isValid() )
            return $response = array(
                'action'        => true,
                'message'       => 'Requested file is uploaded successfully.',
                'file_name'     => $file_name,
                'file_path'     => $file_path
            );
        else
            return $response = array(
                'action'        => false,
                'message'       => 'Something went wrong during uploading.'
            );    
    }
}

if (! function_exists('delete_files_from_storage')) {
    function delete_files_from_storage($file)
    {
        if( $file != "" ) {
            // File::delete(public_path('upload/bio.png'));
            $process = File::delete(public_path('storage').'/'.$file);
            // $process = File::delete(storage_path().'/'.$file);

            if ( $process )
                return $response = array('action' => true, 'message'   => 'Requested file is delete successfully.');
            else
                return $response = array('action' => false, 'message'   => 'Requested file is not exist.', 'file' => public_path('storage').'/'.$file);
        }
        else 
            return $response = array('action' => false, 'message'   => 'There is no file available to delete.');
    }
}

if (! function_exists('isApiRequest')) {
    function isApiRequest($request)
    {
        $isApiRequest = false;
        if( $request->is('api/*')){
            $isApiRequest = true;
        }
        return $isApiRequest;
    }
}

if (! function_exists('array_flatten')) {
    function array_flatten($array) { 
        if (!is_array($array)) { 
            return FALSE; 
        } 
        $result = array(); 
        foreach ($array as $key => $value) { 
            if (is_array($value)) { 
                $result = array_merge($result, array_flatten($value)); 
            } 
            else { 
                $result[$key] = $value; 
            } 
        } 
        return $result; 
    } 
}

if (! function_exists('multidimentional_array_flatten')) {
    function multidimentional_array_flatten($array, $key) { 
        $unique_ids = array_unique(array_map(
            function ($i) use ($key) {
                return $i[$key];
            }, $array)
        );
    
        return $unique_ids;
    }
}

if (! function_exists('split_metadata_strings')) {
    function split_metadata_strings($string = "") {
        $final_result = array();

        foreach (explode('&', $string) as $piece) {
            $result = array();
            $result = explode('=', $piece);
            $final_result[$result[0]] = $result[1];
        }
    
        return $final_result;
    }
}

/* LAST UPDATED ON 19 May, 2022 */
if (! function_exists('getSpecificColumnsFromArray')) {
    function getSpecificColumnsFromArray(array $array, $keys)
    {
        $array = json_decode(json_encode($array), true);

        if (!is_array($keys)) $keys = [$keys];
        $filter = function($k) use ($keys){
            return in_array($k,$keys);
        };
        return array_map(function ($el) use ($keys,$filter) {
            return array_filter($el, $filter, ARRAY_FILTER_USE_KEY );
        }, $array);
    }
}


if (! function_exists('decodeShortCodesTemplate')) {
    // function decodeShortCodesTemplate($html, $email_message_id='', $user_id='', $replace_ary = array()) {
    function decodeShortCodesTemplate($posted_data = array()) {
        
        $email_subject = isset($posted_data['subject']) ? $posted_data['subject'] : '';
        $email_body = isset($posted_data['body']) ? $posted_data['body'] : '';
        $email_message_id = isset($posted_data['email_message_id']) ? $posted_data['email_message_id'] : 0;
        $user_id = isset($posted_data['user_id']) ? $posted_data['user_id'] : 0;
        $sender_id = isset($posted_data['sender_id']) ? $posted_data['sender_id'] : 0;
        $receiver_id = isset($posted_data['receiver_id']) ? $posted_data['receiver_id'] : 0;
        $new_password = isset($posted_data['new_password']) ? $posted_data['new_password'] : '[Something went wrong with server. Please request again]';
        $verification_code = isset($posted_data['email_verification_url']) ? $posted_data['email_verification_url'] : '[Something went wrong with server. Please request again]';
        
        $ShortCodesObj = new \App\Models\ShortCodes;
        $UserObj = new \App\Models\User;

        $all_codes = $ShortCodesObj->getShortCodes();
        
        // echo "Line no beforeeee@"."<br>";
        // echo "<pre>";
        // print_r($email_html);
        // echo "</pre>";

        $user_data = $UserObj->getUser(['id' => $user_id, 'without_with' => true, 'detail' => true]);
        foreach ($all_codes as $key => $code ) {

            if ($code['title'] == '[user_name]') {
                $search = $code['title'];
                $replace = $user_data ? ucwords($user_data->name) : 'User';
                $email_subject = stripcslashes(str_replace($search, $replace, $email_subject));
                $email_body = stripcslashes(str_replace($search, $replace, $email_body));
            }
            else if ($code['title'] == '[user_email]') {
                $search = $code['title'];
                $replace = $user_data ? ucwords($user_data->email) : 'your email';
                $email_subject = stripcslashes(str_replace($search, $replace, $email_subject));
                $email_body = stripcslashes(str_replace($search, $replace, $email_body));
            }
            else if ($code['title'] == '[sender_name]') {
                $search = $code['title'];
                $data = $UserObj->getUser(['id' => $sender_id, 'without_with' => true, 'detail' => true]);
                $replace = $data ? ucwords($data->name) : 'User';
                $email_subject = stripcslashes(str_replace($search, $replace, $email_subject));
                $email_body = stripcslashes(str_replace($search, $replace, $email_body));
            }
            else if ($code['title'] == '[receiver_name]') {
                $search = $code['title'];
                $data = $UserObj->getUser(['id' => $receiver_id, 'without_with' => true, 'detail' => true]);
                $replace = $data ? ucwords($data->name) : 'User';
                $email_subject = stripcslashes(str_replace($search, $replace, $email_subject));
                $email_body = stripcslashes(str_replace($search, $replace, $email_body));
            }
            else if ($code['title'] == '[app_name]') {
                $search = $code['title'];
                $replace = config('app.name') ? config('app.name') : 'Application';
                $email_subject = stripcslashes(str_replace($search, $replace, $email_subject));
                $email_body = stripcslashes(str_replace($search, $replace, $email_body));
            }
            else if ($code['title'] == '[logo_url]') {
                $search = $code['title'];
                $replace = asset("storage/default-images/app-logo-email.png");
                $email_subject = stripcslashes(str_replace($search, $replace, $email_subject));
                $email_body = stripcslashes(str_replace($search, $replace, $email_body));
            }
            else if ($code['title'] == '[email_verification_url]') {
                $token = $verification_code;
                $search = $code['title'];
                $replace = route('email_verify', $token);
                $email_subject = stripcslashes(str_replace($search, $replace, $email_subject));
                $email_body = stripcslashes(str_replace($search, $replace, $email_body));
            }
            else if ($code['title'] == '[email_background]') {
                $search = $code['title'];
                $replace = asset("storage/default-images/email-background.png");
                $email_subject = stripcslashes(str_replace($search, $replace, $email_subject));
                $email_body = stripcslashes(str_replace($search, $replace, $email_body));
            }
            else if ($code['title'] == '[new_password]') {
                $search = $code['title'];
                $replace = $new_password;
                $email_subject = stripcslashes(str_replace($search, $replace, $email_subject));
                $email_body = stripcslashes(str_replace($search, $replace, $email_body));
            }
        }
        
        // echo "Line no afterrrr@"."<br>";
        // echo "<pre>";
        // print_r($email_html);
        // echo "</pre>";
        // exit("@@@@");

        // $SettingObj = new Setting();
        return $response = [
            'email_subject' => $email_subject,
            'email_body' => $email_body
        ];
    }
}