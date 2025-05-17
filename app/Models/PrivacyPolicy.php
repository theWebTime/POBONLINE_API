<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrivacyPolicy extends Model
{
    use HasFactory;
    protected $fillable = ['id', 'privacy_policy', 'user_id'];

    public function client()
    {
        return $this->belongsTo(Client::class, 'user_id');
    }
}
