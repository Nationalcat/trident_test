<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\TableController\StoreRequest;
use App\Models\Table;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TableController extends Controller
{
    /**
     * @param  Request  $request
     *
     * @return JsonResponse
     * @see app/docs/Staff/TableController/Index.yaml
     */
    public function index(Request $request): JsonResponse
    {
        $tables = Table::paginate(min($request->get('per_page', 10), 50));

        return response()->json([
            'data' => $tables->map(fn($table) => [
                'id' => $table->id,
                'seat' => $table->seat,
                'is_activated' => $table->is_activated,
            ]),
            'total' => $tables->total(),
        ]);
    }

    /**
     * @param  StoreRequest  $request
     *
     * @return JsonResponse
     * @see app/docs/Staff/TableController/Store.yaml
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $payload = $request->validated();
        Table::create([
            'seat' => $payload['seat'],
        ]);

        return response()->json('ok');
    }

    /**
     * @param  int  $id
     *
     * @return JsonResponse
     * @see app/docs/Staff/TableController/Disable.yaml
     */
    public function disable(int $id): JsonResponse
    {
        Table::findOrFail($id)->update([
            'is_activated' => false,
        ]);

        return response()->json('ok');
    }
}
