<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderService extends Model
{
    use HasFactory;

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class)->with('category');
    }

    public function getOrderService($posted_data = array())
    {
        $query = OrderService::latest()->with('order')->with('service');

        if (isset($posted_data['id'])) {
            $query = $query->where('id', $posted_data['id']);
        }
        if (isset($posted_data['service_id'])) {
            $query = $query->where('service_id', $posted_data['service_id']);
        }
        if (isset($posted_data['order_id'])) {
            $query = $query->where('order_id', $posted_data['order_id']);
        }

        $query->select('*');
        
        $query->getQuery()->orders = null;
        if (isset($posted_data['orderBy_name'])) {
            $query->orderBy($posted_data['orderBy_name'], $posted_data['orderBy_value']);
        } else {
            $query->orderBy('id', 'DESC');
        }

        if (isset($posted_data['paginate'])) {
            $result = $query->paginate($posted_data['paginate']);
        } else {
            if (isset($posted_data['detail'])) {
                $result = $query->first();
            } else if (isset($posted_data['count'])) {
                $result = $query->count();
            } else {
                $result = $query->get();
            }
        }
        return $result;
    }



    public function saveUpdateOrderService($posted_data = array())
    {
        if (isset($posted_data['update_id'])) {
            $data = OrderService::find($posted_data['update_id']);
        } else {
            $data = new OrderService;
        }

        if (isset($posted_data['order_id'])) {
            $data->order_id = $posted_data['order_id'];
        }
        if (isset($posted_data['service_id'])) {
            $data->service_id = $posted_data['service_id'];
        }
        if (isset($posted_data['schedule_date'])) {
            $data->schedule_date = $posted_data['schedule_date'];
        }
        if (isset($posted_data['schedule_time'])) {
            $data->schedule_time = $posted_data['schedule_time'];
        }
        if (isset($posted_data['service_price'])) {
            $data->service_price = $posted_data['service_price'];
        }
        if (isset($posted_data['admin_earn'])) {
            $data->admin_earn = $posted_data['admin_earn'];
        }
        if (isset($posted_data['supplier_earn'])) {
            $data->supplier_earn = $posted_data['supplier_earn'];
        }
        if (isset($posted_data['adm_aftr_reedem'])) {
            $data->adm_aftr_reedem = $posted_data['adm_aftr_reedem'];
        }
        if (isset($posted_data['sup_aftr_reedem'])) {
            $data->sup_aftr_reedem = $posted_data['sup_aftr_reedem'];
        }
        if (isset($posted_data['reedem_disc'])) {
            $data->reedem_disc = $posted_data['reedem_disc'];
        }
        if (isset($posted_data['reedem_disc'])) {
            $data->reedem_disc = $posted_data['reedem_disc'];
        }



        $data->save();
        return $data;
    }


    public function deleteOrderService($id) {
        $data = OrderService::find($id);
        if (isset($data->id) )
            return $data->delete();
        else 
            return false;
    }
}   