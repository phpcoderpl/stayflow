<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Inertia\Inertia;

class PublicPageController extends Controller
{
    public function contact()
    {
        return Inertia::render('Public/Contact', [
            'contact' => [
                'name' => Setting::get('contact_name', ''),
                'email' => Setting::get('contact_email', ''),
                'phone' => Setting::get('contact_phone', ''),
                'address' => Setting::get('contact_address', ''),
                'description_pl' => Setting::get('contact_description_pl', ''),
                'description_en' => Setting::get('contact_description_en', ''),
                'photo' => Setting::get('contact_photo', ''),
            ],
            'brandName' => Setting::get('brand_name', 'StayFlow'),
        ]);
    }

    public function terms()
    {
        return Inertia::render('Public/Terms', [
            'terms_pl' => Setting::get('terms_pl', ''),
            'terms_en' => Setting::get('terms_en', ''),
            'brandName' => Setting::get('brand_name', 'StayFlow'),
        ]);
    }
}
