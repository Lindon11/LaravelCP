<?php

namespace App\Core\Http\Controllers\Admin;

use App\Core\Models\LotteryStatus;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class LotteryStatusController extends Controller
{
    public function index()
    {
        return LotteryStatus::orderBy('sort_order')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:lottery_statuses,name',
            'label' => 'required|string',
            'color' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['sort_order'] = $validated['sort_order'] ?? LotteryStatus::max('sort_order') + 1;

        return LotteryStatus::create($validated);
    }

    public function show(LotteryStatus $lotteryStatus)
    {
        return $lotteryStatus;
    }

    public function update(Request $request, LotteryStatus $lotteryStatus)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|unique:lottery_statuses,name,' . $lotteryStatus->id,
            'label' => 'sometimes|string',
            'color' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        $lotteryStatus->update($validated);
        return $lotteryStatus;
    }

    public function destroy(LotteryStatus $lotteryStatus)
    {
        $lotteryStatus->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
