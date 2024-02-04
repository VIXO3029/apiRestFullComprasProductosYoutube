<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    use HasFactory;

    /**
     * Los atributos que pueden ser asignados en masa.
     *
     * @var array
     */
    protected $fillable = [
        'subtotal', 'total',
    ];

    /**
     * Define la relación muchos a muchos con Producto a través de la tabla pivot.
     *
     * Una Compra puede tener varios Productos y viceversa.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function productos()
    {
        return $this->belongsToMany(Producto::class)
            ->withPivot(['precio', 'cantidad', 'subtotal'])
            ->withTimestamps();
    }
}
