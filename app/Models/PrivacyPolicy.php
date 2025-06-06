<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrivacyPolicy extends Model
{
    use HasFactory;
    protected $fillable = ['id', 'privacy_policy', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
