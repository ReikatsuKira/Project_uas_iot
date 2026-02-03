<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\UserLog;
use Illuminate\Http\Request;

class UserLogController extends Controller
{

    // scan RFID → masuk / keluar
    public function store(Request $request)
    {
        $rfid = $request->rfid;

        // cek RFID di tabel master
        $user = User::where('rfid', $rfid)->first();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'RFID tidak terdaftar'
            ], 404);
        }

        // cek log user (jika belum ada, buat baris awal tapi no insert user baru)
        $log = UserLog::firstOrCreate(
            ['user_id' => $user->id],
            ['masuk' => null, 'keluar' => null, 'status' => 'keluar']
        );

        // toggle status
        if ($log->status === 'masuk') {
            $log->update([
                'keluar' => now(),
                'status' => 'keluar'
            ]);
        } else {
            $log->update([
                'masuk' => now(),
                'keluar' => null,
                'status' => 'masuk'
            ]);
        }

        return response()->json([
            'status' => 'ok',
            'nama' => $user->nama,
            'state' => $log->status
        ]);
    }

    // kirim data user untuk tabel realtime
    public function data()
    {
        $logs = UserLog::with('user')->orderBy('masuk', 'desc')->get();
        return response()->json($logs);
    }

    // halaman view
    public function index()
    {
        $logs = UserLog::with('user')->orderBy('masuk', 'desc')->get();
        return view('user-log', compact('logs'));
    }

    // gas darurat → keluarkan semua user
    public function gasDarurat()
    {
        UserLog::where('status', 'masuk')->update([
            'status' => 'keluar',
            'keluar' => now()
        ]);

        return response()->json(['status' => 'DARURAT OK']);
    }
}


