<?php

namespace Database\Seeders;

use App\Models\Amenity;
use App\Models\EmailTemplate;
use App\Models\Property;
use App\Models\Photo;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        User::updateOrCreate(['email' => 'admin@stayflow.pl'], [
            'name' => 'Admin',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'locale' => 'pl',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Default settings
        $settings = [
            'brand_name' => 'StayFlow',
            'brand_primary_color' => '#0ea5e9',
            'brand_secondary_color' => '#06b6d4',
            'deposit_percent' => '30',
            'pre_arrival_days' => '3',
            'post_stay_days' => '1',
            'default_currency' => 'PLN',
            'default_locale' => 'pl',
        ];
        foreach ($settings as $key => $value) {
            Setting::set($key, $value);
        }

        // Amenities
        $amenities = [
            ['slug' => 'wifi', 'name_pl' => 'WiFi', 'name_en' => 'WiFi', 'icon' => 'wifi', 'category' => 'general'],
            ['slug' => 'parking', 'name_pl' => 'Parking', 'name_en' => 'Parking', 'icon' => 'car', 'category' => 'general'],
            ['slug' => 'ac', 'name_pl' => 'Klimatyzacja', 'name_en' => 'Air conditioning', 'icon' => 'snowflake', 'category' => 'general'],
            ['slug' => 'kitchen', 'name_pl' => 'Kuchnia', 'name_en' => 'Kitchen', 'icon' => 'utensils', 'category' => 'kitchen'],
            ['slug' => 'washer', 'name_pl' => 'Pralka', 'name_en' => 'Washing machine', 'icon' => 'washer', 'category' => 'general'],
            ['slug' => 'tv', 'name_pl' => 'Telewizor', 'name_en' => 'TV', 'icon' => 'tv', 'category' => 'entertainment'],
            ['slug' => 'pool', 'name_pl' => 'Basen', 'name_en' => 'Pool', 'icon' => 'pool', 'category' => 'outdoor'],
            ['slug' => 'garden', 'name_pl' => 'Ogrod', 'name_en' => 'Garden', 'icon' => 'tree', 'category' => 'outdoor'],
            ['slug' => 'bbq', 'name_pl' => 'Grill', 'name_en' => 'BBQ', 'icon' => 'flame', 'category' => 'outdoor'],
            ['slug' => 'pet_friendly', 'name_pl' => 'Przyjazny zwierzetom', 'name_en' => 'Pet friendly', 'icon' => 'paw', 'category' => 'general'],
            ['slug' => 'children', 'name_pl' => 'Przyjazny dzieciom', 'name_en' => 'Child friendly', 'icon' => 'baby', 'category' => 'general'],
            ['slug' => 'heating', 'name_pl' => 'Ogrzewanie', 'name_en' => 'Heating', 'icon' => 'flame', 'category' => 'general'],
            ['slug' => 'iron', 'name_pl' => 'Zelazko', 'name_en' => 'Iron', 'icon' => 'iron', 'category' => 'general'],
            ['slug' => 'hair_dryer', 'name_pl' => 'Suszarka', 'name_en' => 'Hair dryer', 'icon' => 'wind', 'category' => 'bathroom'],
            ['slug' => 'towels', 'name_pl' => 'Reczniki', 'name_en' => 'Towels', 'icon' => 'towel', 'category' => 'bathroom'],
            ['slug' => 'bed_linen', 'name_pl' => 'Posciel', 'name_en' => 'Bed linen', 'icon' => 'bed', 'category' => 'general'],
            ['slug' => 'dishwasher', 'name_pl' => 'Zmywarka', 'name_en' => 'Dishwasher', 'icon' => 'dish', 'category' => 'kitchen'],
            ['slug' => 'microwave', 'name_pl' => 'Mikrofalowka', 'name_en' => 'Microwave', 'icon' => 'microwave', 'category' => 'kitchen'],
            ['slug' => 'coffee_maker', 'name_pl' => 'Ekspres do kawy', 'name_en' => 'Coffee maker', 'icon' => 'coffee', 'category' => 'kitchen'],
            ['slug' => 'balcony', 'name_pl' => 'Balkon', 'name_en' => 'Balcony', 'icon' => 'balcony', 'category' => 'outdoor'],
            ['slug' => 'elevator', 'name_pl' => 'Winda', 'name_en' => 'Elevator', 'icon' => 'elevator', 'category' => 'general'],
            ['slug' => 'safe', 'name_pl' => 'Sejf', 'name_en' => 'Safe', 'icon' => 'lock', 'category' => 'safety'],
            ['slug' => 'smoke_detector', 'name_pl' => 'Czujnik dymu', 'name_en' => 'Smoke detector', 'icon' => 'alarm', 'category' => 'safety'],
            ['slug' => 'first_aid', 'name_pl' => 'Apteczka', 'name_en' => 'First aid kit', 'icon' => 'first-aid', 'category' => 'safety'],
        ];
        foreach ($amenities as $i => $a) {
            Amenity::updateOrCreate(['slug' => $a['slug']], [...$a, 'sort_order' => $i]);
        }

        // Email templates
        $templates = [
            [
                'slug' => 'booking_confirmation',
                'name' => 'Potwierdzenie rezerwacji',
                'subject_pl' => 'Potwierdzenie rezerwacji {{confirmation_code}}',
                'subject_en' => 'Booking confirmation {{confirmation_code}}',
                'body_pl' => "Witaj {{guest_name}},\n\nDziekujemy za rezerwacje!\n\nObiekt: {{property_name}}\nPrzyjazd: {{check_in}}\nWyjazd: {{check_out}}\nKod: {{confirmation_code}}\nKwota: {{total_price}}\n\nSzczegoly rezerwacji: {{booking_url}}\n\nPozdrawiamy,\nZespol {{brand_name}}",
                'body_en' => "Hello {{guest_name}},\n\nThank you for your booking!\n\nProperty: {{property_name}}\nCheck-in: {{check_in}}\nCheck-out: {{check_out}}\nCode: {{confirmation_code}}\nTotal: {{total_price}}\n\nBooking details: {{booking_url}}\n\nBest regards,\n{{brand_name}} Team",
                'variables' => ['guest_name', 'property_name', 'check_in', 'check_out', 'confirmation_code', 'total_price', 'booking_url', 'brand_name'],
            ],
            [
                'slug' => 'payment_confirmation',
                'name' => 'Potwierdzenie platnosci',
                'subject_pl' => 'Platnosc potwierdzona - {{confirmation_code}}',
                'subject_en' => 'Payment confirmed - {{confirmation_code}}',
                'body_pl' => "Witaj {{guest_name}},\n\nOtrzymalismy Twoja platnosc w wysokosci {{amount}} za rezerwacje {{confirmation_code}}.\n\nPozdrawiamy,\nZespol {{brand_name}}",
                'body_en' => "Hello {{guest_name}},\n\nWe received your payment of {{amount}} for booking {{confirmation_code}}.\n\nBest regards,\n{{brand_name}} Team",
                'variables' => ['guest_name', 'confirmation_code', 'amount', 'brand_name'],
            ],
            [
                'slug' => 'pre_arrival',
                'name' => 'Przypomnienie przed przyjazdem',
                'subject_pl' => 'Przypomnienie - Twoj pobyt juz wkrotce!',
                'subject_en' => 'Reminder - Your stay is coming up!',
                'body_pl' => "Witaj {{guest_name}},\n\nPrzypominamy o nadchodzacym pobycie:\n\nObiekt: {{property_name}}\nPrzyjazd: {{check_in}} (od {{check_in_time}})\nWyjazd: {{check_out}} (do {{check_out_time}})\n\nDo zobaczenia!\nZespol {{brand_name}}",
                'body_en' => "Hello {{guest_name}},\n\nA reminder about your upcoming stay:\n\nProperty: {{property_name}}\nCheck-in: {{check_in}} (from {{check_in_time}})\nCheck-out: {{check_out}} (by {{check_out_time}})\n\nSee you soon!\n{{brand_name}} Team",
                'variables' => ['guest_name', 'property_name', 'check_in', 'check_out', 'check_in_time', 'check_out_time', 'brand_name'],
            ],
            [
                'slug' => 'post_stay',
                'name' => 'Podziekowanie po pobycie',
                'subject_pl' => 'Dziekujemy za pobyt!',
                'subject_en' => 'Thank you for your stay!',
                'body_pl' => "Witaj {{guest_name}},\n\nDziekujemy za pobyt w {{property_name}}!\n\nMamy nadzieje, ze sie podobalo. Bedzie nam milo, jesli podzielisz sie swoja opinia.\n\nZapraszamy ponownie!\nZespol {{brand_name}}",
                'body_en' => "Hello {{guest_name}},\n\nThank you for staying at {{property_name}}!\n\nWe hope you enjoyed your stay. We would appreciate it if you could share your feedback.\n\nWe look forward to seeing you again!\n{{brand_name}} Team",
                'variables' => ['guest_name', 'property_name', 'brand_name'],
            ],
            [
                'slug' => 'booking_cancelled',
                'name' => 'Anulowanie rezerwacji',
                'subject_pl' => 'Rezerwacja {{confirmation_code}} anulowana',
                'subject_en' => 'Booking {{confirmation_code}} cancelled',
                'body_pl' => "Witaj {{guest_name}},\n\nRezerwacja {{confirmation_code}} zostala anulowana.\n\nObiekt: {{property_name}}\nTermin: {{check_in}} - {{check_out}}\n\nJesli masz pytania, skontaktuj sie z nami.\n\nPozdrawiamy,\nZespol {{brand_name}}",
                'body_en' => "Hello {{guest_name}},\n\nBooking {{confirmation_code}} has been cancelled.\n\nProperty: {{property_name}}\nDates: {{check_in}} - {{check_out}}\n\nIf you have any questions, please contact us.\n\nBest regards,\n{{brand_name}} Team",
                'variables' => ['guest_name', 'confirmation_code', 'property_name', 'check_in', 'check_out', 'brand_name'],
            ],
            [
                'slug' => 'admin_new_booking',
                'name' => 'Nowa rezerwacja (admin)',
                'subject_pl' => 'Nowa rezerwacja: {{confirmation_code}}',
                'subject_en' => 'New booking: {{confirmation_code}}',
                'body_pl' => "Nowa rezerwacja w systemie!\n\nGosc: {{guest_name}} ({{guest_email}})\nObiekt: {{property_name}}\nTermin: {{check_in}} - {{check_out}}\nKwota: {{total_price}}\nKod: {{confirmation_code}}",
                'body_en' => "New booking received!\n\nGuest: {{guest_name}} ({{guest_email}})\nProperty: {{property_name}}\nDates: {{check_in}} - {{check_out}}\nTotal: {{total_price}}\nCode: {{confirmation_code}}",
                'variables' => ['guest_name', 'guest_email', 'property_name', 'check_in', 'check_out', 'total_price', 'confirmation_code'],
            ],
        ];
        foreach ($templates as $t) {
            $vars = $t['variables'];
            unset($t['variables']);
            EmailTemplate::updateOrCreate(['slug' => $t['slug']], [...$t, 'variables' => $vars, 'is_active' => true]);
        }

        // Demo property
        $property = Property::updateOrCreate(['slug' => 'apartament-demo'], [
            'name' => 'Apartament Sloneczny',
            'type' => 'apartment',
            'address' => 'ul. Morska 15/3',
            'city' => 'Sopot',
            'postal_code' => '81-735',
            'country' => 'PL',
            'description_pl' => 'Piekny, sloneczny apartament z widokiem na morze. Doskonala lokalizacja w centrum Sopotu, 5 minut spacerem od plazy. Nowocześnie urzadzony, w pelni wyposazony.',
            'description_en' => 'Beautiful, sunny apartment with sea view. Excellent location in the center of Sopot, 5 minutes walk from the beach. Modernly furnished, fully equipped.',
            'max_guests' => 4,
            'bedrooms' => 2,
            'bathrooms' => 1,
            'area_sqm' => 55,
            'base_price_per_night' => 35000,
            'cleaning_fee' => 15000,
            'check_in_time' => '15:00',
            'check_out_time' => '11:00',
            'min_nights' => 2,
            'is_published' => true,
            'reservations_enabled' => true,
        ]);

        // Attach amenities to demo property
        $amenitySlugs = ['wifi', 'parking', 'ac', 'kitchen', 'washer', 'tv', 'balcony', 'coffee_maker', 'towels', 'bed_linen', 'hair_dryer', 'iron'];
        $amenityIds = Amenity::whereIn('slug', $amenitySlugs)->pluck('id');
        $property->amenities()->sync($amenityIds);
    }
}
