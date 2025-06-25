<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Voucher;
use App\Models\Partner;
use App\Models\HistoryRedeem;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


class VoucherController extends Controller
{
    public function getVoucherByCategory(Request $request)
    {
        $id_category = $request->query('id');

        $partners = Partner::select('id')
        ->where('category_partner_id', $id_category)
        ->whereNull('deleted_at')
        ->get();

        $voucher = Voucher::whereIn('id_partner',$partners)
        ->where('is_approved','0')
        ->whereNull('deleted_at')
        ->paginate('10');

        return response()->json([
            'status' => 'true',
            'messsage' => 'berhasil mendapatkan data',
            'data' => $voucher,
        ]);
    }

    public function addVoucher(Request $request) 
    {
        $validator = Validator::make($request->all(), [
            'id_partner' => 'required',
            'merchant_name' => 'required',
            'description' => 'required',
            'limit_voucher' => 'required',
            'duration_start' => 'required',
            'duration_end' => 'required',
            'point' => 'required',
            'image1' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image2' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image3' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image4' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        //check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //upload image
        $image1 = $request->file('image1');
        $image1->storeAs('public/image_vouchers', $image1->hashName());

        $image2 = $request->file('image2');
        $image2->storeAs('public/image_vouchers', $image2->hashName());

        $image3 = $request->file('image3');
        $image3->storeAs('public/image_vouchers', $image3->hashName());

        $image4 = $request->file('image4');
        $image4->storeAs('public/image_vouchers', $image4->hashName());

        $add_voucher = Voucher::create([
            'id_partner' => $request->id_partner,
            'nama_merchant' => $request->merchant_name,
            'description' => $request->description,
            'total_voucher' => $request->limit_voucher,
            'start_date' => $request->duration_start,
            'end_date' => $request->duration_end,
            'point_needed' => $request->point,
            'is_approved' => '0',
            'image1' => $image1->hashName(),
            'image2' => $image2->hashName(),
            'image3' => $image3->hashName(),
            'image4' => $image4->hashName()
        ]);

        if($add_voucher){
            return response()->json([
                'status' => 'true',
                'messsage' => 'berhasil add voucher',
                'data' => $add_voucher,
            ]);
        }else{
            return response()->json([
                'status' => 'false',
                'messsage' => 'gagal add voucher'
            ]);
        }
    }

    public function getVoucherCode(Request $request)
    {
        $id = $request->query('id');

        $check_voucher = Voucher::where('id', $id)->get();

        if($check_voucher->isEmpty()){
            return response()->json([
                'status' => 'false',
                'messsage' => 'voucher tidak ditemukan'
            ]);
        }

        $code = random_int(100000, 999999);

        $voucher_code = "VOU-$id-$code";

        return response()->json([
            'status' => 'true',
            'messsage' => 'voucher ditemukan',
            'data' => $voucher_code
        ]);
    }

    public function scanVoucherCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_voucher' => 'required',
            'id_user' => 'required',
            'voucher_code' => 'required'
        ]);

        //check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //validating voucher
        $explode_voucher = explode('-',$request->voucher_code);
        if(isset($explode_voucher[1])){
            $check_voucher = Voucher::where('id', $explode_voucher[1])->get();

            if($check_voucher->isEmpty()){
                return response()->json([
                    'status' => 'false',
                    'messsage' => 'voucher tidak ditemukan'
                ]);
            }
        }else{
            return response()->json([
                'status' => 'false',
                'messsage' => 'voucher tidak valid'
            ]);
        }


        //check voucher exist
        $total_voucher_existing = Voucher::select('total_voucher')
        ->where('id', $request->id_voucher)
        ->first();

        $total_voucher_redeemed = HistoryRedeem::where('voucher_id', $request->id_voucher)
        ->count();

        if($total_voucher_existing->total_voucher <= $total_voucher_redeemed){
            return response()->json([
                'status' => 'false',
                'messsage' => 'voucher sudah habis, silahkan pilih voucher yang lain ya!'
            ]);
        }

        $add_redeem = HistoryRedeem::create([
            'user_id' => $request->id_user,
            'voucher_id' => $request->id_voucher,
            'is_redeemed' => '1',
        ]);

        if($add_redeem){
            return response()->json([
                'status' => 'true',
                'messsage' => 'berhasil scan voucher'
            ]);
        }else{
            return response()->json([
                'status' => 'false',
                'messsage' => 'gagal scan code voucher'
            ]);
        }

    }
}
