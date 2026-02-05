<?php

namespace App\Core\Http\Controllers\Admin;

use App\Core\Models\BountyStatus;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class BountyStatusController extends Controller
{
    public function index()
    {
        return BountyStatus::orderBy('sort_order')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:bounty_statuses,name',
            'label' => 'required|string',
            'color' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['sort_order'] = $validated['sort_order'] ?? BountyStatus::max('sort_order') + 1;

        return BountyStatus::create($validated);
    }

    public function show(BountyStatus $bountyStatus)
    {
        return $bountyStatus;
    }

    public function update(Request $request, BountyStatus $bountyStatus)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|unique:bounty_statuses,name,' . $bountyStatus->id,
            'label' => 'sometimes|string',
            'color' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        $bountyStatus->update($validated);
        return $bountyStatus;
    }

    public function destroy(BountyStatus $bountyStatus)
    {
        $bountyStatus->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
