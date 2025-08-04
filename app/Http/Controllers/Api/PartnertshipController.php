<?php

namespace App\Http\Controllers\Api;

use App\Models\Partner;
use App\Models\CategoryPartner;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

use Illuminate\Http\Request;

class PartnertshipController extends Controller
{
    public function addPartnertship(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'id_category_partner' => 'required',
            'nama_partner' => 'required',
            'city' => 'required',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'password' => 'required',
            'phone_number' => 'required',
            'email' => 'required'

        ]);

        //check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //set akun
        //create post
        $user = User::create([
            'full_name'     => $request->nama_partner,
            'username'   => $request->username,
            'phone_number'     => $request->phone_number,
            'email'   => $request->email,
            'password' => $request->password,
            'image'     => 'https://cdn-icons-png.flaticon.com/512/8847/8847419.png', //$image->hashName(),
            'point'   => '0',
            'is_admin' => '0',
            'is_partner' => '1',
        ]);

        //upload image
        $image = $request->file('image');
        $image->storeAs('public/image_partners', $image->hashName());

        $article = Partner::create([
            'user_id' => $user->id,
            'category_partner_id' => $request->id_category_partner,
            'nama_partner' => $request->nama_partner,
            'city' => $request->city,
            'image' => $image->hashName(),
        ]);

        if($article)
        {
            return response()->json([
                'status' => 'true',
                'message' => 'berhasil upload partner',
                'data' => $article,
            ]);
        }

        return response()->json([
            'status' => 'false',
            'message' => 'gagal upload partner, silahkan coba lagi'
        ]);
    }

    public function getPartners(Request $request) {
        $id = $request->query('id');
        $partners = Partner::latest()->paginate(10);

        return response()->json([
            'status' => 'true',
            'data' => $partners,
        ]);
    }

    public function getPartnersByCategory(Request $request) {
        $id = $request->query('category_id');
        $partners = Partner::where('category_partner_id',$id)
        ->whereNull('deleted_at')
        ->paginate(10);

        return response()->json([
            'status' => 'true',
            'data' => $partners,
        ]);
    }

    public function deletePartner(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_partner' => 'required'
        ]);

        //check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = Partner::find($request->id_partner);
        $user_id = $data->user_id;
        $data->delete();

        if($data){
            $user = User::find($user_id);
            $user->delete();

            if($user){
                return response()->json([
                'status' => 'true',
                'message' => 'berhasil delete partner'
            ]);

            }else{
                return response()->json([
                'status' => 'false',
                'message' => 'gagal delete partner'
            ]);
            }
            
        }else{
             return response()->json([
                'status' => 'false',
                'message' => 'gagal delete partner'
            ]);
        }
    }

    public function getCategoryPartners(Request $request) {
        $partners = CategoryPartner::orderBy('nama_category','asc')->get();

        return response()->json([
            'status' => 'true',
            'data' => $partners,
        ]);
    }

    public function updatePartnertship(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'id_category_partner' => 'required',
            'nama_partner' => 'required',
            'city' => 'required'
        ]);

        //check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }


        $article = Partner::where('id',$request->id)->update([
            'category_partner_id' => $request->id_category_partner,
            'nama_partner' => $request->nama_partner,
            'city' => $request->city,
        ]);

        $partner = Partner::where('id',$request->id)->first();

        //update akun
        $user = User::where('id',$partner->user_id)->update([
            'full_name'     => $request->nama_partner,
            'phone_number'     => '-',
            'email'   => '-',
            'image'     => 'https://cdn-icons-png.flaticon.com/512/8847/8847419.png', //$image->hashName(),
            'point'   => '0',
            'is_admin' => '0',
            'is_partner' => '1',
        ]);

        if($user)
        {
            return response()->json([
                'status' => 'true',
                'message' => 'berhasil update partner'
            ]);
        }

        return response()->json([
            'status' => 'false',
            'message' => 'gagal upload partner, silahkan coba lagi'
        ]);
    }

    public function updatePhotoPartnertship(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        //check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

         //upload image
        $image = $request->file('image');
        $image->storeAs('public/image_partners', $image->hashName());


        $updatePartner = Partner::where('id',$request->id)->update([
            'image' => $image->hashName()
        ]);

        if($updatePartner)
        {
            return response()->json([
                'status' => 'true',
                'message' => 'berhasil update foto partner'
            ]);
        }

        return response()->json([
            'status' => 'false',
            'message' => 'gagal upload foto partner, silahkan coba lagi'
        ]);
    }
}
