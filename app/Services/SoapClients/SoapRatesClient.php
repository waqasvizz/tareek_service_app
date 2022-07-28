<?php
namespace App\Services\SoapClients;

use App\Services\SoapServiceInterface;
// use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;
// use App\Models\User;
// use App\Models\Payment;
// use App\Models\Setting;
// use Auth;
use SoapClient;

class SoapRatesClient
{
    private static $soapClient;
    public function __construct()
    {
        static::$soapClient = SoapRatesClient::getObject();
    }

    public static function getObject()
    {
        $path = config('services.soap.rates_wsdl');
        static::$soapClient = new SoapClient($path, array('trace' => 1));
        return static::$soapClient;
    }

    public static function calculateShippingRate($data)
    {
        // $params = array(
        //     'ClientInfo'  			=> array(
        //                                 'AccountCountryCode'	=> 'JO',
        //                                 'AccountEntity'		 	=> 'AMM',
        //                                 'AccountNumber'		 	=> '00000',
        //                                 'AccountPin'		 	=> '000000',
        //                                 'UserName'			 	=> 'user@company.com',
        //                                 'Password'			 	=> '000000000',
        //                                 'Version'			 	=> 'v1.0'
        //                             ),
                                    
        //     'Transaction' 			=> array(
        //                                 'Reference1'			=> '001' 
        //                             ),
                                    
        //     'OriginAddress' 	 	=> array(
        //                                 'City'					=> 'Amman',
        //                                 'CountryCode'				=> 'JO'
        //                             ),
                                    
        //     'DestinationAddress' 	=> array(
        //                                 'City'					=> 'Dubai',
        //                                 'CountryCode'			=> 'AE'
        //                             ),
        //     'ShipmentDetails'		=> array(
        //                                 'PaymentType'			 => 'P',
        //                                 'ProductGroup'			 => 'EXP',
        //                                 'ProductType'			 => 'PPX',
        //                                 'ActualWeight' 			 => array('Value' => 5, 'Unit' => 'KG'),
        //                                 'ChargeableWeight' 	     => array('Value' => 5, 'Unit' => 'KG'),
        //                                 'NumberOfPieces'		 => 5
        //                             )
        // );
        
        // $soapClient = new SoapClient('http://url/to/wsdl.wsdl', array('trace' => 1));
        // $results = $soapClient->CalculateRate($params);	

        try {
            $response = static::$soapClient->CalculateRate($data);
            return SoapRatesClient::clientResponse($response);
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