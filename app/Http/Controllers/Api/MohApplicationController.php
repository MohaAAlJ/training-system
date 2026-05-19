<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\SyncMohApplicationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\MohApplicationRequest;

class MohApplicationController extends Controller
{
    public function __invoke(MohApplicationRequest $request, SyncMohApplicationAction $action)
    {
        $result = $action->execute($request->validated(), $request->user());

        if (! $result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], $result['status_code'] ?? 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم استلام واعتماد طلب التدريب بنجاح.',
            'data' => $result['data']
        ], 200);
    }
}
