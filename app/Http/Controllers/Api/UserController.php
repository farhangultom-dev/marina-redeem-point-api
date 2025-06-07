<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\PasswordResetToken;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()->paginate(5);
        return new UserResource(true, 'List Data Users', $users);
    }

    public function login(Request $request)
    {
        //define validation rules
        $validator = Validator::make($request->all(), [
            'username_email_or_phone'   => 'required',
            'password'   => 'required'
        ]);

         //check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::where('email', $request->username_email_or_phone)
                ->orWhere('username', $request->username_email_or_phone)
                ->orWhere('phone_number', $request->username_email_or_phone)
                ->first();

        if ($user && Hash::check($request->password, $user->password)) {
            return new UserResource(true, 'Berhasil login', $user);
        }

        return new UserResource(false, 'Gagal login, username,email,atau phone_number tidak cocok', array());

    }

    public function forgotPassword(Request $request)
    {
        //define validation rules
        $validator = Validator::make($request->all(), [
            'email'   => 'required'
        ]);

        //check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::where('email', $request->email)
                ->first();

        if ($user) {
            $token = random_int(100000, 999999);

            //create post
            $passwordToken = PasswordResetToken::create([
                'email'     => $request->email,
                'token'   => $token,
                'created_at' => Carbon::now()
            ]);

            if($passwordToken)
            {
                // Send token by email
                Mail::raw("Your verification code is: {$token}", function ($mail) use ($user) {
                    $mail->to($user->email)
                        ->subject('Your Verification Code');
                });
            }

            return new UserResource(true, 'Silahkan cek email anda untuk mendapatkan kode otp', array());
        }

        return new UserResource(false, 'email tidak ditemukan', array());

    }

    public function checkOtp(Request $request)
    {
        //define validation rules
        $validator = Validator::make($request->all(), [
            'email'   => 'required',
            'token'   => 'required',
        ]);

        //check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $passwordToken = PasswordResetToken::where('email', $request->email)
                ->where('token', $request->token)
                ->orderBy('created_at','desc')
                ->first();

        if($passwordToken)
        {
            $selisihMenit = Carbon::parse($passwordToken->created_at)->diffInMinutes(now());

            if($selisihMenit > 5)
            {
                return new UserResource(false, 'Token expired, silahkan kirim ulang otp', array());
            }

            return new UserResource(true, 'Token sesuai, silahkan ubah password anda', array());
        }

        return new UserResource(false, 'token tidak sesuai, silahkan coba kembali', array());

    }

    public function store(Request $request)
    {
        //define validation rules
        $validator = Validator::make($request->all(), [
            'full_name'     => 'required',
            'email'   => 'required',
            'phone_number'   => 'required',
            'username'   => 'required',
            'image'     => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'point'   => 'required',
            'password' => 'required',
            'is_admin'   => 'required',
            'is_partner' => 'required',
        ]);

        //check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //upload image
        $image = $request->file('image');
        $image->storeAs('public/photo_profile', $image->hashName());

        //create post
        $user = User::create([
            'full_name'     => $request->full_name,
            'username'   => $request->username,
            'phone_number'     => $request->phone_number,
            'email'   => $request->email,
            'password' => $request->password,
            'image'     => $image->hashName(),
            'point'   => $request->point,
            'is_admin' => $request->is_admin,
            'is_partner' => $request->is_partner,
        ]);

        //return response
        return new UserResource(true, 'Data User Berhasil Ditambahkan!', $user);
    }
}
