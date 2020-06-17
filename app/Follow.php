<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Follow extends Model
{
    protected $table = 'follows';

    // Relacion de Muchos a Uno
    public function user(){
        return $this->belongsTo('App\user', 'user_id');
    }
}
