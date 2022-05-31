<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
// use Laravel\Sanctum\HasApiTokens;
use Laravel\Passport\HasApiTokens;
use DB;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    public function role()
    {
        return $this->belongsTo('App\Models\Role')
            ->select(['roles.id', 'roles.name']);
    }

    public function userAssets()
    {
        return $this->hasMany(UserAssets::class);
    }

    public function userAddress()
    {
        return $this->hasMany(UserMultipleAddresse::class);
    }

    public function fcm_tokens()
    {
        return $this->hasMany('App\Models\FCM_Token');
        // return $this->belongsToMany('App\Models\AssignJob');
    }

    public function AssignService()
    {
        return $this->hasMany(AssignService::class);
    }

    public function AssignServiceHasOne()
    {
        return $this->hasOne(AssignService::class);
    }


    public function getUser($posted_data = array())
    {
        $query = User::latest()
                    ->with('role')
                    ->with('userAssets')
                    ->with('userAddress')
                    ->with('fcm_tokens');

        if (isset($posted_data['id'])) {
            $query = $query->where('users.id', $posted_data['id']);
        }
        if (isset($posted_data['users_in'])) {
            $query = $query->whereIn('users.id', $posted_data['users_in']);
        }
        if (isset($posted_data['email'])) {
            $query = $query->where('users.email', $posted_data['email']);
        }
        if (isset($posted_data['name'])) {
            $query = $query->where('users.name', 'like', '%' . $posted_data['name'] . '%');
        }
        if (isset($posted_data['role'])) {
            $query = $query->where('users.role_id', $posted_data['role']);
        }
	    if (isset($posted_data['phone_number'])) {
            $query = $query->where('users.phone_number', $posted_data['phone_number']);
        }

        // $query->join('roles', 'roles.id', '=', 'users.role');

        // $query->leftJoin('payments', function ($join) {
        //     $join->on('payments.user_id', '=', 'users.id');
        //     $join->on('payments.id', DB::raw('(SELECT MAX(payments.id) FROM payments WHERE `payments`.`user_id` = `users`.`id`)'));
        // });
        
        $query->select('*');
        
        $query->getQuery()->orders = null;
        if (isset($posted_data['orderBy_name'])) {
            $query->orderBy($posted_data['orderBy_name'], $posted_data['orderBy_value']);
        } else {
            $query->orderBy('users.id', 'DESC');
        }

        if (isset($posted_data['paginate'])) {
            $result = $query->paginate($posted_data['paginate']);
        } else {
            if (isset($posted_data['detail'])) {
                $result = $query->first();
            } else if (isset($posted_data['count'])) {
                $result = $query->count();
            } else if (isset($posted_data['to_array'])) {
                $result = $query->get()->toArray();
            } else {
                $result = $query->get();
            }
        }
        
        if (isset($posted_data['to_sql'])) {
            $result = $query->toSql();
            echo '<pre>';
            print_r($result);
            exit;
        }

        if (isset($posted_data['sumBy_column'])) {
            $result = $result->sum($posted_data['sumBy_columnName']);
        }
        
        return $result;
    }

    public function saveUpdateUser($posted_data = array())
    {
        if (isset($posted_data['update_id'])) {
            $data = User::find($posted_data['update_id']);
        } else {
            $data = new User;
        }
        
        if (isset($posted_data['company_documents'])) {
            unset($posted_data['company_documents']);
        }
        if (isset($posted_data['role'])) {
            $data->role_id = $posted_data['role'];
        }
        if (isset($posted_data['name'])) {
            $data->name = $posted_data['name'];
        }
        if (isset($posted_data['full_name'])) {
            $data->name = $posted_data['full_name'];
        }
        if (isset($posted_data['date_of_birth'])) {
            $data->date_of_birth = $posted_data['date_of_birth'];
        }
        if (isset($posted_data['email'])) {
            $data->email = $posted_data['email'];
        }
        if (isset($posted_data['profile_image'])) {
            $data->profile_image = $posted_data['profile_image'];
        }
        if (isset($posted_data['phone_number'])) {
            $data->phone_number = $posted_data['phone_number'];
        }
        if (isset($posted_data['company_name'])) {
            $data->company_name = $posted_data['company_name'];
        }
        if (isset($posted_data['company_number'])) {
            $data->company_number = $posted_data['company_number'];
        }
        if (isset($posted_data['password'])) {
            $data->password = Hash::make($posted_data['password']);
        }
        if (isset($posted_data['user_type'])) {
            $data->user_type = $posted_data['user_type'];
        }
        if (isset($posted_data['address'])) {
            $data->address = $posted_data['address'];
        }
        if (isset($posted_data['latitude'])) {
            $data->latitude = $posted_data['latitude'];
        }
        if (isset($posted_data['longitude'])) {
            $data->longitude = $posted_data['longitude'];
        }
        if (isset($posted_data['email_verified_at'])) {
            $data->email_verified_at = $posted_data['email_verified_at'];
        }
        if (isset($posted_data['account_status'])) {
            $data->account_status = $posted_data['account_status'];
        }
        if (isset($posted_data['email_token'])) {
            $data->remember_token = $posted_data['email_token'];
        }

        $data->save();

        $data = User::getUser([
            'detail' => true,
            'id' => $data->id,
        ]);

        return $data;
    }

    public function deleteUser($id=0)
    {
        $data = User::find($id);
        return $data->delete();
    }
}