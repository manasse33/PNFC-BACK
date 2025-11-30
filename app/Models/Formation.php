<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Formation extends Model
{
    use HasFactory;

    protected $fillable = [
        'entreprise_id',
        'title',
        'description',
        'resume',
        'programme',
        'sector',
        'city_id',
        'price',
        'duree',
        'end_date',
        'image_couverture',
        'views'
    ];

    protected $casts = [
        'end_date' => 'date',
    ];

    // Relation avec l'entreprise
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    // Relation avec la ville
    public function city()
    {
        return $this->belongsTo(City::class);
    }

   

    // Vérifie si la formation est terminée
    public function isFinished(): bool
    {
        return $this->end_date ? $this->end_date->isPast() : false;
    }
}
