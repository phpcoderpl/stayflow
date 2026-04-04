<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $widget->property->name }} - Book Now</title>
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
        <div class="mb-4">
            <h2 class="text-xl font-bold text-gray-900">{{ $widget->property->name }}</h2>
            <p class="text-sm text-gray-600">Book your stay</p>
        </div>

        <form id="booking-form" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Check-in</label>
                <input
                    type="date"
                    id="check_in"
                    name="check_in"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Check-out</label>
                <input
                    type="date"
                    id="check_out"
                    name="check_out"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Number of guests</label>
                <input
                    type="number"
                    id="guests"
                    name="guests"
                    min="1"
                    max="{{ $widget->property->max_guests }}"
                    value="2"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                />
            </div>

            <div id="price-display" class="hidden bg-sky-50 border border-sky-200 rounded-lg p-4 space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600"><span id="nights-count"></span> nights</span>
                    <span class="font-medium text-gray-900" id="accommodation-price"></span>
                </div>
                <div id="cleaning-fee-row" class="hidden flex justify-between text-sm">
                    <span class="text-gray-600">Cleaning fee</span>
                    <span class="font-medium text-gray-900" id="cleaning-fee"></span>
                </div>
                <div class="border-t border-sky-200 pt-2 flex justify-between">
                    <span class="font-semibold text-gray-900">Total</span>
                    <span class="font-bold text-sky-600 text-lg" id="total-price"></span>
                </div>
            </div>

            <div id="error-message" class="hidden bg-red-50 border border-red-200 rounded-lg p-3 text-sm text-red-700"></div>

            <button
                type="button"
                id="check-availability-btn"
                class="w-full py-3 rounded-lg bg-sky-500 hover:bg-sky-600 text-white font-medium transition"
            >
                Check Availability
            </button>

            <button
                type="button"
                id="book-now-btn"
                class="hidden w-full py-3 rounded-lg bg-green-500 hover:bg-green-600 text-white font-medium transition"
            >
                Book Now
            </button>
        </form>
    </div>

    <script>
        const propertyId = {{ $widget->property_id }};
        const propertyUrl = '{{ url("/properties/" . $widget->property->slug) }}';

        document.getElementById('check-availability-btn').addEventListener('click', async () => {
            const checkIn = document.getElementById('check_in').value;
            const checkOut = document.getElementById('check_out').value;
            const guests = document.getElementById('guests').value;

            if (!checkIn || !checkOut) {
                showError('Please select check-in and check-out dates');
                return;
            }

            try {
                const response = await fetch('/bookings/check-availability', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        property_id: propertyId,
                        check_in: checkIn,
                        check_out: checkOut,
                        guests_count: guests
                    })
                });

                const data = await response.json();

                if (response.ok && data.available) {
                    hideError();
                    displayPrice(data);
                    document.getElementById('check-availability-btn').classList.add('hidden');
                    document.getElementById('book-now-btn').classList.remove('hidden');
                } else {
                    showError(data.message || 'Dates not available');
                }
            } catch (error) {
                showError('Failed to check availability');
            }
        });

        document.getElementById('book-now-btn').addEventListener('click', () => {
            const checkIn = document.getElementById('check_in').value;
            const checkOut = document.getElementById('check_out').value;
            const guests = document.getElementById('guests').value;

            const url = `${propertyUrl}?check_in=${checkIn}&check_out=${checkOut}&guests=${guests}`;
            window.open(url, '_blank');
        });

        function displayPrice(data) {
            document.getElementById('nights-count').textContent = data.nights;
            document.getElementById('accommodation-price').textContent = formatPrice(data.accommodation_total);

            if (data.cleaning_fee > 0) {
                document.getElementById('cleaning-fee-row').classList.remove('hidden');
                document.getElementById('cleaning-fee').textContent = formatPrice(data.cleaning_fee);
            }

            document.getElementById('total-price').textContent = formatPrice(data.total_price);
            document.getElementById('price-display').classList.remove('hidden');
        }

        function formatPrice(amount) {
            return new Intl.NumberFormat('pl-PL', {
                style: 'currency',
                currency: 'PLN'
            }).format(amount);
        }

        function showError(message) {
            const errorDiv = document.getElementById('error-message');
            errorDiv.textContent = message;
            errorDiv.classList.remove('hidden');
            document.getElementById('price-display').classList.add('hidden');
            document.getElementById('book-now-btn').classList.add('hidden');
            document.getElementById('check-availability-btn').classList.remove('hidden');
        }

        function hideError() {
            document.getElementById('error-message').classList.add('hidden');
        }

        // Set min date to today
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('check_in').setAttribute('min', today);
        document.getElementById('check_out').setAttribute('min', today);

        // Update check-out min date when check-in changes
        document.getElementById('check_in').addEventListener('change', (e) => {
            const checkIn = new Date(e.target.value);
            checkIn.setDate(checkIn.getDate() + 1);
            document.getElementById('check_out').setAttribute('min', checkIn.toISOString().split('T')[0]);
        });
    </script>
</body>
</html>
