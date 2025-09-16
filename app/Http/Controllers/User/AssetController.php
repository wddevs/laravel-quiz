<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Asset;
use App\Services\AssetService;

class AssetController extends Controller
{
    public function store(Request $request, AssetService $assetService)
    {
        $request->validate([
            'files'   => ['required'],
            'files.*' => ['file','max:20480'], // 20MB
        ]);

        $result = $assetService->upload('files');

        // Повертаємо JSON для XHR-аплоадів у Vue
        if ($request->wantsJson()) {
            $data = is_array($result) ? array_map(fn($a) => $a->toArray(), $result instanceof \Traversable ? iterator_to_array($result) : $result) : $result->toArray();
            return response()->json(['data' => $data]);
        }

        return back()->with('success', 'Asset(s) uploaded successfully.');
    }

    public function destroy(Asset $asset)
    {
        $this->authorize('delete', $asset); // опційно
        $asset->delete(); // файл видалиться автоматично через booted()
        return back()->with('success', 'Asset deleted.');
    }
}
