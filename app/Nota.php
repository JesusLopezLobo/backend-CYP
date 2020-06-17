<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Nota extends Model
{
    protected $table = 'notas';

    // Relacion de Muchos a Uno
    public function user(){
        return $this->belongsTo('App\user', 'user_id');
    }
}
