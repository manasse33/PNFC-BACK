<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;

class RoleController extends Controller
{
    // Lister tous les rôles
    public function index()
    {
        return response()->json(Role::with('users')->get());
    }

    // Créer un nouveau rôle
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',
            'description' => 'nullable|string',
        ]);

        $role = Role::create($request->all());
        return response()->json($role, 201);
    }

    // Afficher un rôle précis
    public function show(Role $role)
    {
        return response()->json($role->load('users'));
    }

    // Mettre à jour un rôle
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'sometimes|string|unique:roles,name,' . $role->id,
            'description' => 'nullable|string',
        ]);

        $role->update($request->all());
        return response()->json($role);
    }

    // Supprimer un rôle
    public function destroy(Role $role)
    {
        $role->delete();
        return response()->json(['message' => 'Rôle supprimé avec succès']);
    }
}
