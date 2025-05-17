<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FieldController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'fields' => 'required|array',
            'fields.*' => 'required|string|max:255',
        ]);

        // Save to database if needed
        foreach ($validated['fields'] as $fieldValue) {
            \App\Models\Field::create(['value' => $fieldValue]);
        }

        return response()->json(['message' => 'Fields saved successfully']);
    }
}
