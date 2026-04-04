<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $widget->property->name }} - Availability Calendar</title>
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
            <p class="text-sm text-gray-600">Availability Calendar</p>
        </div>

        <div id="calendar-container">
            <div class="flex items-center justify-between mb-4">
                <button id="prev-month" class="px-3 py-1 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium">
                    &larr; Previous
                </button>
                <span id="month-year" class="font-semibold text-gray-900"></span>
                <button id="next-month" class="px-3 py-1 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium">
                    Next &rarr;
                </button>
            </div>

            <div id="calendar-grid" class="grid grid-cols-7 gap-1"></div>
        </div>

        <div class="mt-4 flex items-center gap-4 text-xs text-gray-600">
            <div class="flex items-center gap-1">
                <div class="w-4 h-4 rounded bg-green-100 border border-green-300"></div>
                <span>Available</span>
            </div>
            <div class="flex items-center gap-1">
                <div class="w-4 h-4 rounded bg-red-100 border border-red-300"></div>
                <span>Unavailable</span>
            </div>
        </div>
    </div>

    <script>
        const propertyId = {{ $widget->property_id }};
        let currentDate = new Date();
        let unavailableDates = [];

        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'];
        const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

        async function fetchAvailability() {
            const start = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1);
            const end = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 0);

            const startStr = start.toISOString().split('T')[0];
            const endStr = end.toISOString().split('T')[0];

            try {
                const response = await fetch(`/api/properties/${propertyId}/availability?start=${startStr}&end=${endStr}`);
                const data = await response.json();
                unavailableDates = data.unavailable_dates || [];
                renderCalendar();
            } catch (error) {
                console.error('Failed to fetch availability:', error);
                renderCalendar();
            }
        }

        function renderCalendar() {
            const grid = document.getElementById('calendar-grid');
            const monthYear = document.getElementById('month-year');

            monthYear.textContent = `${monthNames[currentDate.getMonth()]} ${currentDate.getFullYear()}`;

            grid.innerHTML = '';

            // Day headers
            dayNames.forEach(day => {
                const header = document.createElement('div');
                header.className = 'text-center text-xs font-medium text-gray-500 py-2';
                header.textContent = day;
                grid.appendChild(header);
            });

            const firstDay = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1).getDay();
            const daysInMonth = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 0).getDate();

            // Empty cells before first day
            for (let i = 0; i < firstDay; i++) {
                const empty = document.createElement('div');
                grid.appendChild(empty);
            }

            // Day cells
            for (let day = 1; day <= daysInMonth; day++) {
                const date = new Date(currentDate.getFullYear(), currentDate.getMonth(), day);
                const dateStr = date.toISOString().split('T')[0];
                const isUnavailable = unavailableDates.includes(dateStr);
                const isPast = date < new Date().setHours(0, 0, 0, 0);

                const cell = document.createElement('div');
                cell.className = `aspect-square flex items-center justify-center text-sm rounded cursor-pointer transition ${
                    isUnavailable || isPast
                        ? 'bg-red-100 text-red-700 border border-red-300'
                        : 'bg-green-100 text-green-700 border border-green-300 hover:bg-green-200'
                }`;
                cell.textContent = day;
                grid.appendChild(cell);
            }
        }

        document.getElementById('prev-month').addEventListener('click', () => {
            currentDate.setMonth(currentDate.getMonth() - 1);
            fetchAvailability();
        });

        document.getElementById('next-month').addEventListener('click', () => {
            currentDate.setMonth(currentDate.getMonth() + 1);
            fetchAvailability();
        });

        // Initial load
        fetchAvailability();
    </script>
</body>
</html>
