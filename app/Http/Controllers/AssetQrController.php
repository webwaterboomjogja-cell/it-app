<?php

namespace App\Http\Controllers;

use App\Models\Itassests;

class AssetQrController extends Controller
{
    public function show(string $token)
    {
        $asset = Itassests::with([
            'category',
            'location',
            'responsibleUser',
        ])
            ->where('qr_token', $token)
            ->firstOrFail();

        return view('assets.qr-show', compact('asset'));
    }

    public function label(string $token)
    {
        $asset = Itassests::with([
            'category',
            'location',
            'responsibleUser',
        ])
            ->where('qr_token', $token)
            ->firstOrFail();

        return view('assets.qr-label-print', compact('asset'));
    }
}
