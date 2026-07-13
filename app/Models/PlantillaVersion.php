<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlantillaVersion extends Model
{
    use HasFactory;

    protected $table = 'plantilla_versiones';

    protected $fillable = ['plantilla_creativa_id','user_id','version','estructura','configuracion','nota'];

    protected function casts(): array
    {
        return ['estructura' => 'array', 'configuracion' => 'array'];
    }

    public function plantilla() { return $this->belongsTo(PlantillaCreativa::class, 'plantilla_creativa_id'); }
}
