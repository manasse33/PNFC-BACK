<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EntrepriseController;

// Route par défaut générée par Laravel
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// ------------------------------------------------------
// AUTH
// ------------------------------------------------------
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);


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

}); // <-- celle-ci tu l'avais oubliée !
