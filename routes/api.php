<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EntrepriseController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\FormationController;



// Route par défaut générée par Laravel
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// ------------------------------------------------------
// AUTH
// ------------------------------------------------------
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);

Route::prefix('admin')->group(function () {
    // Countries
    Route::get('countries', [CountryController::class, 'index']);
    Route::post('countries', [CountryController::class, 'store']);
    Route::get('countries/{country}', [CountryController::class, 'show']);
    Route::put('countries/{country}', [CountryController::class, 'update']);
    Route::delete('countries/{country}', [CountryController::class, 'destroy']);

    // Cities
    Route::get('cities', [CityController::class, 'index']);
    Route::post('cities', [CityController::class, 'store']);
    Route::get('cities/{city}', [CityController::class, 'show']);
    Route::put('cities/{city}', [CityController::class, 'update']);
    Route::delete('cities/{city}', [CityController::class, 'destroy']);

     Route::get('roles', [RoleController::class, 'index']);
    Route::post('roles', [RoleController::class, 'store']);
    Route::get('roles/{role}', [RoleController::class, 'show']);
    Route::put('roles/{role}', [RoleController::class, 'update']);
    Route::delete('roles/{role}', [RoleController::class, 'destroy']);
});
// ------------------------------------------------------
// ROUTES PROTÉGÉES
// ------------------------------------------------------
Route::middleware('auth:sanctum')->group(function () {

    // utilisateur connecté
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Entreprise (uniquement role entreprise)
    Route::middleware('role:entreprise')->group(function () {
        Route::post('/entreprise', [EntrepriseController::class, 'store']);
        Route::put('/entreprise/{id}', [EntrepriseController::class, 'update']);
        Route::get('/entreprise/{id}', [EntrepriseController::class, 'show']);
    });

}); 


// PUBLIC
// ENTREPRISE (protégé par Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('formations/entreprise', [FormationController::class, 'getFormationsByEntreprise']);
    Route::get('formations/stats', [FormationController::class, 'stats']);

    Route::post('/formations', [FormationController::class, 'store']);
    Route::put('/formations/{id}', [FormationController::class, 'update']);
    Route::delete('/formations/{id}', [FormationController::class, 'destroy']);
});

// ROUTES PUBLIQUES
Route::get('/formations', [FormationController::class, 'index']);
Route::get('/formations/{id}', [FormationController::class, 'show']); // doit être dernier
