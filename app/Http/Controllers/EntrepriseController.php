<?php

namespace App\Http\Controllers;

use App\Models\Entreprise;
use Illuminate\Http\Request;

class EntrepriseController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string',
            'sector'      => 'required|string',
            'adresse'     => 'required|string',
            'description' => 'nullable|string',
            'country_id'  => 'required|exists:countries,id',
            'city_id'     => 'required|exists:cities,id'
        ]);

        $entreprise = Entreprise::create([
            'user_id'     => $request->user()->id,
            'name'        => $request->name,
            'sector'      => $request->sector,
            'adresse'     => $request->adresse,
            'description' => $request->description,
            'country_id'  => $request->country_id,
            'city_id'     => $request->city_id,
            'status'      => 'pending'
        ]);

        return response()->json($entreprise, 201);
    }

    public function update(Request $request, $id)
    {
        $entreprise = Entreprise::where('user_id', $request->user()->id)->findOrFail($id);

        $entreprise->update($request->all());

        return response()->json($entreprise);
    }

    public function show($id)
    {
        return Entreprise::with(['country', 'city', 'documents'])->findOrFail($id);
    }
}
