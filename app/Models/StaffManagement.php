<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffManagement extends Model
{
    use HasFactory;
    protected $fillable = ['id', 'name', 'phone_number', 'email', 'category_role_id', 'user_id'];

    public function organizedDepartments()
    {
        return $this->hasMany(OrganizeDepartment::class, 'staff_id');
    }
}
