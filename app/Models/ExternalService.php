<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExternalService extends Model
{
    use HasFactory;
    protected $fillable = ['id', 'service_name', 'service_price', 'user_id'];

    public function client()
    {
        return $this->belongsTo(Client::class, 'user_id');
    }
}
