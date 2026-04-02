<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'part_id', 'service_id', 'type', 'employee_id', 'quantity', 'price'];

    public function order() { return $this->belongsTo(Order::class); }
    public function part() { return $this->belongsTo(Part::class); }
    public function service() { return $this->belongsTo(Service::class); }
    public function mechanic() { return $this->belongsTo(User::class, 'employee_id'); }
}
