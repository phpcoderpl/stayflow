<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $widget->property->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#0ea5e9',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-white">
    <div class="p-4">
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden hover:shadow-lg transition">
            @if($widget->property->cover_photo)
            <div class="aspect-video overflow-hidden">
                <img
                    src="{{ Storage::url($widget->property->cover_photo) }}"
                    alt="{{ $widget->property->name }}"
                    class="w-full h-full object-cover hover:scale-105 transition-transform duration-300"
                />
            </div>
            @endif

            <div class="p-4 space-y-3">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">{{ $widget->property->name }}</h3>
                    <p class="text-sm text-gray-600">{{ $widget->property->city }}, {{ $widget->property->country }}</p>
                </div>

                <div class="flex items-center gap-4 text-sm text-gray-600">
                    <div class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span>{{ $widget->property->max_guests }} guests</span>
                    </div>
                    @if($widget->property->bedrooms)
                    <div class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        <span>{{ $widget->property->bedrooms }} bedrooms</span>
                    </div>
                    @endif
                </div>

                @if($widget->property->description_en || $widget->property->description_pl)
                <p class="text-sm text-gray-700 line-clamp-2">
                    {{ Str::limit(strip_tags($widget->property->description_en ?: $widget->property->description_pl), 120) }}
                </p>
                @endif

                <div class="pt-2 border-t border-gray-200">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <span class="text-2xl font-bold text-gray-900">{{ number_format($widget->property->base_price, 0) }} PLN</span>
                            <span class="text-sm text-gray-600">/ night</span>
                        </div>
                    </div>

                    <a
                        href="{{ url('/properties/' . $widget->property->slug) }}"
                        target="_blank"
                        class="block w-full py-2.5 rounded-lg bg-sky-500 hover:bg-sky-600 text-white text-center font-medium transition"
                    >
                        Book Now
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
