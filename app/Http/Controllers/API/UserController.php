<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Rules\Password;

class UserController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'email|required',
                'password' => 'required'
            ]);

            // kita masukan pengenalan dari request melalui inputan kedalam variable credentials
            $credentials = request(['email', 'password']);

            // kita lakukan pengecheckan apakah data yang di input benar atau valid
            if (!Auth::attempt($credentials)) {
                return ResponseFormatter::error([
                    'message' => 'Unauthorized'
                ], 'Authentication Failed', 500);
            }
            // jika inputan valid maka akan di cek apaka inputan ada di dalam database 
            $user = User::where('email', $request->email)->first();
            // jika encrypt hash tidak sesuai makan akan di lempar respon error 
            if (!Hash::check($request->password, $user->password, [])) {
                throw new \Exception('Invalid Credentials');
            }

            // jika berhasil maka akan di kembalikan berupa token yang berapa plain text
            $tokenResult = $user->createToken('authToken')->plainTextToken;
            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user
            ], 'Authenticated');
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $error,
            ], 'Authentication Failed', 500);
        }
    }
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function register(Request $request)
    {
        // sebelum create user kita lakukan valdasi
        try {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'username' => ['required', 'string', 'max:255', 'unique:users'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'phone' => ['nullable', 'string', 'max:255', 'min:10'],
                'password' => ['required', 'min:6', 'string', new Password]
            ]);

            // setelah validasi kita masukan input request ke database
            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'username' => $request->username,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
            ]);

            // setelah input database selesai kita ambil emailnya atau juga bisa uniq key nya
            $user = User::where('email', $request->email)->first();

            // kita berikan token dengan method ->createToke bawaan jetstream laravel
            $tokenResult = $user->createToken('authToken')->plainTextToken;

            // jika berhasil kita kembalikan data nya dengan format json yang di olah melalui ResponseFormatter
            return ResponseFormatter::success(
                [
                    'access_token' => $tokenResult,
                    'token_type' => 'Bearer',
                    'user' => $user
                ],
                // pesan result yang akan di tampilkan 
                'User Registered'
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something went wrong',
                    'error' => $error,
                ],
                // pesan result yang akan di tampilkan dan error code nya 500
                'Authentication Failed',
                500
            );
        }
    }

    public function fetch(Request $request)
    {
        return ResponseFormatter::success($request->user(), "Data berhasil diambil");
    }

    public function updateProfile(Request $request)
    {
        $data = $request->all();
        $user = Auth::user();
        $user->update($data);

        return ResponseFormatter::success($user, "Data berhasil di update");
    }

    public function logout(Request $request)
    {

        $token = $request->user()->currentAccessToken()->delete();
        return ResponseFormatter::success($token, "Token Revoked");
    }
}
