<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\City;

class CityController extends Controller
{
    // Lister toutes les villes
    public function index()
    {
        return response()->json(City::with('country', 'entreprises', 'formations')->get());
    }

    // Créer une ville
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'country_id' => 'required|exists:countries,id',
        ]);

        $city = City::create($request->all());
        return response()->json($city, 201);
    }

    // Afficher une ville précise
    public function show(City $city)
    {
        return response()->json($city->load('country', 'entreprises', 'formations'));
    }

    // Mettre à jour une ville
    public function update(Request $request, City $city)
    {
        $request->validate([
            'name' => 'sometimes|string',
            'country_id' => 'sometimes|exists:countries,id',
        ]);

        $city->update($request->all());
        return response()->json($city);
    }

    // Supprimer une ville
    public function destroy(City $city)
    {
        $city->delete();
        return response()->json(['message' => 'Ville supprimée avec succès']);
    }
}
