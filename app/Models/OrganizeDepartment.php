<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizeDepartment extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'id',
        'client_id',
        'client_function_id',
        'manage_client_category_id',
        'category_management_id', // Changed from category_id
        'staff_management_id',
        'user_id' // Added
    ];

    public function staffManagement()
    {
        return $this->belongsTo(StaffManagement::class, 'staff_management_id');
    }

    public function category()
    {
        return $this->belongsTo(CategoryManagement::class, 'category_management_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function clientFunction()
    {
        return $this->belongsTo(ClientFunction::class, 'client_function_id');
    }

    public function manageClientCategory()
    {
        return $this->belongsTo(ManageClientCategory::class, 'manage_client_category_id');
    }

}
