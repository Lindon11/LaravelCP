<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index()
    {
        $companies = Company::withCount('employees')->with('positions')->orderBy('name')->get();
        return response()->json($companies);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'description' => 'required|string',
            'rating' => 'nullable|integer|min:0|max:100',
        ]);
        $company = Company::create($request->all());
        return response()->json(['message' => 'Company created', 'company' => $company], 201);
    }

    public function show(int $id)
    {
        $company = Company::with('positions')->withCount('employees')->findOrFail($id);
        return response()->json($company);
    }

    public function update(Request $request, int $id)
    {
        $company = Company::findOrFail($id);
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'rating' => 'nullable|integer|min:0|max:100',
        ]);
        $company->update($request->all());
        return response()->json(['message' => 'Company updated', 'company' => $company]);
    }

    public function destroy(int $id)
    {
        $company = Company::findOrFail($id);
        
        // Check if company has active employees
        if ($company->employees()->where('status', 'active')->exists()) {
            return response()->json(['error' => 'Cannot delete company with active employees'], 400);
        }
        
        $company->delete();
        return response()->json(['message' => 'Company deleted successfully']);
    }
}
