<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\PhoneController\BlockPhoneRequest;
use App\Models\Phone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PhoneController extends Controller
{
    /**
     * @param  Request  $request
     *
     * @return JsonResponse
     * @see app/docs/Staff/PhoneController/Index.yaml
     */
    public function index(Request $request): JsonResponse
    {
        $phones = Phone
            ::when($request->phones, static fn($query) => $query
                ->whereIn('phone', $request->phones)
            )
            ->when($request->is_blacklisted, static fn($query) => $query
                ->where('is_blacklisted', $request->is_blacklisted)
            )
            ->paginate(min($request->get('per_page'), 50));

        return response()->json([
            'data' => $phones->map(fn($phone) => [
                'id' => $phone->id,
                'phone' => $phone->phone,
                'is_blacklisted' => $phone->is_blacklisted,
            ]),
            'total' => $phones->total(),
        ]);
    }

    /**
     * @param  BlockPhoneRequest  $request
     *
     * @return JsonResponse
     * @see app/docs/Staff/PhoneController/BlockPhones.yaml
     */
    public function blockPhones(BlockPhoneRequest $request): JsonResponse
    {
        $phone = Phone::whereIn('id', $request->validated('ids'))->firstOrFail();
        $phone->update([
            'is_blacklisted' => true,
        ]);

        return response()->json('ok');
    }

    /**
     * @param  BlockPhoneRequest  $request
     *
     * @return JsonResponse
     * @see app/docs/Staff/PhoneController/UnblockPhones.yaml
     */
    public function unblockPhones(BlockPhoneRequest $request): JsonResponse
    {
        $phone = Phone::whereIn('id', $request->validated('ids'))->firstOrFail();
        $phone->update([
            'is_blacklisted' => false,
        ]);

        return response()->json('ok');
    }
}
