<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BlockedDate;
use Illuminate\Http\Request;

class BlockedDateController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'property_id' => ['required', 'exists:properties,id'],
            'date_from' => ['required', 'date'],
            'date_to' => ['required', 'date', 'after_or_equal:date_from'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        BlockedDate::create([
            ...$validated,
            'source' => 'admin',
        ]);

        return back()->with('success', 'Daty zablokowane.');
    }

    public function destroy(BlockedDate $blockedDate)
    {
        $blockedDate->delete();

        return back()->with('success', 'Blokada usunięta.');
    }
}
