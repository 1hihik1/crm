<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employment extends Model
{
    protected $fillable = ['user_id', 'workplace_id', 'started_at', 'finished_at'];

    protected $casts = ['started_at' => 'datetime', 'finished_at' => 'datetime'];

    public function employee() { return $this->belongsTo(User::class, 'user_id'); }
    public function workplace() { return $this->belongsTo(Workplace::class); }
}
