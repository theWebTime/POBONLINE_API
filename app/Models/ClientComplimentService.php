<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientComplimentService extends Model
{
    use HasFactory;
    protected $fillable = ['id', 'client_id', 'compliment_service_id'];
}
