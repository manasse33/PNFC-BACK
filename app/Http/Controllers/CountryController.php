<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Country;

class CountryController extends Controller
{
    // Lister tous les pays
    public function index()
    {
        return response()->json(Country::with('cities', 'entreprises')->get());
    }

    // Créer un nouveau pays
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:countries,name',
            'code' => 'required|string|unique:countries,code',
        ]);

        $country = Country::create($request->all());
        return response()->json($country, 201);
    }

    // Afficher un pays précis
    public function show(Country $country)
    {
        return response()->json($country->load('cities', 'entreprises'));
    }

    // Mettre à jour un pays
    public function update(Request $request, Country $country)
    {
        $request->validate([
            'name' => 'sometimes|string|unique:countries,name,' . $country->id,
            'code' => 'sometimes|string|unique:countries,code,' . $country->id,
        ]);

        $country->update($request->all());
        return response()->json($country);
    }

    // Supprimer un pays
    public function destroy(Country $country)
    {
        $country->delete();
        return response()->json(['message' => 'Pays supprimé avec succès']);
    }
}
