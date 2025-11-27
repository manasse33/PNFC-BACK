<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entreprise extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'sector',
        'adresse',
        'description',
        'logo',
        'country_id',
        'city_id',
        'status',
    ];

    // Relation avec l'utilisateur (propriétaire)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relation avec le pays
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    // Relation avec la ville
    public function city()
    {
        return $this->belongsTo(City::class);
    }

    // Relation avec les documents
    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    // Relation avec les formations
    public function formations()
    {
        return $this->hasMany(Formation::class);
    }

    // Vérifie si l'entreprise est validée
    public function isValidated(): bool
    {
        return $this->status === 'validated';
    }
}
