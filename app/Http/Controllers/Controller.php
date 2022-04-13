<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

use App\Models\Role;
use App\Models\Service;
use App\Models\User;

use DB;
use Validator;
use Auth;
use Session;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public $RoleObj;
    public $ServiceObj;
    public $UserObj;

    public function __construct() {
        
        $this->RoleObj = new Role();
        $this->ServiceObj = new Service();
        $this->UserObj = new User();
    }

    public function firebase() {
        return view('firebase');
    }

    public function sendNotification() {
        
        $token = "fylFRv2k89Q:APA91bGYV2b5RjjCLTbUfaBzAota5bfCUGYGTAjxlGyopVcmVLn0ysPKxk5NcYxmnga201jgSpuh3xVAPxAUcAEOqN-63Dn3-zw-90H_2MRU6cG_K8iFq_ImNfjlfiiLgLAeTU2swNdO";  
        // $token = "eba-oZkml2A:APA91bFAOhAUUt0VhsJV4KBcXweZrkbsialRWDGA-ByuPZj0sLtotcXCVoAJ9HXOvE8Z0vcAl0uuHDZc1spJoqCDLzAuqx5I3MAJsaF0sp4S3tYm_PU7Y_pDo2kX5KAsJEhHTnhWg4Ub";  
        $from = "AAAA1x62L-A:APA91bHPEZuPTTVn8tWhggUur4h2_k92s4cRWIu5L9lkRgS2pHtYJKMgCIkg4UcIMui1lWcXRGStyKxjIgrlH7KXefS0CkSS8tlrR0yDWiNRUkeYsNuivIgnV2rgep6QCmQL75-QpBTd";
        $msg = array
            (
                'body'  => "Testing Testing",
                'title' => "Hi, From Raj",
                'receiver' => 'erw',
                'icon'  => "https://image.flaticon.com/icons/png/512/270/270014.png",/*Default Icon*/
                'sound' => 'Default'/*Default sound*/
            );

        $fields = array
                (
                    'to'        => $token,
                    'notification'  => $msg
                );

        $headers = array
                (
                    'Authorization: key=' . $from,
                    'Content-Type: application/json'
                );
        //#Send Reponse To FireBase Server 
        $ch = curl_init();
        curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
        curl_setopt( $ch,CURLOPT_POST, true );
        curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
        $result = curl_exec($ch );
        dd($result);
        curl_close( $ch );
    }


}