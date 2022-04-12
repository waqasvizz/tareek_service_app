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


}