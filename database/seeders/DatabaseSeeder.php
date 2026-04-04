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

        // Demo property coordinates (Sopot center)
        $property->update(['latitude' => 54.4416, 'longitude' => 18.5601]);

        // Demo photos (placeholder images)
        $photos = [
            ['filename' => 'living-room.jpg', 'path' => 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=800&h=600&fit=crop', 'alt_text_pl' => 'Salon', 'alt_text_en' => 'Living room', 'is_cover' => true, 'sort_order' => 0],
            ['filename' => 'bedroom.jpg', 'path' => 'https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?w=800&h=600&fit=crop', 'alt_text_pl' => 'Sypialnia', 'alt_text_en' => 'Bedroom', 'is_cover' => false, 'sort_order' => 1],
            ['filename' => 'kitchen.jpg', 'path' => 'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=800&h=600&fit=crop', 'alt_text_pl' => 'Kuchnia', 'alt_text_en' => 'Kitchen', 'is_cover' => false, 'sort_order' => 2],
            ['filename' => 'bathroom.jpg', 'path' => 'https://images.unsplash.com/photo-1552321554-5fefe8c9ef14?w=800&h=600&fit=crop', 'alt_text_pl' => 'Lazienka', 'alt_text_en' => 'Bathroom', 'is_cover' => false, 'sort_order' => 3],
            ['filename' => 'balcony.jpg', 'path' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800&h=600&fit=crop', 'alt_text_pl' => 'Balkon z widokiem', 'alt_text_en' => 'Balcony with view', 'is_cover' => false, 'sort_order' => 4],
        ];
        foreach ($photos as $p) {
            Photo::updateOrCreate(
                ['property_id' => $property->id, 'filename' => $p['filename']],
                [...$p, 'property_id' => $property->id]
            );
        }

        // Attach amenities to demo property
        $amenitySlugs = ['wifi', 'parking', 'ac', 'kitchen', 'washer', 'tv', 'balcony', 'coffee_maker', 'towels', 'bed_linen', 'hair_dryer', 'iron'];
        $amenityIds = Amenity::whereIn('slug', $amenitySlugs)->pluck('id');
        $property->amenities()->sync($amenityIds);

        // Contact details
        $contactSettings = [
            'contact_name' => 'Anna Kowalska',
            'contact_email' => 'kontakt@apartament-sopot.pl',
            'contact_phone' => '+48 500 123 456',
            'contact_address' => 'ul. Morska 15/3, 81-735 Sopot',
            'contact_photo' => 'contact/profile.jpg',
            'contact_description_pl' => "Witam serdecznie!\n\nJestem Anna — wlascicielka Apartamentu Slonecznego w Sopocie. Od ponad 5 lat goszcze turystow z calego swiata w moim apartamencie, ktory z miloscia urzadzilam i stale udoskonalam.\n\nDbam o kazdy szczegol — od swiezej poscieli po lokalne rekomendacje restauracji i atrakcji. Zalezy mi, abys czul sie jak w domu.\n\nJesli masz jakiekolwiek pytania dotyczace rezerwacji lub pobytu — pisz lub dzwon smalo!",
            'contact_description_en' => "Welcome!\n\nI'm Anna — the owner of Apartament Sloneczny in Sopot. For over 5 years I've been hosting tourists from around the world in my apartment, which I have lovingly furnished and constantly improve.\n\nI pay attention to every detail — from fresh linens to local restaurant and attraction recommendations. I want you to feel right at home.\n\nIf you have any questions about your reservation or stay — feel free to write or call!",
            'terms_pl' => "REGULAMIN REZERWACJI — Apartament Sloneczny, Sopot\n\n1. REZERWACJA I PLATNOSCI\n1.1. Rezerwacja jest potwierdzona po wplacie zaliczki w wysokosci 30% calkowitej kwoty pobytu.\n1.2. Pozostala czesc nalezy uregulowac najpozniej w dniu zameldowania.\n1.3. Platnosci przyjmujemy przez system PayU (BLIK, karta, przelew) lub gotowka.\n\n2. ZAMELDOWANIE I WYMELDOWANIE\n2.1. Zameldowanie: od godziny 15:00.\n2.2. Wymeldowanie: do godziny 11:00.\n2.3. Wczesniejsze zameldowanie lub pozniejsze wymeldowanie — mozliwe po uzgodnieniu i w zaleznosci od dostepnosci.\n\n3. ANULOWANIE\n3.1. Bezplatne anulowanie do 7 dni przed data przyjazdu — zwrot 100% zaliczki.\n3.2. Anulowanie 3-7 dni przed przyjazdem — zwrot 50% zaliczki.\n3.3. Anulowanie ponizej 3 dni przed przyjazdem — zaliczka nie podlega zwrotowi.\n\n4. ZASADY POBYTU\n4.1. Cisza nocna obowiazuje od 22:00 do 7:00.\n4.2. W apartamencie obowiazuje calkowity zakaz palenia.\n4.3. Zwierzeta domowe sa akceptowane po wczesniejszym uzgodnieniu (oplata dodatkowa 50 PLN/noc).\n4.4. Maksymalna liczba gosci: 4 osoby.\n4.5. Organizowanie imprez i przyjec jest niedozwolone.\n\n5. ODPOWIEDZIALNOSC\n5.1. Gosc ponosi odpowiedzialnosc za wszelkie szkody powstale w apartamencie podczas pobytu.\n5.2. Wlasciciel nie ponosi odpowiedzialnosci za rzeczy pozostawione w apartamencie.\n\n6. DANE OSOBOWE\n6.1. Dane osobowe sa przetwarzane wylacznie w celu realizacji rezerwacji, zgodnie z RODO.\n6.2. Dane nie sa udostepniane podmiotom trzecim.\n\nKontakt: kontakt@apartament-sopot.pl | +48 500 123 456",
            'terms_en' => "BOOKING TERMS & CONDITIONS — Apartament Sloneczny, Sopot\n\n1. BOOKING & PAYMENTS\n1.1. A booking is confirmed upon payment of a 30% deposit of the total stay amount.\n1.2. The remaining balance must be paid no later than the check-in day.\n1.3. We accept payments via PayU (BLIK, card, bank transfer) or cash.\n\n2. CHECK-IN & CHECK-OUT\n2.1. Check-in: from 3:00 PM.\n2.2. Check-out: by 11:00 AM.\n2.3. Early check-in or late check-out — possible upon request and subject to availability.\n\n3. CANCELLATION\n3.1. Free cancellation up to 7 days before arrival — 100% deposit refund.\n3.2. Cancellation 3-7 days before arrival — 50% deposit refund.\n3.3. Cancellation less than 3 days before arrival — deposit is non-refundable.\n\n4. HOUSE RULES\n4.1. Quiet hours: 10:00 PM to 7:00 AM.\n4.2. The apartment is strictly non-smoking.\n4.3. Pets are welcome upon prior arrangement (additional fee of 50 PLN/night).\n4.4. Maximum occupancy: 4 guests.\n4.5. Parties and events are not permitted.\n\n5. LIABILITY\n5.1. Guests are responsible for any damage caused during their stay.\n5.2. The owner is not responsible for belongings left in the apartment.\n\n6. PERSONAL DATA\n6.1. Personal data is processed solely for booking purposes, in compliance with GDPR.\n6.2. Data is not shared with third parties.\n\nContact: kontakt@apartament-sopot.pl | +48 500 123 456",
        ];
        foreach ($contactSettings as $key => $value) {
            Setting::set($key, $value);
        }
    }
}
