<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParticularFunction extends Model
{
    use HasFactory;
    protected $fillable = ['id', 'name', 'is_multiple_days', 'user_id'];

    public function clients()
    {
        return $this->hasMany(Client::class, 'particular_function_id');
    }
}
