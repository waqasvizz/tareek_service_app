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

class SoapTrackingClient
{
    private $soapClient;
    public function __construct()
    {
        $this->soapClient = SoapTrackingClient::getObject();
    }

    public function getObject()
    {
        $path = config('services.soap.tracking_wsdl');
        $soapClient = new SoapClient($path);
        return $soapClient;
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