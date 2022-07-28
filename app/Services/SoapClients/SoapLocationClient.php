<?php
namespace App\Services\SoapClients;

use App\Services\SoapServiceInterface;
use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;
// use App\Models\User;
// use App\Models\Payment;
// use App\Models\Setting;
// use Auth;
use SoapClient;

class SoapLocationClient
{
    private static $soapClient;
    public function __construct()
    {
        static::$soapClient = SoapLocationClient::getObject();
    }

    public static function getObject()
    {
        $path = config('services.soap.location_wsdl');
        static::$soapClient = new SoapClient($path);
        return static::$soapClient;
    }

    public static function getAddress($client, $data)
    {
        // $client->ValidateAddress($data);
        // $this->soapClient->ValidateAddress($data);

        // echo '<pre>';
        // print_r($soap_response);
        // die();
    }

    public static function getCountry($data)
    {
        if ( !(isset($data['Code']) && $data['Code']) ) {
            return [
                'Error' => 1,
                'ErrorDetails' => 'The country code not provided in the request'
            ];
        }

        try {
            $response = static::$soapClient->FetchCountry($data);
            return SoapLocationClient::clientResponse($response);
        }
        catch (SoapFault $fault) {
            $response = 'Error : ' . $fault->faultstring;
            return $response;
        }
    }
    
    public static function addressValidation($data)
    {
        $validator = \Validator::make($data, [
            'Address' => 'required|array',
            'Address.Line1' => 'required',
            'Address.City' => 'required',
            'Address.PostCode' => 'required',
            'Address.CountryCode' => 'required',
            

            /*

            //     'Line1'			=> '001',
            //     'Line2'			=> '',
            //     'Line3'			=> '',
            //     'City'			=> 'Mumbai',
            //     'StateOrProvinceCode' => '',
            //     'PostCode'			=> '400093',
            //     'CountryCode'			=> 'IN'	
            

            */
        ]);

        if($validator->fails()){
            return ["error"=>$validator->errors()->first()];
        }
        else {
            return ["okaaa"=>'Trueee...'];
        }

        if ( !(isset($data['Code']) && $data['Code']) ) {
            return [
                'Error' => 1,
                'ErrorDetails' => 'The country code not provided in the request'
            ];
        }

        try {
            $response = static::$soapClient->FetchCountry($data);
            return SoapLocationClient::clientResponse($response);
        }
        catch (SoapFault $fault) {
            $response = 'Error : ' . $fault->faultstring;
            return $response;
        }
    }

    public static function getAllCountries($data)
    {
        try {
            $response = static::$soapClient->FetchCountries($data);
            return SoapLocationClient::clientResponse($response);
        }
        catch (SoapFault $fault) {
            $response = 'Error : ' . $fault->faultstring;
            return $response;
        }
    }

    public function clientResponse($data) {
        $data = json_decode(json_encode($data), true);

        $response = array();
        if ( isset($data['HasErrors']) && $data['HasErrors'] ==  true) {
            $response['Error'] = 1;
            $response['ErrorDetails'] = $data['Notifications']['Notification'];
        }
        else {
            if ( isset($data['Transaction']) ) unset($data['Transaction']);
            if ( isset($data['Notifications']) ) unset($data['Notifications']);
            if ( isset($data['HasErrors']) ) unset($data['HasErrors']);

            $response = $data;
            $response['Error'] = 0;
            $response['ErrorDetails'] = [];
        }
        return $response;
    }

    // public function chargePayment($posted_data = array()){

    //     if(count($posted_data) > 0 && $posted_data['customer_id'] != '' ) {
    //         try {
    //             $stripe_response = $this->stripe->charges->create([
    //                 'customer' => $posted_data['customer_id'],
    //                 'amount' => $posted_data['amount'],
    //                 'currency' => $posted_data['currency'],
    //                 'description' => $posted_data['description'],
    //             ]);

    //             return $stripe_response;
    //         }
    //         catch (\Throwable $th) {
    //             return false;
    //         }
    //     }
    //     else {
    //         return false;
    //     }

    // }

    // public function createCustomer($posted_data = array()){
        // $stripe = StripeGateway::getGatewayObject();

        // try {
        //     $customer_rec = $this->stripe->customers->create([
        //         'email' => $posted_data['email'],
        //         'source'  => $posted_data['stripeToken'],
        //         // 'source'  => $request->stripeToken,
        //         'description' => $posted_data['name'],
        //         // 'description' => 'Customer Name is '.$posted_data['card_name'],
        //         // 'description' => 'Customer Name is '.Auth::user()->name,
        //         // 'description' => 'Customer Name is '.$request->get('card_name'),
        //     ]);
        //     return $customer_rec->id;
        // }
        // catch (\Throwable $th) {
        //     return false;
        // }
    // }
}