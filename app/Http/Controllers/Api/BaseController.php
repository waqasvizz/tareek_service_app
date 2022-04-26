<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use Crypt;
use Auth;
use DB;
use Stripe;
use Storage;

class BaseController extends Controller
{

    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendResponse($result = array(), $message, $count = 0)
    {
    	$response = [
            'success' => true,
            'records'    => $result,
            'message' => $message,
            'count'    => $count,
        ];
        return response()->json($response, 200);
    }


    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendError($error, $errorMessages = array(), $code = 404)
    {
    	$response = [
            'success' => false,
            'message' => $errorMessages['error'],
        ];

        // if(!empty($errorMessages)){
            // $response['error_messages'] = $errorMessages;
        // }
        
        return response()->json($response, $code);
    }
}