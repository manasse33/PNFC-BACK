<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Entreprise;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{

    public function register(Request $request)
{
    try {

        // 1️⃣ VALIDATION
        $request->validate([
            // Infos utilisateur
            'role_id'        => 'required|exists:roles,id',
            'name'           => 'required|string',
            'email'          => 'required|email|unique:users,email',
            'password'       => 'required|string|min:6',
            'genre'          => 'nullable|string',
            'phone'          => 'nullable|string',
            'date_naissance' => 'nullable|date',

            // Infos entreprise
            'entreprise_name'        => 'required_if:role_id,3', // par ex. 3 = entreprise
            'entreprise_sector'      => 'nullable|string',
            'entreprise_adresse'     => 'nullable|string',
            'entreprise_description' => 'nullable|string',
            'entreprise_country_id'  => 'nullable|integer',
            'entreprise_city_id'     => 'nullable|integer',

            // Logo
            'logo' => 'nullable|image|max:2048',

            // Documents multiples
            'documents.*' => 'nullable|file|max:5000',
        ]);

        // 2️⃣ CREATE USER + OTP
        $otp = rand(100000, 999999);

        $user = User::create([
            'role_id'        => $request->role_id,
            'name'           => $request->name,
            'email'          => $request->email,
            'password'       => Hash::make($request->password),
            'genre'          => $request->genre,
            'phone'          => $request->phone,
            'date_naissance' => $request->date_naissance,
            'otp_code'       => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);


        // 3️⃣ SI ROLE = ENTREPRISE
        $entrepriseRoleId = Role::where('name', 'entreprise')->value('id');

        $entreprise = null;

        if ($user->role_id == $entrepriseRoleId) {

            // UPLOAD LOGO
            $logoPath = null;
            if ($request->hasFile('logo')) {
                $logoPath = $request->file('logo')->store('logos', 'public');
            }

            // CREATION ENTREPRISE
            $entreprise = Entreprise::create([
                'user_id'     => $user->id,
                'name'        => $request->entreprise_name,
                'sector'      => $request->entreprise_sector,
                'adresse'     => $request->entreprise_adresse,
                'description' => $request->entreprise_description,
                'logo'        => $logoPath,
                'country_id'  => $request->entreprise_country_id,
                'city_id'     => $request->entreprise_city_id,
                'status'      => 'pending',
            ]);

            // 4️⃣ UPLOAD DES DOCUMENTS
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $path = $file->store('documents', 'public');

                    Document::create([
                        'entreprise_id' => $entreprise->id,
                        'title'         => $file->getClientOriginalName(),
                        'file_path'     => $path,
                        'status'        => 'pending',
                    ]);
                }
            }
        }

        // 5️⃣ SUCCESS
        return response()->json([
            'message'    => 'Compte créé. Vérifiez votre email/SMS pour l’OTP.',
            'otp'        => $otp, // à supprimer en production
            'entreprise' => $entreprise
        ], 201);


    } catch (ValidationException $e) {
        return response()->json(['message' => 'Erreur de validation', 'errors' => $e->errors()], 422);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Erreur lors de la création du compte',
            'error'   => $e->getMessage()
        ], 500);
    }
}



    public function login(Request $request)
{
    try {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Identifiants incorrects'], 401);
        }

        if (!$user->email_verified_at) {
            return response()->json([
                'message' => 'Votre compte n’est pas encore activé. Veuillez valider l’OTP.'
            ], 403);
        }

        // Récupérer le rôle (belongsTo)
        $role = $user->role ? $user->role->name : null;
        $user->load('entreprise');

        // Générer le token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Connexion réussie',
            'token'   => $token,
            'user'    => $user,
            'role'    => $role
        ]);

    } catch (ValidationException $e) {
        return response()->json([
            'message' => 'Erreur de validation',
            'errors'  => $e->errors()
        ], 422);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Erreur lors de la connexion',
            'error'   => $e->getMessage()
        ], 500);
    }
}

    public function verifyOtp(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'otp'   => 'required|string',
            ]);

            $user = User::where('email', $request->email)->firstOrFail();

            if (!$user->isOtpValid($request->otp)) {
                return response()->json(['message' => "OTP invalide ou expiré"], 422);
            }

            $user->update([
                'otp_code'        => null,
                'otp_expires_at'  => null,
                'email_verified_at' => now(),
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

          

            return response()->json([
                'message' => "Compte activé avec succès !",
                'token'   => $token,
                'user'    => $user,
            ]);

        } catch (ValidationException $e) {
            return response()->json(['message' => 'Erreur de validation', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de la vérification OTP', 'error' => $e->getMessage()], 500);
        }
    }

    public function me(Request $request)
    {
        return $request->user();
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->tokens()->delete();
            return response()->json(['message' => 'Déconnecté avec succès']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de la déconnexion', 'error' => $e->getMessage()], 500);
        }
    }
}
