<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'entreprise_id',
        'title',
        'file_path',
        'status',
    ];

    // Relation vers l'entreprise
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    // Méthodes d'aide
    public function isValidated(): bool
    {
        return $this->status === 'validated';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}
