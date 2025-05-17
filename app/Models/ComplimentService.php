<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComplimentService extends Model
{
    use HasFactory;
    protected $fillable = ['id', 'name', 'user_id'];

    public function clients()
    {
        return $this->belongsToMany(Client::class, 'client_compliment_services');
    }
}
