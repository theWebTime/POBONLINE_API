<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class YourStory extends Model
{
    use HasFactory;
    protected $fillable = ['id', 'image', 'image2', 'user_id'];

    /* public function getImageAttribute($value)
    {
        $host = request()->getSchemeAndHttpHost();
        if ($value) {
            return $host . '/images/yourStory/' . $value;
        } else {
            return null;
        }
    } */
}
