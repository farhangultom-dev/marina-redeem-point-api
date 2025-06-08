<?php

namespace App\Http\Controllers\Api;

use App\Models\Banner;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BannerController extends Controller
{
    public function getBanner()
    {
        $banners = Banner::all();

        return response()->json([
            'status' => 'true',
            'data' => $banners,
        ]);
    }

    public function addBanner(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image'     => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        //check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //upload image
        $image = $request->file('image');
        $image->storeAs('public/image_banners', $image->hashName());

        $banners = Banner::create([
            'image' => $image->hashName(),
        ]);

        if($banners){
            return response()->json([
            'status' => 'true',
            'messsage' => 'berhasil upload banner',
            'data' => $banners,
        ]);
        }
    }
}
