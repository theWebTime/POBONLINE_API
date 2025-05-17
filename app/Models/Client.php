<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\ComplimentService;

class Client extends Model
{
    use HasFactory;
    protected $fillable = ['id', 'name', 'phone_number', 'address', 'starting_date', 'particular_function_id', 'user_id', 'is_booked'];

    public function particularFunction()
    {
        return $this->belongsTo(ParticularFunction::class, 'particular_function_id');
    }

    public function clientFunctions(): HasMany
    {
        return $this->hasMany(ClientFunction::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function complimentServices()
    {
        return $this->belongsToMany(ComplimentService::class, 'client_compliment_services');
    }

    public function generateBill()
    {
        return $this->hasOne(GenerateBill::class);
        // or hasMany() if multiple bills are possible
    }

    public function organizedDepartments()
    {
        return $this->hasMany(OrganizeDepartment::class, 'client_id');
    }

    public function yourStory()
    {
        return $this->hasOne(YourStory::class, 'user_id', 'user_id');
    }

    public function privacyPolicy()
    {
        return $this->hasOne(PrivacyPolicy::class);
    }

    public function externalServices()
    {
        return $this->hasMany(ExternalService::class);
    }
}
