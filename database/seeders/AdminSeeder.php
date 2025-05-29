<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Quotation Hub',
            'phone_number' => '7862813861',
            'studio_name' => 'Quotation Hub',
            'image' => 'null',
            'address' => 'Aarem Square, Iskcon',
            'email' => 'admin@quotationhub.com',
            'password' => bcrypt('Quotation@hub@2025'),
            'role' => 1,
            'status' => '1',
            'subscription_date' => 'null',
            'subscription_end_date' => 'null',
            'instagram_link' => 'https://www.instagram.com/pob_online_official/?utm_source=ig_web_button_share_sheet',
            'facebook_link' => 'https://www.instagram.com/pob_online_official/?utm_source=ig_web_button_share_sheet',
            'youtube_link' => 'https://www.instagram.com/pob_online_official/?utm_source=ig_web_button_share_sheet',
            'website_link' => 'https://www.instagram.com/pob_online_official/?utm_source=ig_web_button_share_sheet',
        ]);
    }
}
