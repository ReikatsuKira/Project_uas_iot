<?php

namespace App\Http\Controllers;

use App\Models\SensorData;
use App\Models\UserLog;
use App\Models\GasSetting;
use Illuminate\Http\Request;

class SensorController extends Controller
{
    // SIMPAN DATA SENSOR (DARI ESP)
    public function store(Request $request)
    {
        $gas   = $request->gas ?? 0;
        $jarak = $request->jarak ?? 0;

        // Jangan simpan jarak tidak valid
        if ($jarak < 0) {
            $jarak = 0;
        }

        SensorData::create([
            'gas'        => $gas,
            'ketinggian' => $jarak
        ]);

        return response()->json(['status' => 'ok']);
    }

    // EVENT GAS DARURAT
    public function gasDarurat()
    {
        $users = UserLog::where('status', 'masuk')->get();

        foreach ($users as $user) {
            $user->update([
                'status'    => 'keluar',
                'keluar_at' => now()
            ]);
        }

        return response()->json([
            'status'       => 'gas_darurat',
            'user_keluar'  => $users->count()
        ]);
    }

    // DATA SENSOR TERBARU (REALTIME)
    public function latest()
    {
        $sensor = SensorData::latest()->first();

        if (!$sensor) {
            return response()->json(null);
        }

        $darurat = GasSetting::latest()->first()->gas_darurat ?? 600;

        // STATUS GAS
        if ($sensor->gas >= $darurat) {
            $status = 'GAS DARURAT';
        } else {
            // STATUS SAMPAH
            if ($sensor->ketinggian < 50) {
                $status = 'SAMPAH PENUH';
            } else {
                $status = 'SAMPAH AMAN';
            }
        }

        return response()->json([
            'gas'        => $sensor->gas,
            'ketinggian' => $sensor->ketinggian,
            'status'     => $status,
            'waktu'      => $sensor->created_at
                                ->timezone('Asia/Jakarta')
                                ->toDateTimeString()
        ]);
    }

    // HALAMAN MONITORING
    public function monitoring()
    {
        return view('monitoring');
    }
}