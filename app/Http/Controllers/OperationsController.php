<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OperationsController extends Controller
{
    public function addition(int $a, int $b): int
    {
        return $a + $b;
    }

    public function generate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'length' => ['sometimes', 'integer', 'min:12', 'max:128'],
        ]);

        $length = $validated['length'] ?? 20;

        $password = Str::password(
            length: $length,
            letters: true,
            numbers: true,
            symbols: true,
            spaces: false
        );

        return response()->json([
            'success' => true,
            'password' => $password,
            'length' => strlen($password),
        ]);
    }

}
