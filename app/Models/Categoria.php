<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    use HasFactory;

    /**
     * Los atributos que pueden ser asignados en masa.
     *
     * @var array
     */
    protected $fillable = [
        'nombre', 'descripcion',
    ];

    /**
     * Obtiene la relación con los productos.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function productos()
    {
        return $this->hasMany(Producto::class);
    }
}
