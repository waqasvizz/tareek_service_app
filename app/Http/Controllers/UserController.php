<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\AssignService;
use App\Models\User;
use App\Models\Role;
use App\Models\Service;
use BenefitPaymentGateway;
use Validator;
use Session;
use DB;
use Auth;

class UserController extends Controller
{
    public function welcome()
    {
        return view('auth_v1.login');
    }

    public function login()
    {
        return view('auth_v1.login');
    }

    public function register()
    {
        return view('auth_v1.register');
    }

    public function resetPassword()
    {
        return view('auth_v1.reset-password');
    }

    public function forgotPassword()
    {
        return view('auth_v1.forgot-password');
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/login');
    }

    public function dashboard()
    {
        return view('dashboard');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $posted_data = array();
        $posted_data['paginate'] = 10;
        // $posted_data['latitude'] = '33.548087';
        // $posted_data['longitude'] = '73.130306';
        $data = User::getUser($posted_data);
    
        return view('user.list', compact('data'));
    }

    public function liveChatSample() {
        return view('live_chat');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data = array();
        $posted_data = array();
        $posted_data['orderBy_name'] = 'name';
        $posted_data['orderBy_value'] = 'Asc';
        $data['roles'] = Role::getRoles($posted_data);


        $posted_data = array();
        $posted_data['orderBy_name'] = 'service_name';
        $posted_data['orderBy_value'] = 'Asc';
        $data['services'] = Service::getServices($posted_data);
        
        return view('user.add',compact('data'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $posted_data = $request->all();
        $rules = array(
            'profile_image' => 'required',
            'phone_number' => 'nullable|regex:/^([0-9\s\-\+\(\)]*)$/|min:12',
            'user_role' => 'required',
            'user_name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'confirm_password' => 'required|required_with:password|same:password'
        );
        
        $messages = array(
            'phone_number.min' => 'The :attribute format is not correct (123-456-7890).'
        );

        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
            // ->withInput($request->except('password'));
        } else {

            try{
                $posted_data = $request->all();
                $posted_data['role'] = $posted_data['user_role'];
                $posted_data['name'] = $posted_data['user_name'];

                if($posted_data['role'] == 2 || $posted_data['role'] == 3){

                    if(empty($posted_data['address']) || empty($posted_data['latitude']) || empty($posted_data['longitude'])){
                        $error_message['address'] = 'Address field is required you must select address from the suggession.';
                    }

                    if(empty($posted_data['phone_number'])){
                        $error_message['phone_number'] = 'The Phone number field is required.';
                    }

                    if(!$request->file('profile_image')) {
                        $error_message['profile_image'] = 'The Profile image field is required.';
                    }

                    if(!empty($error_message)){
                        return back()->withErrors($error_message)->withInput();
                    }
                }

                if($posted_data['role'] == 2 && (!isset($posted_data['service']) || empty($posted_data['service']))){
                    // Session::flash('message', 'Please select service for the service provider!');
                    // return redirect()->back()->withInput();
                        $error_message['service'] = 'Please select service for the service provider.';
                    return back()->withErrors($error_message)->withInput();
                }


                $base_url = public_path();
                if($request->file('profile_image')) {
                    $extension = $request->profile_image->getClientOriginalExtension();
                    if($extension == 'jpg' || $extension == 'jpeg' || $extension == 'png'){

                        if (!empty(\Auth::user()->profile_image)) {
                            $url = $base_url.'/'.\Auth::user()->profile_image;
                            if (file_exists($url)) {
                                unlink($url);
                            }
                        }   
                        
                        $file_name = time().'_'.$request->profile_image->getClientOriginalName();
                        $filePath = $request->file('profile_image')->storeAs('profile_image', $file_name, 'public');
                        $posted_data['profile_image'] = 'storage/profile_image/'.$file_name;
                    }else{
                        return back()->withErrors([
                            'profile_image' => 'The Profile image format is not correct you can only upload (jpg, jpeg, png).',
                        ])->withInput();
                    }
                }
                
                $last_rec = User::saveUpdateUser($posted_data);


                if($posted_data['role'] == 2 && isset($posted_data['service']) && !empty($posted_data['service'])){
                //assign single services
                // =================================================================== 
                    $user = User::find($last_rec->id);

                    $assign_service = new AssignService;
                    $assign_service->service_id = $posted_data['service'];
                    
                    $user = $user->AssignServiceHasOne()->save($assign_service);
                // ===================================================================
                }


                Session::flash('message', 'User Register Successfully!');

            } catch (Exception $e) {
                Session::flash('error_message', $e->getMessage());
                // dd("Error: ". $e->getMessage());
            }
            return redirect()->back()->withInput();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // $AssignService = AssignService::where('user_id',3)->first();
        
        // $user = User::find(2);
        
        // // $AssignService->user()->associate($user)->save();
        // $AssignService->user()->associate($user);

 
        // echo '<pre>';
        // print_r($AssignService->toArray());
        // exit; 
        
        $posted_data = array();
        $posted_data['id'] = $id;
        $posted_data['detail'] = true;
        $data = User::getUser($posted_data);

 
        $posted_data = array();
        $posted_data['orderBy_name'] = 'name';
        $posted_data['orderBy_value'] = 'Asc';
        $data['roles'] = Role::getRoles($posted_data);


        $posted_data = array();
        $posted_data['orderBy_name'] = 'service_name';
        $posted_data['orderBy_value'] = 'Asc';
        $data['services'] = Service::getServices($posted_data);


        $AssignService = AssignService::where('user_id', $id)->first();
        if($AssignService){
            // $AssignService->user()->associate($data);
            // $data = $AssignService;
            $data['assign_service'] = $data->AssignService->toArray();
        }

        // echo '<pre>';
        // print_r($data->toArray());
        // exit; 

        // $user = User::find($id);
        // $data['assign_service'] = $data->AssignService;

        // echo '<pre>';
        // print_r($data['assign_service']->toArray());
        // exit; 
        

        return view('user.add',compact('data'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $posted_data = $request->all(); 
        $posted_data['update_id'] = $id;
        
        $rules = array(
            'update_id' => 'exists:users,id',
            'phone_number' => 'nullable|regex:/^([0-9\s\-\+\(\)]*)$/|min:12',
            'user_role' => 'required',
            'user_name' => 'required',
            'email' => 'required|email|unique:users,email,'.$id.',id',
            'password' => 'nullable|min:6',
            'confirm_password' => 'nullable|required_with:password|same:password'
        );
        
        $messages = array(
            'phone_number.min' => 'The :attribute format is not correct (123-456-7890).'
        );

        $validator = \Validator::make($posted_data, $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
            // ->withInput($request->except('password'));
        } else {

            try{
                $posted_data['role'] = $posted_data['user_role'];
                $posted_data['name'] = $posted_data['user_name'];



                if($posted_data['role'] == 2 || $posted_data['role'] == 3){

                    if(empty($posted_data['address']) || empty($posted_data['latitude']) || empty($posted_data['longitude'])){
                        $error_message['address'] = 'Address field is required you must select address from the suggession.';
                    }

                    if(empty($posted_data['phone_number'])){
                        $error_message['phone_number'] = 'The Phone number field is required.';
                    }

                    // if(!$request->file('profile_image')) {
                    //     $error_message['profile_image'] = 'The Profile image field is required.';
                    // }
 
                    if((!isset($posted_data['service']) && $posted_data['role'] == 2) || (isset($posted_data['service']) && $posted_data['role'] == 2 && empty($posted_data['service']))){
                        $error_message['service'] = 'Please select service for the service provider.';
                    }

                    if(!empty($error_message)){
                        return back()->withErrors($error_message)->withInput();
                    }
                }


                //assign single services
                // =================================================================== 
                    // $user = User::find($id);

                    // $assign_service = new AssignService;
                    // $assign_service->service_id = $posted_data['service'];
                    
                    // $user = $user->AssignServiceHasOne()->save($assign_service);
                // ===================================================================


                if(isset($posted_data['service']) && $posted_data['role'] == 2 && $posted_data['service'] != ''){

                    $AssignService = AssignService::where('user_id',$id)->first();

                    if($AssignService){
                        $service = Service::find($posted_data['service']);
                        $AssignService->service()->associate($service)->save();
                    }else{
                        $user = User::find($id);
                        $assign_service = new AssignService;
                        $assign_service->service_id = $posted_data['service'];
                        $user->AssignServiceHasOne()->save($assign_service);
                    }
                    
                }else{
                    if($posted_data['role'] == 2 && (!isset($posted_data['service']) || empty($posted_data['service']))){
                        $error_message['service'] = 'Please select service for the service provider.';
                        return back()->withErrors($error_message)->withInput();
                    }
                    $delete_old_selected_services = AssignService::where('user_id',$id);
                    $delete_old_selected_services->delete(); 
                }



                //assign multiple services
                // =================================================================== 

                    // $delete_old_selected_services = AssignService::where('user_id',$id);
                    // $delete_old_selected_services->delete(); 

                    // $user = User::find($id);
    
                    // $assign_service1 = new AssignService;
                    // $assign_service1->service_id = 1;
                    
                    // $assign_service2 = new AssignService;
                    // $assign_service2->service_id = 2;
                    
                    // $user = $user->AssignService()->saveMany([$assign_service1, $assign_service2]);
                // ===================================================================
                
                // $assignService = AssignService::find(1);
                // $service = Service::find($posted_data['service']);
                // $assignService->service()->associate($service)->save();


                $base_url = public_path();
                if($request->file('profile_image')) {
                    $extension = $request->profile_image->getClientOriginalExtension();
                    if($extension == 'jpg' || $extension == 'jpeg' || $extension == 'png'){

                        if (!empty(\Auth::user()->profile_image)) {
                            $url = $base_url.'/'.\Auth::user()->profile_image;
                            if (file_exists($url)) {
                                unlink($url);
                            }
                        }   
                        
                        $file_name = time().'_'.$request->profile_image->getClientOriginalName();
                        $filePath = $request->file('profile_image')->storeAs('profile_image', $file_name, 'public');
                        $posted_data['profile_image'] = 'storage/profile_image/'.$file_name;
                    }else{
                        return back()->withErrors([
                            'profile_image' => 'The Profile image format is not correct you can only upload (jpg, jpeg, png).',
                        ])->withInput();
                    }
                }

                User::saveUpdateUser($posted_data);
                Session::flash('message', 'User Update Successfully!');
                return redirect()->back();

            } catch (Exception $e) {
                Session::flash('error_message', $e->getMessage());
                // dd("Error: ". $e->getMessage());
            }
            return redirect()->back()->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::find($id);
        if($user){
            
            $base_url = public_path();
            if (!empty($user->profile_image)) {
                $url = $base_url.'/'.$user->profile_image;
                if (file_exists($url)) {
                    unlink($url);
                }
            }

            User::deleteUser($id); 
            Session::flash('message', 'User deleted successfully!');
        }else{
            Session::flash('error_message', 'User already deleted!');
        }
        return redirect()->back();
        // if(Service::find($id)){
        //     Service::deleteService($id); 
        //     Session::flash('message', 'Service deleted successfully!');
        // }else{
        //     Session::flash('error_message', 'Service already deleted!');
        // }
        // return redirect()->back();
    }
    
    public function testing() {

        // dd($route);

        // exit(asset('storage/default-images/app-logo-email.png'));
        
        // exit($_SERVER['SERVER_NAME']);
        // exit($_SERVER['SERVER_PORT']);
        // exit($_SERVER['DOCUMENT_ROOT']);

        // URL::to("/");
        $val = '25';
        
        $benefit_gateway = new BenefitPaymentGateway();
        
        // modify the following to reflect your "Tranportal ID", "Tranportal Password ", "Terminal Resourcekey"
        
        $benefit_gateway->setAction("1");
        $benefit_gateway->setCurrency("048");
        $benefit_gateway->setLanguage("USA");
        $benefit_gateway->setType("D");

        $benefit_gateway->setAlias("test18009950");

        $key_full_url = public_path().'/storage/key_files/keystore/';
        $resource_full_url = public_path().'/storage/key_files/resource/';

        // $benefit_gateway->setResourcePath("resource/"); //only the path that contains the file; do not write the file name
        // $benefit_gateway->setKeystorePath("resource/"); //only the path that contains the file; do not write the file name
        $benefit_gateway->setResourcePath($resource_full_url);
        $benefit_gateway->setKeystorePath($key_full_url);


        // if (file_exists($key_full_url))
        //     return config('app.url').'/'.$image_path;
        // else
        //     return config('app.url').'/storage/defaults/user.jpg';
        
        // $benefit_gateway->setkey("21715115560721715115560721715115");
        // $benefit_gateway->setid("18009950");
        // $benefit_gateway->setpassword("18009950");
        
        // Do NOT change the values of the following parameters at all.
        // $benefit_gateway->setaction("1");
        // $benefit_gateway->setcardType("D");
        // $benefit_gateway->setcurrencyCode("048");

        // modify the following to reflect your pages URLs
        // $benefit_gateway->setresponseURL("https://www.yourWebsite.com/PG/response.php");
        // $benefit_gateway->seterrorURL("https://www.yourWebsite.com/PG/error.php");

        
        // $benefit_gateway->setresponseURL("https://tareek.go-demo.com/payment_response/response.php");
        // $benefit_gateway->seterrorURL("https://tareek.go-demo.com/payment_response/error.php");
        $benefit_gateway->setresponseURL("https://tareek.go-demo.com/response");
        $benefit_gateway->seterrorURL("https://tareek.go-demo.com/error");

        

        // set a unique track ID for each transaction so you can use it later to match transaction response and identify transactions in your system and “BENEFIT Payment Gateway” portal.
        
        $benefit_gateway->settrackId(date('Ymdhis'));
        
        // set transaction amount
        $benefit_gateway->setamt("20.500");
        
        // $benefit_gateway->setamt($val);
        // $benefit_gateway->setaction($val);
        // $benefit_gateway->setpassword($val);
        // $benefit_gateway->setid($val);
        // $benefit_gateway->setcurrencyCode($val);
        // $benefit_gateway->settrackId($val);

        // The following user-defined fields (UDF1, UDF2, UDF3, UDF4, UDF5) are optional fields.
        // However, we recommend setting theses optional fields with invoice/product/customer identification information as they will be reflected in “BENEFIT Payment Gateway” portal where you will be able to link transactions to respective customers. This is helpful for dispute cases. 
        $benefit_gateway->setudf1('AA11');
        $benefit_gateway->setudf2('AA22');
        $benefit_gateway->setudf3('AA33');
        $benefit_gateway->setudf4('AA44');
        $benefit_gateway->setudf5('AA55');

                        // $benefit_gateway->setexpYear('2024');
                        // $benefit_gateway->setexpMonth('06');
                        // $benefit_gateway->setmember($val);
                        // $benefit_gateway->setcardNo('4600410123456789');
        // $benefit_gateway->setcardType($val);

        // $date = date('Y-m-d h:i:s');
        // $benefit_gateway->setpaymentData($date);

                        // $benefit_gateway->setpaymentMethod($val);
                        // $benefit_gateway->settransactionIdentifier($val);
        // $benefit_gateway->setresponseURL($val);
        // $benefit_gateway->seterrorURL($val);
                        // $benefit_gateway->settransId($val);
                        // $benefit_gateway->setpin($val);
                        // $benefit_gateway->setticketNo($val);
                        // $benefit_gateway->setbookingId($val);
        // $benefit_gateway->settransactionDate($date);

        // $isSuccess = $benefit_gateway->performeTransaction();
        // if($isSuccess==1){
        //     header('location:'.$benefit_gateway->getresult());
        // }
        // else{
        //     echo 'Error: '.$benefit_gateway->geterror().'<br />Error Text: '.$benefit_gateway->geterrorText();
        // }

        
        if(trim($benefit_gateway->performPaymentInitializationHTTP())!=0) {
            echo("ERROR OCCURED! SEE CONSOLE FOR MORE DETAILS");
            return;
        }
        else {
            $url=$benefit_gateway->getwebAddress();
            echo "<meta http-equiv='refresh' content='0;url=$url'>";
        }
        

        /*
        $isSuccess = $benefit_gateway->performPaymentInitializationHTTP();
        // $isSuccess = $benefit_gateway->performPaymentInitializationHTTP();
        if($isSuccess==1){
            echo "Okaaa";
            // header('location:'.$benefit_gateway->getresult());
            echo 'Success: '.$benefit_gateway->getresult();
        }
        else{
            echo 'Error: '.$benefit_gateway->geterror().'<br />Error Text: '.$benefit_gateway->geterrorText();
        }
        */

        // if(trim($benefit_gateway->performPaymentInitializationHTTP())!=0)
        // {
        //     echo("ERROR OCCURED! SEE CONSOLE FOR MORE DETAILS");
        //     return;
        // }
        // else
        // {
        //     $url=$benefit_gateway->getwebAddress();
        //     echo "<meta http-equiv='refresh' content='0;url=$url'>";
        // }
        
        echo "Line no @"."<br>";
        echo "<pre>";
        print_r($benefit_gateway);
        echo "</pre>";
        exit("@@@@");

        echo "OUTSIDE";
        exit("@@@@");

        /*
        **************************************
                 ALL RELATED PARAMS
        **************************************

        'amt' => $this->amt,
		'action' => $this->action,
		'password' => $this->password,
		'id' => $this->id,
		'currencycode' => $this->currencyCode,
		'trackId' => $this->trackId,
		'udf1' => $this->udf1,
		'udf2' => $this->udf2,
		'udf3' => $this->udf3,
		'udf4' => $this->udf4,
		'udf5' => $this->udf5,
		'expYear' => $this->expYear,
		'expMonth' => $this->expMonth,
		'member' => $this->member,
		'cardNo' => $this->cardNo,
		'cardType' => $this->cardType,
		'paymentData' => $this->paymentData,
		'paymentMethod' => $this->paymentMethod,
		'transactionIdentifier' => $this->transactionIdentifier,
		'responseURL' => $this->responseURL,
		'errorURL' => $this->errorURL,
		'transId' => $this->transId,
		'pin' => $this->pin,
		'ticketNo' => $this->ticketNo,
		'bookingId' => $this->bookingId,
		'transactionDate' => $this->transactionDate,
        
        **************************************
               ALL RELATED FUNCTIONS
        **************************************
        
        $benefit_gateway->setamt($val);
        $benefit_gateway->setaction($val);
        $benefit_gateway->setpassword($val);
        $benefit_gateway->setid($val);
        $benefit_gateway->setcurrencyCode($val);
        $benefit_gateway->settrackId($val);
        $benefit_gateway->setudf1($val);
        $benefit_gateway->setudf2($val);
        $benefit_gateway->setudf3($val);
        $benefit_gateway->setudf4($val);
        $benefit_gateway->setudf5($val);
        $benefit_gateway->setexpYear($val);
        $benefit_gateway->setexpMonth($val);
        $benefit_gateway->setmember($val);
        $benefit_gateway->setcardNo($val);
        $benefit_gateway->setcardType($val);
        $benefit_gateway->setpaymentData($val);
        $benefit_gateway->setpaymentMethod($val);
        $benefit_gateway->settransactionIdentifier($val);
        $benefit_gateway->setresponseURL($val);
        $benefit_gateway->seterrorURL($val);
        $benefit_gateway->settransId($val);
        $benefit_gateway->setpin($val);
        $benefit_gateway->setticketNo($val);
        $benefit_gateway->setbookingId($val);
        $benefit_gateway->settransactionDate($val);

        */

        echo "Line no @"."<br>";
        echo "<pre>";
        print_r($benefit_gateway);
        echo "</pre>";
        exit("@@@@");

        exit('deee');

        $data = [
            'subject' => 'New Order - '.config('app.name'),
            'name' => 'Danish Hussain',
            'email' => 'danishhussain9525@gmail.com',
        ];

        \Mail::send('emails.order_email', ['email_data' => $data], function($message) use ($data) {
            $message->to($data['email'])
                    ->subject($data['subject']);
        });

        exit('aaaaa');

        $html = decodeShortCodesTemplate([
            'html' => '<b><p>Hi [receiver_name], How are you? [receiver_name] you are awesome from [sender_name], App name is [app_name], Logo link is [logo_url], Email verify with this link [email_verification_url]</p></b>',
            'email_message_id' => 1,
            'sender_id' => 1,
            'receiver_id' => 10,
        ]);

        echo "Line no deee@"."<br>";
        echo "<pre>";
        print_r($html);
        echo "</pre>";
        exit("@@@@");


        // $base_url = public_path();
        // echo $base_url.'<br><br>';

        // echo $_SERVER['DOCUMENT_ROOT'];

        exit('deedeeee');
        // $posted_data['id'] = Auth::user()->id;
        // $posted_data['detail'] = true;

        // $result = User::getUser($posted_data);

    }

    public function response(Request $request) {
        // require('BenefitAPIPlugin.php');
        $data = $request->all();
        $trandata = isset($_POST["trandata"]) ? $_POST["trandata"] : "";
        
        echo "Line no data@"."<br>";
        echo "<pre>";
        print_r($data);
        echo "</pre><br><br><br><br>";

        echo "Line no trans@"."<br>";
        echo "<pre>";
        print_r($trandata);
        echo "</pre>";
        exit("@@@@");

        if ($trandata != "")
        {
            $Pipe = new BenefitPaymentGateway();
            
            // modify the following to reflect your "Terminal Resourcekey"
            $Pipe->setkey("21715115560721715115560721715115");
            
            $Pipe->settrandata($trandata);
            
            $returnValue =  $Pipe->parseResponseTrandata();
            if ($returnValue == 1)
            {
                $paymentID = $Pipe->getpaymentId();
                $result = $Pipe->getresult();
                $responseCode = $Pipe->getauthRespCode();
                $transactionID = $Pipe->gettransId();
                $referenceID = $Pipe->getref();
                $trackID = $Pipe->gettrackId();
                $amount = $Pipe->getamt();
                $UDF1 = $Pipe->getudf1();
                $UDF2 = $Pipe->getudf2();
                $UDF3 = $Pipe->getudf3();
                $UDF4 = $Pipe->getudf4();
                $UDF5 = $Pipe->getudf5();
                $authCode = $Pipe->getauthCode();
                $postDate = $Pipe->gettranDate();
                $errorCode = $Pipe->geterror();
                $errorText = $Pipe->geterrorText();
            
                // Remove any HTML/CSS/javascrip from the page. Also, you MUST NOT write anything on the page EXCEPT the word "REDIRECT=" (in upper-case only) followed by a URL.
                // If anything else is written on the page then you will not be able to complete the process.
                if ($Pipe->getresult() == "CAPTURED")
                {
                    echo("REDIRECT=https://www.yourWebsite.com/PG/approved.php");
                }
                else if ($Pipe->getresult() == "NOT CAPTURED" || $Pipe->getresult() == "CANCELED" || $Pipe->getresult() == "DENIED BY RISK" || $Pipe->getresult() == "HOST TIMEOUT")
                {
                    if ($Pipe->getresult() == "NOT CAPTURED")
                    {
                        switch ($Pipe->getAuthRespCode())
                        {
                            case "05":
                                $response = "Please contact issuer";
                                break;
                            case "14":
                                $response = "Invalid card number";
                                break;
                            case "33":
                                $response = "Expired card";
                                break;
                            case "36":
                                $response = "Restricted card";
                                break;
                            case "38":
                                $response = "Allowable PIN tries exceeded";
                                break;
                            case "51":
                                $response = "Insufficient funds";
                                break;
                            case "54":
                                $response = "Expired card";
                                break;
                            case "55":
                                $response = "Incorrect PIN";
                                break;
                            case "61":
                                $response = "Exceeds withdrawal amount limit";
                                break;
                            case "62":
                                $response = "Restricted Card";
                                break;
                            case "65":
                                $response = "Exceeds withdrawal frequency limit";
                                break;
                            case "75":
                                $response = "Allowable number PIN tries exceeded";
                                break;
                            case "76":
                                $response = "Ineligible account";
                                break;
                            case "78":
                                $response = "Refer to Issuer";
                                break;
                            case "91":
                                $response = "Issuer is inoperative";
                                break;
                            default:
                                // for unlisted values, please generate a proper user-friendly message
                                $response = "Unable to process transaction temporarily. Try again later or try using another card.";
                                break;
                        }
                    }
                    else if ($Pipe->getresult() == "CANCELED")
                    {
                        $response = "Transaction was canceled by user.";
                    }
                    else if ($Pipe->getresult() == "DENIED BY RISK")
                    {
                        $response = "Maximum number of transactions has exceeded the daily limit.";
                    }
                    else if ($Pipe->getresult() == "HOST TIMEOUT")
                    {
                        $response = "Unable to process transaction temporarily. Try again later.";
                    }
                    echo "REDIRECT=https://www.yourWebsite.com/PG/declined.php";
                }
                else
                {
                    //Unable to process transaction temporarily. Try again later or try using another card.
                    echo "REDIRECT=https://www.yourWebsite.com/PG/err-response.php";
                }
            }
            else
            {
                $errorText = $Pipe->geterrorText();
            }
        }
        else if (isset($_POST["ErrorText"]))
        {
            $paymentID = $_POST["paymentid"];
            $trackID = $_POST["trackid"];
            $amount = $_POST["amt"];
            $UDF1 = $_POST["udf1"];
            $UDF2 = $_POST["udf2"];
            $UDF3 = $_POST["udf3"];
            $UDF4 = $_POST["udf4"];
            $UDF5 = $_POST["udf5"];
            $errorText = $_POST["ErrorText"];
        }
        else
        {
            $errorText = "Unknown Exception";
        }
    }

    public function error(Request $request) {
        echo "Line no nowwww@"."<br>";
        echo "<pre>";
        print_r($request->all());
        echo "</pre>";
        exit("@@@@");        
    }

    public function accountLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],  
        ]);
        // $credentials['role'] = 1;

        if (Auth::attempt($credentials)) {
            return redirect('/admin');
        }else{
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ]);
        }
    }

    public function accountRegister(Request $request)
    {
        $rules = array(
            'user_name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'confirm_password' => 'required|required_with:password|same:password'
        );

        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }else{
            try{
                $posted_data = $request->all();
                $posted_data['role'] = 2;
                $posted_data['name'] = $posted_data['user_name'];

                User::saveUpdateUser($posted_data);
                Session::flash('message', 'User Register Successfully!');

            } catch (Exception $e) {
                Session::flash('error_message', $e->getMessage());
                // dd("Error: ". $e->getMessage());
            }
            return redirect('/login');
        }
    }
    
    public function accountResetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // 'email' => 'required|email|unique:users',
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        } else {

            $users = User::where('email', '=', $request->input('email'))->first();
            if ($users === null) {
                // echo 'User does not exist';
                return back()->withErrors([
                    'email' => 'The provided credentials do not match our records.',
                ]);
                // Session::flash('error_message', 'Your email does not exists.');
                return redirect()->back()->withInput();
            } else {
                // echo 'User exits';
                $random_hash = substr(md5(uniqid(rand(), true)), 10, 10); 
                $email = $request->get('email');
                $password = Hash::make($random_hash);

                // $userObj = new user();
                // $posted_data['email'] = $email;
                // $posted_data['password'] = $password;
                // $userObj->updateUser($posted_data);

                DB::update('update users set password = ? where email = ?',[$password,$email]);

                $data = [
                    'new_password' => $random_hash,
                    'subject' => 'Reset Password',
                    'email' => $email
                ];

                Mail::send('emails.reset_password', $data, function($message) use ($data) {
                    $message->to($data['email'])
                    ->subject($data['subject']);
                });
                Session::flash('message', 'Your password has been changed successfully please check you email!');
                return redirect('/login');
            }

        }
    }
}