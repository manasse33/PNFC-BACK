<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
    ];

    // Relation avec les villes
    public function cities()
    {
        return $this->hasMany(City::class);
    }

    // Relation avec les entreprises
    public function entreprises()
    {
        return $this->hasMany(Entreprise::class);
    }
}
