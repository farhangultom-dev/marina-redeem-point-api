<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\PasswordResetToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('user_id')) {
            $user = User::find($request->user_id);

            if (! $user) {
                return response()->json(['message' => 'User not found'], 404);
            }

            return new UserResource(true, 'User Found', $user);
        } else {
            $users = User::latest()->paginate(5);

            return new UserResource(true, 'List Data Users', $users);
        }
    }

    public function login(Request $request)
    {
        // define validation rules
        $validator = Validator::make($request->all(), [
            'username_email_or_phone' => 'required',
            'password' => 'required',
        ]);

        // check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::where('email', $request->username_email_or_phone)
            ->orWhere('username', $request->username_email_or_phone)
            ->orWhere('phone_number', $request->username_email_or_phone)
            ->whereNull('deleted_at')
            ->first();

        if ($user && Hash::check($request->password, $user->password)) {
            return new UserResource(true, 'Berhasil login', $user);
        }

        return new UserResource(false, 'Gagal login, username,email,atau phone_number tidak cocok', []);

    }

    public function forgotPassword(Request $request)
    {
        // define validation rules
        $validator = Validator::make($request->all(), [
            'username' => 'required',
        ]);

        // check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::where('username', $request->username)
            ->first();

        if ($user) {
            $token = random_int(100000, 999999);

            // create post
            $passwordToken = PasswordResetToken::create([
                'email' => $request->username,
                'token' => $token,
                'created_at' => Carbon::now(),
            ]);

            if ($passwordToken) {
                // Ensure your .env file contains the correct credentials
                $username_sms_gateway = env('USERNAME_SMS_GATEWAY');
                $password_sms_gateway = env('PASSWORD_SMS_GATEWAY');
                $user_phone = $user->phone_number;
                $message = 'Your token is: '.$token; // Replace $token with the actual token value

                // Construct the URL with all parameters
                $url = "https://secure.gosmsgateway.com/masking/api/send.php?username={$username_sms_gateway}&mobile={$user_phone}&message={$message}&password={$password_sms_gateway}";

                // Send the request
                $response = Http::get($url);

                // Check the response
                if ($response->successful()) {
                    // Success logic
                    $responseData = $response->json(); // If the response is JSON

                    return new UserResource(true, 'Token sent successfully', $responseData);
                } else {
                    // Error handling
                    return new UserResource(false, 'Failed to send Token', []);
                }
            }

            return new UserResource(false, 'Terjadi kesalahan', []);
        }

        return new UserResource(false, 'email tidak ditemukan', []);

    }

    public function checkOtp(Request $request)
    {
        // define validation rules
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'token' => 'required',
            'new_password' => 'required',

        ]);

        // check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $passwordToken = PasswordResetToken::where('email', $request->username)
            ->where('token', $request->token)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($passwordToken) {
            $selisihMenit = Carbon::parse($passwordToken->created_at)->diffInMinutes(now());

            if ($selisihMenit > 5) {
                return new UserResource(false, 'Token expired, silahkan kirim ulang otp', []);
            }

            // update password
            $user = User::where('username', $request->username)->update([
                'password' => Hash::make($request->new_password),
            ]);

            // return response
            if ($user) {
                return new UserResource(true, 'Password berhasil di ubah', []);
            }
        }

        return new UserResource(false, 'token tidak sesuai, silahkan coba kembali', []);

    }

    public function changePassword(Request $request)
    {
        // define validation rules
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required',
        ]);

        // check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // update password
        $user = User::where('email', $request->email)->update([
            'password' => Hash::make($request->password),
        ]);

        // return response
        if ($user) {
            return new UserResource(true, 'Password berhasil di ubah', []);
        }

        return new UserResource(true, 'User tidak ditemukan', $user);

    }

    public function store(Request $request)
    {
        // define validation rules
        $validator = Validator::make($request->all(), [
            'full_name' => 'required',
            'email' => 'required',
            'phone_number' => 'required',
            'username' => 'required',
            // 'image'     => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'point' => 'required',
            'password' => 'required',
            'is_admin' => 'required',
            'is_partner' => 'required',
        ]);

        // check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // //upload image
        // $image = $request->file('image');
        // $image->storeAs('public/photo_profile', $image->hashName());

        // create post
        $user = User::create([
            'full_name' => $request->full_name,
            'username' => $request->username,
            'phone_number' => $request->phone_number,
            'email' => $request->email,
            'password' => $request->password,
            'image' => '8847419.png', // $image->hashName(),
            'point' => $request->point,
            'is_admin' => $request->is_admin,
            'is_partner' => $request->is_partner,
        ]);

        // return response
        return new UserResource(true, 'Data User Berhasil Ditambahkan!', $user);
    }

    public function updateUser(Request $request)
    {
        // define validation rules
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'full_name' => 'required',
            'email' => 'required',
            'phone_number' => 'required',
            'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data_user = User::find($request->user_id);

        if (isset($request->image)) {
            $image = $request->file('image');
            $image->storeAs('public/photo_profile', $image->hashName());
            $data_user->image = $image->hashName();
        }

        $data_user->full_name = $request->full_name;
        $data_user->email = $request->email;
        $data_user->phone_number = $request->phone_number;

        if ($data_user->save()) {
            return new UserResource(true, 'Data User Berhasil Di Update!', $data_user);
        } else {
            return new UserResource(false, 'Data User Gagal Di Update!', []);
        }
    }

    public function deleteUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
        ]);

        // check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::find($user_id);
        $user->delete();

        if ($user) {
            return response()->json([
                'status' => 'true',
                'message' => 'berhasil delete user',
            ]);
        } else {
            return response()->json([
                'status' => 'false',
                'message' => 'gagal delete user',
            ]);
        }
    }
}
