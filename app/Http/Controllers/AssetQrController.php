<?php

namespace App\Http\Controllers;

use App\Models\Itassests;
use Illuminate\Support\Str;

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

    public function printAll()
    {
        Itassests::query()
            ->whereNull('qr_token')
            ->get()
            ->each(function ($asset) {
                $asset->update([
                    'qr_token' => (string) Str::uuid(),
                ]);
            });

        $assets = Itassests::with([
            'category',
            'location',
            'responsibleUser',
        ])
            ->whereNotNull('qr_token')
            ->orderBy('code')
            ->get();

        return view('assets.qr-label-print-all', compact('assets'));
    }
}
