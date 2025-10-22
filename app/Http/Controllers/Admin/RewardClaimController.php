<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RewardClaim;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class RewardClaimController extends Controller
{
    public function index()
    {
        // Buscamos los reclamos con estado 'solicitado' y cargamos la información relacionada
        $claims = RewardClaim::where('status', 'solicitado')
            ->with(['user', 'reward']) // Carga el usuario y la recompensa
            ->latest()
            ->get();

        return view('admin.claims.index', ['claims' => $claims]);
    }

    public function fulfill(Request $request, RewardClaim $claim)
    {
        $validatedData = $request->validate([
            'tracking_number' => 'nullable|string|max:255',
            'tracking_code' => 'nullable|string|max:255',
            'voucher_photo' => 'nullable|image|max:2048',
            'agency_address' => 'nullable|string|max:255',
            'pickup_password' => 'nullable|string|max:255',
        ]);

        $voucherPath = $claim->voucher_path;
        if ($request->hasFile('voucher_photo')) {
            // Delete old voucher if exists and new one is uploaded
            if ($claim->voucher_path) {
                Storage::disk('public')->delete($claim->voucher_path);
            }
            $voucherPath = $request->file('voucher_photo')->store('vouchers', 'public');
        }

        // --- START OF CORRECTION ---
        // Use ?? null to safely access nullable fields from validated data
        $claim->update([
            'tracking_number' => $validatedData['tracking_number'] ?? null,
            'tracking_code' => $validatedData['tracking_code'] ?? null,
            'voucher_path' => $voucherPath,
            'agency_address' => $validatedData['agency_address'] ?? null,
            'pickup_password' => $validatedData['pickup_password'] ?? null,
            'status' => 'enviado',
        ]);
        // --- END OF CORRECTION ---

        return redirect()->route('admin.claims.index')->with('success', 'Comprobante enviado correctamente.');
    }
}
