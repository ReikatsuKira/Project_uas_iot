<?php

namespace App\Http\Controllers;

use App\Models\GasSetting;
use Illuminate\Http\Request;
use App\Models\SensorData;
use Illuminate\Support\Facades\DB;

class GasControlController extends Controller
{
    // GET /gas-control
    public function index()
    {
        // ambil setting (anggap cuma 1 baris)
        $setting = DB::table('gas_settings')->first();

        // fallback kalau tabel masih kosong
        $gasNormal  = $setting->gas_normal  ?? 500;
        $gasDarurat = $setting->gas_darurat ?? 600;

        // contoh nilai gas terakhir (nanti dari tabel sensor)
        $gasValue = SensorData::latest()->first()->gas ?? 0;

        // hitung status (LOGIKA DI CONTROLLER)
        $status = $gasValue >= $gasDarurat ? 'DARURAT' : 'NORMAL';

        return view('gas-control', [
            'gasValue'   => $gasValue,
            'status'     => $status,
            'gasNormal'  => $gasNormal,
            'gasDarurat' => $gasDarurat,
        ]);
    }

    // POST /gas-control
    public function update(Request $request)
    {
        $request->validate([
            'gas_normal'  => 'required|integer',
            'gas_darurat' => 'required|integer|gt:gas_normal',
        ]);

        $setting = DB::table('gas_settings')->first();

        if ($setting) {
            DB::table('gas_settings')
                ->where('id', $setting->id)
                ->update([
                    'gas_normal'  => $request->gas_normal,
                    'gas_darurat' => $request->gas_darurat,
                    'updated_at'  => now(),
                ]);
        } else {
            DB::table('gas_settings')->insert([
                'gas_normal'  => $request->gas_normal,
                'gas_darurat' => $request->gas_darurat,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        return redirect('/')->with('success', 'Pengaturan gas diperbarui');
    }

    public function gasConfig()
    {
        $setting = GasSetting::latest()->first();

        return response()->json([
            'gas_normal'  => $setting->gas_normal ?? 500,
            'gas_darurat' => $setting->gas_darurat ?? 600
        ]);
    }
}