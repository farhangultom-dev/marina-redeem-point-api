<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = [
        'id_partner',
        'nama_merchant',
        'description',
        'total_voucher',
        'start_date',
        'end_date',
        'point_needed',
        'is_approved',
    ];
}
