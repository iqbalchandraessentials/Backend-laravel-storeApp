<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Toko;
use App\Helpers\ResponseFormatter;
use Illuminate\Http\Request;

class TokoController extends Controller
{
    public function register(Request $request)
    {
        try {
            Toko::create([
                'name' => $request->name,
                'alamat' => $request->alamat,
                'owner' => $request->owner,
            ]);

            return ResponseFormatter::success(
                [
                    'name' => $request->name,
                    'alamat' => $request->alamat,
                    'owner' => $request->owner,
                ],
                // pesan result yang akan di tampilkan 
                'Toko Registered'
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

    public function al()
    {
        $data =  Toko::query();;
    }
}
