<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BookingComScraperService
{
    /**
     * Parse property data from pasted HTML (no HTTP request needed)
     */
    public static function parseFromHtml(string $html): array
    {
        $data = [
            'name' => null,
            'description' => null,
            'address' => null,
            'city' => null,
            'postal_code' => null,
            'country' => null,
            'latitude' => null,
            'longitude' => null,
            'photos' => [],
            'max_guests' => null,
            'bedrooms' => null,
            'bathrooms' => null,
            'area_sqm' => null,
            'price' => null,
            'amenities' => [],
        ];

        try {
            $jsonLdBlocks = self::parseJsonLd($html);
            $lodging = self::findJsonLdByType($jsonLdBlocks, ['Hotel', 'LodgingBusiness', 'VacationRental', 'Apartment', 'House']);

            $data['name'] = self::extractName($html, $lodging);
            $data['description'] = self::extractDescription($html, $lodging);
            $data['address'] = self::extractAddress($html, $lodging);
            $data['city'] = self::extractCity($html, $lodging);
            $data['country'] = self::extractCountry($html, $lodging);
            $coords = self::extractCoordinates($html, $lodging);
            $data['latitude'] = $coords['lat'];
            $data['longitude'] = $coords['lng'];
            $data['photos'] = self::extractPhotos($html, $lodging);
            $data['max_guests'] = self::extractMaxGuests($html);
            $data['bedrooms'] = self::extractBedrooms($html);
            $data['bathrooms'] = self::extractBathrooms($html);
            $data['area_sqm'] = self::extractArea($html);
            $data['price'] = self::extractPrice($html, $lodging);
            $data['amenities'] = self::extractAmenities($html, $lodging);
        } catch (\Exception $e) {
            Log::error('Booking.com HTML parse exception: ' . $e->getMessage());
        }

        return $data;
    }

    /**
     * Scrape property data from a Booking.com URL
     *
     * @param string $url
     * @return array
     */
    public static function scrape(string $url): array
    {
        $data = [
            'name' => null,
            'description' => null,
            'address' => null,
            'city' => null,
            'postal_code' => null,
            'country' => null,
            'latitude' => null,
            'longitude' => null,
            'photos' => [],
            'max_guests' => null,
            'bedrooms' => null,
            'bathrooms' => null,
            'area_sqm' => null,
            'price' => null,
            'amenities' => [],
        ];

        try {
            // Resolve share/short URLs by following redirects
            $resolvedUrl = self::resolveUrl($url);

            // Fetch the page with a realistic User-Agent
            $response = Http::timeout(30)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
                    'Accept-Language' => 'pl-PL,pl;q=0.9,en-US;q=0.8,en;q=0.7',
                    'Accept-Encoding' => 'gzip, deflate, br',
                    'Cache-Control' => 'no-cache',
                    'Sec-Fetch-Dest' => 'document',
                    'Sec-Fetch-Mode' => 'navigate',
                    'Sec-Fetch-Site' => 'none',
                    'Sec-Fetch-User' => '?1',
                    'Upgrade-Insecure-Requests' => '1',
                ])
                ->withOptions(['allow_redirects' => ['max' => 10, 'track_redirects' => true]])
                ->get($resolvedUrl);

            if (!$response->successful()) {
                Log::warning('Booking.com scrape failed: HTTP ' . $response->status() . ' for URL: ' . $resolvedUrl);
                return $data;
            }

            $html = $response->body();

            // If Booking.com returned a challenge/blocked page, try to extract name from URL
            if ($response->status() === 202 || strlen($html) < 10000) {
                $data['name'] = self::extractNameFromUrl($resolvedUrl);
                $data['city'] = self::extractCityFromUrl($resolvedUrl);
                Log::info('Booking.com returned challenge page, extracted from URL: ' . $resolvedUrl);
                return $data;
            }

            // Parse JSON-LD once
            $jsonLdBlocks = self::parseJsonLd($html);
            $lodging = self::findJsonLdByType($jsonLdBlocks, ['Hotel', 'LodgingBusiness', 'VacationRental', 'Apartment', 'House']);

            // Extract data from various sources
            $data['name'] = self::extractName($html, $lodging);
            $data['description'] = self::extractDescription($html, $lodging);
            $data['address'] = self::extractAddress($html, $lodging);
            $data['city'] = self::extractCity($html, $lodging);
            $data['country'] = self::extractCountry($html, $lodging);
            $coords = self::extractCoordinates($html, $lodging);
            $data['latitude'] = $coords['lat'];
            $data['longitude'] = $coords['lng'];
            $data['photos'] = self::extractPhotos($html, $lodging);
            $data['max_guests'] = self::extractMaxGuests($html);
            $data['bedrooms'] = self::extractBedrooms($html);
            $data['bathrooms'] = self::extractBathrooms($html);
            $data['area_sqm'] = self::extractArea($html);
            $data['price'] = self::extractPrice($html, $lodging);
            $data['amenities'] = self::extractAmenities($html, $lodging);
        } catch (\Exception $e) {
            Log::error('Booking.com scrape exception: ' . $e->getMessage());
        }

        return $data;
    }

    /**
     * Resolve short/share URLs to the actual property URL
     */
    private static function resolveUrl(string $url): string
    {
        // Handle Booking.com share URLs (e.g. /Share-XXXXX)
        if (preg_match('/booking\.com\/Share-/i', $url)) {
            try {
                $response = Http::timeout(15)
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
                    ])
                    ->withOptions([
                        'allow_redirects' => ['max' => 10, 'track_redirects' => true],
                    ])
                    ->get($url);

                // Get the final URL after redirects
                $redirectHistory = $response->header('X-Guzzle-Redirect-History');
                if ($redirectHistory) {
                    $redirects = explode(', ', $redirectHistory);
                    return end($redirects);
                }

                // Check for meta refresh or JavaScript redirect in HTML
                $html = $response->body();
                if (preg_match('/url=(["\']?)(https?:\/\/[^"\'\s>]+)/i', $html, $m)) {
                    return $m[2];
                }
                if (preg_match('/window\.location\s*=\s*["\']([^"\']+)/i', $html, $m)) {
                    return $m[1];
                }

                // Effective URL from response
                $effectiveUrl = $response->effectiveUri()?->__toString();
                if ($effectiveUrl && $effectiveUrl !== $url) {
                    return $effectiveUrl;
                }
            } catch (\Exception $e) {
                Log::warning('Failed to resolve Booking.com share URL: ' . $e->getMessage());
            }
        }

        return $url;
    }

    /**
     * Parse all JSON-LD blocks from the HTML
     */
    private static function parseJsonLd(string $html): array
    {
        $results = [];
        if (preg_match_all('/<script\s+type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/si', $html, $matches)) {
            foreach ($matches[1] as $json) {
                $decoded = json_decode(trim($json), true);
                if ($decoded) {
                    // Handle @graph arrays
                    if (isset($decoded['@graph'])) {
                        foreach ($decoded['@graph'] as $item) {
                            $results[] = $item;
                        }
                    } else {
                        $results[] = $decoded;
                    }
                }
            }
        }
        return $results;
    }

    /**
     * Find a JSON-LD block by @type
     */
    private static function findJsonLdByType(array $jsonLdBlocks, array $types): ?array
    {
        foreach ($jsonLdBlocks as $block) {
            $blockType = $block['@type'] ?? '';
            foreach ($types as $type) {
                if (strcasecmp($blockType, $type) === 0) {
                    return $block;
                }
            }
        }
        return null;
    }

    /**
     * Extract property name from the Booking.com URL path
     */
    private static function extractNameFromUrl(string $url): ?string
    {
        // URL format: /hotel/pl/property-name-city.pl.html
        if (preg_match('/\/hotel\/[a-z]{2}\/([^.?]+)/i', $url, $m)) {
            $slug = $m[1];
            // Remove trailing city/language parts and convert dashes to spaces
            $name = str_replace('-', ' ', $slug);
            return mb_convert_case(trim($name), MB_CASE_TITLE, 'UTF-8');
        }
        return null;
    }

    /**
     * Extract city from the Booking.com URL path
     */
    private static function extractCityFromUrl(string $url): ?string
    {
        // Sometimes the URL contains the city at the end: property-name-city.pl.html
        if (preg_match('/\/hotel\/([a-z]{2})\//i', $url, $m)) {
            $countryCode = strtoupper($m[1]);
            // Can't reliably extract city from URL alone
        }
        return null;
    }

    /**
     * Extract property name from HTML
     */
    private static function extractName(string $html, ?array $lodging): ?string
    {
        // Try JSON-LD
        if ($lodging && isset($lodging['name'])) {
            return $lodging['name'];
        }

        // Try og:title
        if (preg_match('/<meta\s+property=["\']og:title["\']\s+content=["\'](.*?)["\']/', $html, $matches)) {
            return html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        // Try h2 with specific class
        if (preg_match('/<h2[^>]*class=["\'][^"\']*pp-header__title[^"\']*["\'][^>]*>(.*?)<\/h2>/s', $html, $matches)) {
            return trim(strip_tags($matches[1]));
        }

        // Try title tag
        if (preg_match('/<title>(.*?)<\/title>/', $html, $matches)) {
            $title = html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $title = preg_replace('/\s*[-|]\s*Booking\.com.*$/i', '', $title);
            return trim($title) ?: null;
        }

        return null;
    }

    private static function extractDescription(string $html, ?array $lodging): ?string
    {
        if ($lodging && isset($lodging['description'])) {
            return $lodging['description'];
        }

        if (preg_match('/<meta\s+property=["\']og:description["\']\s+content=["\'](.*?)["\']/', $html, $matches)) {
            return html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        if (preg_match('/<meta\s+name=["\']description["\']\s+content=["\'](.*?)["\']/', $html, $matches)) {
            return html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        return null;
    }

    private static function extractAddress(string $html, ?array $lodging): ?string
    {
        if ($lodging && isset($lodging['address']['streetAddress'])) {
            return $lodging['address']['streetAddress'];
        }
        return null;
    }

    private static function extractCity(string $html, ?array $lodging): ?string
    {
        if ($lodging && isset($lodging['address']['addressLocality'])) {
            return $lodging['address']['addressLocality'];
        }
        return null;
    }

    private static function extractCountry(string $html, ?array $lodging): ?string
    {
        if ($lodging && isset($lodging['address']['addressCountry'])) {
            return $lodging['address']['addressCountry'];
        }
        return null;
    }

    private static function extractCoordinates(string $html, ?array $lodging): array
    {
        if ($lodging && isset($lodging['geo']['latitude'], $lodging['geo']['longitude'])) {
            return ['lat' => (float) $lodging['geo']['latitude'], 'lng' => (float) $lodging['geo']['longitude']];
        }

        // Try b_map_center_* patterns used by Booking.com
        if (preg_match('/b_map_center_latitude["\']?\s*[:=]\s*["\']?([0-9.-]+)/i', $html, $latMatch) &&
            preg_match('/b_map_center_longitude["\']?\s*[:=]\s*["\']?([0-9.-]+)/i', $html, $lngMatch)) {
            return ['lat' => (float) $latMatch[1], 'lng' => (float) $lngMatch[1]];
        }

        if (preg_match('/latitude["\']?\s*:\s*([0-9.-]+)/i', $html, $latMatch) &&
            preg_match('/longitude["\']?\s*:\s*([0-9.-]+)/i', $html, $lngMatch)) {
            return ['lat' => (float) $latMatch[1], 'lng' => (float) $lngMatch[1]];
        }

        return ['lat' => null, 'lng' => null];
    }

    private static function extractPhotos(string $html, ?array $lodging): array
    {
        $photos = [];

        // Try JSON-LD image
        if ($lodging) {
            $images = $lodging['image'] ?? $lodging['photo'] ?? [];
            if (is_string($images)) $images = [$images];
            if (is_array($images)) {
                foreach ($images as $img) {
                    $url = is_string($img) ? $img : ($img['contentUrl'] ?? $img['url'] ?? null);
                    if ($url) $photos[] = $url;
                }
            }
        }

        // Try og:image
        if (preg_match_all('/<meta\s+property=["\']og:image["\']\s+content=["\'](.*?)["\']/', $html, $matches)) {
            foreach ($matches[1] as $url) {
                if (!in_array($url, $photos)) $photos[] = $url;
            }
        }

        // Booking.com CDN images (bstatic.com)
        if (preg_match_all('/https?:\/\/cf\.bstatic\.com\/[^"\'\s<>]+\.(?:jpg|jpeg|png|webp)/i', $html, $matches)) {
            foreach ($matches[0] as $url) {
                // Prefer larger versions — replace /max300/ or /square60/ with /max1024x768/
                $url = preg_replace('/\/max\d+(?:x\d+)?\//', '/max1024x768/', $url);
                $url = preg_replace('/\/square\d+\//', '/max1024x768/', $url);
                if (!in_array($url, $photos)) $photos[] = $url;
            }
        }

        return array_slice(array_unique($photos), 0, 20);
    }

    /**
     * Extract maximum guests
     */
    private static function extractMaxGuests(string $html): ?int
    {
        // Look for patterns like "4 guests", "Sleeps 4", etc.
        if (preg_match('/(\d+)\s*(?:guests?|people|persons?|sleeps)/i', $html, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    /**
     * Extract number of bedrooms
     */
    private static function extractBedrooms(string $html): ?int
    {
        // Look for patterns like "2 bedrooms", "2-bedroom"
        if (preg_match('/(\d+)\s*(?:-?\s*bedrooms?)/i', $html, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    /**
     * Extract number of bathrooms
     */
    private static function extractBathrooms(string $html): ?int
    {
        // Look for patterns like "1 bathroom", "2 bathrooms"
        if (preg_match('/(\d+)\s*(?:bathrooms?)/i', $html, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    /**
     * Extract area in square meters
     */
    private static function extractArea(string $html): ?int
    {
        // Look for patterns like "50 m²", "50m2", "50 sqm"
        if (preg_match('/(\d+)\s*(?:m²|m2|sqm)/i', $html, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    private static function extractPrice(string $html, ?array $lodging): ?int
    {
        // Try JSON-LD priceRange
        if ($lodging && isset($lodging['priceRange'])) {
            if (preg_match('/(\d+(?:[.,]\d+)?)/', $lodging['priceRange'], $m)) {
                $price = str_replace(',', '.', $m[1]);
                return (int) (floatval($price) * 100);
            }
        }

        if (preg_match('/(\d+(?:[.,]\d+)?)\s*(?:PLN|zł)/i', $html, $matches)) {
            $price = str_replace(',', '.', $matches[1]);
            return (int) (floatval($price) * 100);
        }

        return null;
    }

    private static function extractAmenities(string $html, ?array $lodging): array
    {
        $amenities = [];

        // Try JSON-LD amenityFeature
        if ($lodging && isset($lodging['amenityFeature'])) {
            foreach ($lodging['amenityFeature'] as $feature) {
                $name = is_string($feature) ? $feature : ($feature['name'] ?? null);
                if ($name) $amenities[] = strtolower($name);
            }
        }

        $keywords = [
            'wifi', 'wi-fi', 'parking', 'kitchen', 'air conditioning', 'klimatyzacja',
            'washing machine', 'pralka', 'dishwasher', 'zmywarka', 'tv', 'telewizor',
            'balcony', 'balkon', 'terrace', 'taras', 'pool', 'basen',
            'elevator', 'winda', 'iron', 'zelazko', 'hair dryer', 'suszarka',
            'coffee', 'kawa', 'microwave', 'mikrofalowka', 'safe', 'sejf',
        ];

        foreach ($keywords as $keyword) {
            if (stripos($html, $keyword) !== false && !in_array($keyword, $amenities)) {
                $amenities[] = $keyword;
            }
        }

        return array_unique($amenities);
    }
}
