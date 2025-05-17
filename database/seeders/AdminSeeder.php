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
            'name' => 'POB',
            'phone_number' => '7862813861',
            'studio_name' => 'POB',
            'image' => 'null',
            'address' => 'Aarem Square, Iskcon',
            'email' => 'pob@gmail.com',
            'password' => bcrypt('123456'),
            'role' => 1,
            'status' => '1',
            'subscription_date' => 'null',
            'instagram_link' => 'https://www.instagram.com/pob_online_official/?utm_source=ig_web_button_share_sheet',
            'facebook_link' => 'https://www.instagram.com/pob_online_official/?utm_source=ig_web_button_share_sheet',
            'youtube_link' => 'https://www.instagram.com/pob_online_official/?utm_source=ig_web_button_share_sheet',
            'website_link' => 'https://www.instagram.com/pob_online_official/?utm_source=ig_web_button_share_sheet',
        ]);
    }
}
