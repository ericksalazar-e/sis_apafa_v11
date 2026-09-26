<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoConsanguinidad extends Model
{
    use HasFactory;

    protected $table = 'tipos_consanguinidad';

    protected $fillable = [
        'nombre',
    ];
}
