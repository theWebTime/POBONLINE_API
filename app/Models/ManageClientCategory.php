<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManageClientCategory extends Model
{
    use HasFactory;
    protected $fillable = ['id', 'client_function_id', 'category_management_id', 'category_quantity', 'user_id'];

    public function clientFunction()
    {
        return $this->belongsTo(ClientFunction::class, 'client_function_id');
    }

    public function categoryManagement()
    {
        return $this->belongsTo(CategoryManagement::class, 'category_management_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function organizedDepartments()
    {
        return $this->hasMany(OrganizeDepartment::class, 'category_management_id', 'category_management_id');
    }
}
