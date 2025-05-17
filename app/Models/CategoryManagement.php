<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryManagement extends Model
{
    use HasFactory;
    protected $fillable = ['id', 'category_role', 'category_price', 'user_id'];

    public function organizedDepartments()
    {
        return $this->hasMany(OrganizeDepartment::class, 'category_management_id');
    }
}
