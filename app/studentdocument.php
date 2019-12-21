<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class studentdocument extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'birthcertificate','otherdocument','user_id',
    ];
}
