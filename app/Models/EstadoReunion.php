<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstadoReunion extends Model
{
    use HasFactory;

    protected $table = 'estados_reunion';

    protected $fillable = [
        'estado',
    ];
}
