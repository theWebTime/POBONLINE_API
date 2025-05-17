<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GenerateBill extends Model
{
    use HasFactory;
    protected $fillable = ['id', 'client_id', 'grand_total', 'discount_percentage', 'slots', 'breakdown'];

    protected $casts = [
        'breakdown' => 'array',
        'slots' => 'array'
    ];
    
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

}
