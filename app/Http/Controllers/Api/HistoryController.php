<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HistoryController extends Controller
{
    public function getHistory(Request $request)
    {
        $baseUrl = url('storage/image_vouchers/');

        $datas = DB::table('history_redeems')
            ->join('vouchers', 'history_redeems.voucher_id', '=', 'vouchers.id')
            ->join('partners', 'vouchers.id_partner', '=', 'partners.user_id')
            ->where('history_redeems.user_id', $request->query('user_id'))
            ->select('history_redeems.user_id',
                'history_redeems.voucher_id',
                'vouchers.point_needed as point',
                DB::raw('DATE(history_redeems.created_at) as tanggal_penukaran'),
                DB::raw("CONCAT('$baseUrl/', image1) as image1"), 'partners.city')
            ->get();

        return response()->json([
            'status' => 'true',
            'data' => $datas,
        ]);
    }

    public function getHistoryPartnership(Request $request)
    {
        $baseUrl = url('storage/image_vouchers/');

        $datas = DB::table('history_redeems')
            ->join('vouchers', 'history_redeems.voucher_id', '=', 'vouchers.id')
            ->join('partners', 'vouchers.id_partner', '=', 'partners.user_id')
            ->join('users', 'history_redeems.user_id', '=', 'users.id')
            ->where('partners.user_id', $request->query('partner_id'))
            ->select('partners.id as partner_id',
                'history_redeems.voucher_id', 'users.full_name as customer_name', 'users.phone_number',
                'vouchers.point_needed as point',
                DB::raw('DATE(history_redeems.created_at) as tanggal_penukaran'),
                DB::raw("CONCAT('$baseUrl/', image1) as image1"), 'partners.city')
            ->get();

        return response()->json([
            'status' => 'true',
            'data' => $datas,
        ]);
    }

    public function getNotification()
    {
        $data = Voucher::select('nama_merchant', 'description', DB::raw('DATE(created_at) as created_date'))
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->where('is_approved', '1')
            ->get();

        return response()->json([
            'status' => 'true',
            'data' => $data,
        ]);
    }
}
