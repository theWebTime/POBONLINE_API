<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientFunction extends Model
{
    use HasFactory;
    protected $fillable = ['id', 'client_id', 'date', 'day_label', 'function_name', 'function_time', 'venue', 'user_id'];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function categories()
    {
        return $this->hasMany(ManageClientCategory::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function manageClientCategories()
    {
        return $this->hasMany(ManageClientCategory::class, 'client_function_id');
    }

    public function organizedDepartments()
    {
        return $this->hasMany(OrganizedDepartment::class, 'client_function_id');
    }
}
