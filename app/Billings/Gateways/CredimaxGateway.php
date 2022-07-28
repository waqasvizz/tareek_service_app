<?php
namespace App\Billings\Gateways;

use App\Billings\PaymentGatewayInterface;
use Illuminate\Http\Request;

class CredimaxGateway implements PaymentGatewayInterface
{

    public function process(Request $request)
    {
        $products = [];
        $products['items'] = [
            [
                'name'    => 'Laravel Book',
                'price'   => 50,
                'des'     => "Laravel Book for Advance Learning",
                'qty'     => 1
            ]
        ];

        $products['invoice_id'] = uniqid();
        $products['invoice_description'] = "Order #{$products['invoice_id']} Billing";
        $products['return_url'] = route('success.pay');
        $products['cancel_url'] = route('success.pay');
        $products['total'] = 50;

        $paypal = new ExpressCheckout();
        return $paypal->setExpressCheckout($products);
    }

    public function checkout($res)
    {
        return redirect($res['paypal_link']);
    }

    public function createConnection(Request $request){
        if ( !empty($posted_data) ) {

            $cURLConnection = curl_init();

            curl_setopt($cURLConnection, CURLOPT_URL, 'https://credimax.gateway.mastercard.com/api/rest/version/1/information');
            curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
            
            $response = curl_exec($cURLConnection);
            curl_close($cURLConnection);
            
            $response = json_decode($response, true);
            return $response;
        }
    }

    public function payerDetails(Request $request){
        // some code here...
    }

    public function receiverDetails(Request $request){
        // some code here...
    }

    public function transactionDetails(Request $request){
        // some code here...
    }
}