<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\SoapServiceInterface;
use App\Services\SoapClients\SoapLocationClient;
use App\Services\SoapClients\SoapRatesClient;
use App\Services\SoapClients\SoapTrackingClient;
use App\Services\SoapClients\SoapShippingClient;

class SoapClientProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(SoapLocationClient::class, function($app) {
            return new SoapLocationClient();
        });

        $this->app->bind(SoapRatesClient::class, function($app) {
            return new SoapRatesClient();
        });

        $this->app->bind(SoapTrackingClient::class, function($app) {
            return new SoapTrackingClient();
        });
        
        $this->app->bind(SoapShippingClient::class, function($app) {
            return new SoapShippingClient();
        });

            // $serviceUrl = '/sap/bc/srt/scs/sap/managecustomerin1';
            // $wsdlPath = 'soap/managecustomerin1.wsdl';
            // $soapClient = new \SoapClient(
            //     storage_path($wsdlPath),
            //     array(
            //         'trace' => 1,
            //         'soap_version' => SOAP_1_2,
            //         'exceptions' => 1,
            //         'login' => env('SOAP_USER'),
            //         'password' => env('SOAP_PASSWORD'),
            //     )
            // );
            // $soapClient->__setLocation(env('BYD_DOMAIN') . $serviceUrl);
            // return $soapClient;
        // });
        
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
