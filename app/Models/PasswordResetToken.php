<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordResetToken extends Model
{
    use HasFactory;
    public $timestamps = false;


    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = [
        'email',
        'token',
        'created_at',
    ];
}
