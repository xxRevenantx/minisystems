<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Marco extends Model
{
    /** @use HasFactory<\Database\Factories\MarcoFactory> */
    use HasFactory;
    protected $fillable = [
        'marco',
        'descripcion',
    ];

}
