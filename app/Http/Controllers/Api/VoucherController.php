<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Voucher;
use App\Models\Partner;
use App\Models\HistoryRedeem;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class VoucherController extends Controller
{
    public function getVoucherByCategory(Request $request)
    {
        $id_category = $request->query('id');
        $status = $request->query('is_approved');

        $partners = Partner::select('user_id')
        ->where('category_partner_id', $id_category)
        ->whereNull('deleted_at')
        ->get();

        if($status == '3'){
            $voucher = Voucher::whereIn('id_partner',$partners)
            ->where('end_date', '<', Carbon::today())
            ->where('is_approved','1')
            ->whereNull('deleted_at')
            ->paginate('10');

            return response()->json([
            'status' => 'true',
            'message' => 'berhasil mendapatkan data',
            'data' => $voucher,
            ]);
        }

        $voucher = Voucher::whereIn('id_partner',$partners)
        ->where('end_date', '>', Carbon::today())
        ->where('is_approved',$status)
        ->whereNull('deleted_at')
        ->paginate('10');

        return response()->json([
            'status' => 'true',
            'message' => 'berhasil mendapatkan data',
            'data' => $voucher,
        ]);
    }

    public function getVoucherByPartner(Request $request)
    {
        $id_partner = $request->query('id_partner');
        $status = $request->query('is_approved');

        if($status == '3'){
            $voucher = Voucher::where('id_partner',$id_partner)
            ->where('end_date', '<', Carbon::today())
            ->where('is_approved','1')
            ->whereNull('deleted_at')
            ->paginate('10');

            return response()->json([
            'status' => 'true',
            'message' => 'berhasil mendapatkan data',
            'data' => $voucher,
            ]);
        }

        $voucher = Voucher::where('id_partner',$id_partner)
        ->where('end_date', '>', Carbon::today())
        ->where('is_approved',$status)
        ->whereNull('deleted_at')
        ->paginate('10');

        return response()->json([
            'status' => 'true',
            'message' => 'berhasil mendapatkan data',
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
                'message' => 'berhasil add voucher',
                'data' => $add_voucher,
            ]);
        }else{
            return response()->json([
                'status' => 'false',
                'message' => 'gagal add voucher'
            ]);
        }
    }

    public function getVoucherCode(Request $request)
    {
        $id = $request->query('id');
        $point = $request->query('user_point');

        $check_voucher = Voucher::where('id', $id)->get();

        if($check_voucher->isEmpty()){
            return response()->json([
                'status' => 'false',
                'message' => 'voucher tidak ditemukan'
            ]);
        }

        $user_point = intval($point);

        if($user_point < $check_voucher[0]->point_needed){
            return response()->json([
                'status' => 'false',
                'message' => 'point anda tidak mencukupi'
            ]);
        }

        //check voucher exist
        $total_voucher_existing = Voucher::select('total_voucher')
        ->where('id', $id)
        ->where('is_approved', '1')
        ->first();

        $total_voucher_redeemed = HistoryRedeem::where('voucher_id', $id)
        ->count();

        if($total_voucher_existing != null){
            if($total_voucher_existing->total_voucher <= $total_voucher_redeemed){
                return response()->json([
                    'status' => 'false',
                    'message' => 'voucher sudah habis, silahkan pilih voucher yang lain ya!'
                ]);
            }
        }else{
             return response()->json([
                'status' => 'false',
                'message' => 'voucher tidak ditemukan'
            ]);
        }

        $code = random_int(100000, 999999);

        $voucher_code = "VOU-$id-$code";

        return response()->json([
            'status' => 'true',
            'message' => 'voucher ditemukan',
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

        if(isset($explode_voucher[0])){
            if($explode_voucher[0] != 'VOU'){
                    return response()->json([
                    'status' => 'false',
                    'message' => 'voucher tidak valid'
                ]);
            }
        }else{
             return response()->json([
                'status' => 'false',
                'message' => 'voucher tidak valid'
            ]);
        }

        if(isset($explode_voucher[1])){
            $check_voucher = Voucher::where('id', $explode_voucher[1])->get();

            if($check_voucher->isEmpty()){
                return response()->json([
                    'status' => 'false',
                    'message' => 'voucher tidak ditemukan'
                ]);
            }
        }else{
            return response()->json([
                'status' => 'false',
                'message' => 'voucher tidak valid'
            ]);
        }

        if(isset($explode_voucher[2])){
            $length = Str::length($explode_voucher[2]);
                if($length != 6){
                    return response()->json([
                    'status' => 'false',
                    'message' => 'voucher tidak valid'
                ]);
            }
        }else{
            return response()->json([
                'status' => 'false',
                'message' => 'voucher tidak valid'
            ]);
        }


        //check voucher exist
        $total_voucher_existing = Voucher::select('total_voucher')
        ->where('id', $request->id_voucher)
        ->where('is_approved', '1')
        ->first();

        $total_voucher_redeemed = HistoryRedeem::where('voucher_id', $request->id_voucher)
        ->count();

        if($total_voucher_existing != null){
            if($total_voucher_existing->total_voucher <= $total_voucher_redeemed){
                return response()->json([
                    'status' => 'false',
                    'message' => 'voucher sudah habis, silahkan pilih voucher yang lain ya!'
                ]);
            }
        }else{
             return response()->json([
                'status' => 'false',
                'message' => 'voucher tidak ditemukan'
            ]);
        }

        $voucher_is_used = HistoryRedeem::where('voucher_id', $request->id_voucher)
        ->where('code_voucher', $request->voucher_code)
        ->count();

        if($voucher_is_used > 0){
             return response()->json([
                'status' => 'false',
                'message' => 'code voucher ini sudah digunakan'
            ]);
        }
        

        $add_redeem = HistoryRedeem::create([
            'user_id' => $request->id_user,
            'voucher_id' => $request->id_voucher,
            'code_voucher' => $request->voucher_code,
            'is_redeemed' => '1',
        ]);

        if($add_redeem){
            return response()->json([
                'status' => 'true',
                'message' => 'berhasil scan voucher'
            ]);
        }else{
            return response()->json([
                'status' => 'false',
                'message' => 'gagal scan code voucher'
            ]);
        }

    }

    public function approvalVoucher(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_voucher' => 'required',
            'status' => 'required'
        ]);

        //check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if($request->decline_reason != null){
            $update_voucher = Voucher::where('id', $request->id_voucher)
            ->update([
                'is_approved' => $request->status,
                'decline_reason' => $request->decline_reason
            ]);

            if($update_voucher){
                return response()->json([
                    'status' => 'true',
                    'message' => 'berhasil approval voucher'
                ]);
            }else{
                return response()->json([
                    'status' => 'false',
                    'message' => 'gagal approval voucher'
                ]);
            }
        }

        $update_voucher = Voucher::where('id', $request->id_voucher)
        ->update([
            'is_approved' => $request->status,
            'decline_reason' => ''

        ]);

        if($update_voucher){
             return response()->json([
                'status' => 'true',
                'message' => 'berhasil approval voucher'
            ]);
        }else{
             return response()->json([
                'status' => 'false',
                'message' => 'gagal approval voucher'
            ]);
        }

    }

    public function deleteVoucher(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_voucher' => 'required'
        ]);

        //check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = Voucher::find($request->id_voucher);
        $data->delete();

        if($data){
            return response()->json([
                'status' => 'true',
                'message' => 'berhasil delete voucher'
            ]);
        }else{
             return response()->json([
                'status' => 'false',
                'message' => 'gagal delete voucher'
            ]);
        }
    }

    public function updateVoucher(Request $request) 
    {
        $validator = Validator::make($request->all(), [
            'id_voucher' => 'required',
            'id_partner' => 'required',
            'merchant_name' => 'required',
            'description' => 'required',
            'limit_voucher' => 'required',
            'duration_start' => 'required',
            'duration_end' => 'required',
            'point' => 'required',
            'image1' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'image2' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'image3' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'image4' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        //check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $voucher = Voucher::find($request->id_voucher);

        //upload image
        if(isset($request->image1)){
            $image1 = $request->file('image1');
            $image1->storeAs('public/image_vouchers', $image1->hashName());
            $voucher->image1 = $image1->hashName();
        }

        if(isset($request->image2)){
            $image2 = $request->file('image2');
            $image2->storeAs('public/image_vouchers', $image2->hashName());
            $voucher->image2 = $image2->hashName();
        }

        if(isset($request->image3)){
            $image3 = $request->file('image3');
            $image3->storeAs('public/image_vouchers', $image3->hashName());
            $voucher->image3 = $image3->hashName();

        }

        if(isset($request->image4)){
            $image4 = $request->file('image4');
            $image4->storeAs('public/image_vouchers', $image4->hashName());
            $voucher->image4 = $image4->hashName();
        }

        $voucher->id_partner = $request->id_partner;
        $voucher->nama_merchant = $request->merchant_name;
        $voucher->description = $request->description;
        $voucher->total_voucher = $request->limit_voucher;
        $voucher->start_date = $request->duration_start;
        $voucher->end_date = $request->duration_end;
        $voucher->point_needed = $request->point;

        if($voucher->save()){
            return response()->json([
                'status' => 'true',
                'message' => 'berhasil update voucher',
                'data' => $voucher,
            ]);
        }else{
            return response()->json([
                'status' => 'false',
                'message' => 'gagal update voucher'
            ]);
        }
    }
}
