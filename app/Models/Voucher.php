<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;


class Voucher extends Model
{
    use HasFactory;
    use SoftDeletes;

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
        'decline_reason',
        'image1',
        'image2',
        'image3',
        'image4',
    ];

    protected function image1(): Attribute
    {
        return Attribute::make(
            get: fn ($image1) => url('/storage/image_vouchers/' . $image1),
        );
    }

    protected function image2(): Attribute
    {
        return Attribute::make(
            get: fn ($image2) => url('/storage/image_vouchers/' . $image2),
        );
    }

    protected function image3(): Attribute
    {
        return Attribute::make(
            get: fn ($image3) => url('/storage/image_vouchers/' . $image3),
        );
    }

    protected function image4(): Attribute
    {
        return Attribute::make(
            get: fn ($image4) => url('/storage/image_vouchers/' . $image4),
        );
    }
}
