<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    /**
     * Los atributos que pueden ser asignados en masa.
     *
     * @var array
     */
    protected $fillable = [
        'nombre', 'descripcion', 'precio', 'cantidad_disponible', 'categoria_id', 'marca_id',
    ];

    /**
     * Establece la relación muchos a uno con la tabla de categorías.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    /**
     * Establece la relación muchos a uno con la tabla de marcas.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function marca()
    {
        return $this->belongsTo(Marca::class);
    }

    /**
     * Establece la relación muchos a muchos con la tabla de compras.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function compras()
    {
        return $this->belongsToMany(Compra::class)
            ->withPivot('precio', 'cantidad', 'subtotal')
            ->withTimestamps();
    }
}
