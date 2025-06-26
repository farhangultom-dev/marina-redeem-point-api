<?php

namespace App\Http\Controllers\Api;

use App\Models\CategoryPartner;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CategoryPartnerController extends Controller
{
    public function getCategory()
    {
        $datas = CategoryPartner::select('id','nama_category')->get();

        return response()->json([
            'status' => 'true',
            'data' => $datas,
        ]);
    }
}
