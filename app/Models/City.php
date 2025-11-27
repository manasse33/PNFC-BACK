<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'country_id',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function entreprises()
    {
        return $this->hasMany(Entreprise::class);
    }

    public function formations()
    {
        return $this->hasMany(Formation::class);
    }
}
