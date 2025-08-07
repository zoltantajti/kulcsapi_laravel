<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\License;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Throwable;

class LicenseController extends Controller
{
    public function validateKey(Request $request): JsonResponse
    {
        $license = License::where('license_key', $request->input('key'))->first();
        if(!$license){
            return response()->json(['valid' => false, 'message' => 'Érvénytelen kulcs'], 404);
        };
        if($license->status === 'revoked'){
            return response()->json(['valid' => false, 'message' => 'A kulcs visszavonásra került'], 403);
        };
        if($license->status === 'expired' || ($license->expires_at && now()->gt($license->expires_at))){
            return response()->json(['valid' => false, 'message' => 'A kulcs lejárt'], 403);
        };

        return response()->json([
            'valid' => true,
            'message' => 'A kulcs érvényes.',
            'status' => $license->status,
            'expires_at' => $license->expires_at
        ]);
    }

    public function useKey(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required|string',
            'email' => 'required|email',
            'machineID' => 'required|string'
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false, 
                'message' => 'Hiányos, vagy érvénytelen adatok', 
                'errors' => $validator->errors()
            ], 400);
        };

        try{
            $result = DB::transaction(function () use ($request) {
                $license = License::where('license_key', $request->input('key'))->lockForUpdate()->first();
                if(!$license){
                    return ['success' => false, 'message' => 'Érvénytelen kulcs'];
                };
                if(!in_array($license->status, ['available','in_use'])){
                    return ['success' => false, 'message' => 'A kulcs nem aktiválható!'];
                };

                $existingActivation = $license->activations()->where('machine_id', $request->input('machineID'))->first();
                if($existingActivation){
                    return ['success' => true, 'message' => 'A kulcs ezen a gépen aktív!'];
                };
                if($license->activations()->count() >= $license->max_activations){
                    return ['success' => true, 'message' => 'A kulcs elérte a maximálius aktivációk számát.'];
                };

                $license->activations()->create([
                    'email' => $request->input('email'),
                    'machine_id' => $request->input('machineID')
                ]);

                if($license->status === 'available'){
                    $license->status = 'in_use';
                    $license->save();
                };

                return ['success' => true, 'message' => 'A kulcs sikeresen aktiválva'];
            });

            $statusCode = $result['success'] ? 200 : 409;
            return response()->json($result, $statusCode);
        }catch(Throwable $e){
            return response()->json(['success' => false, 'message' => 'Szerverhiba történt!'], 500);
        };
    }
}
