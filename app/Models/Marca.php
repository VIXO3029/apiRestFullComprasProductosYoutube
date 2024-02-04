<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Marca extends Model
{
    use HasFactory;

    /**
     * Los atributos que pueden ser asignados en masa.
     *
     * @var array
     */
    protected $fillable = [
        'nombre', 'descripcion', 'origen',
    ];

    /**
     * Establece la relación uno a muchos con la tabla de productos.
     *
     * Una Marca puede tener muchos productos asociados.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function productos()
    {
        return $this->hasMany(Producto::class);
    }
}
