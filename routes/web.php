<?php

use App\Http\Controllers\GasControlController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SensorController;
use App\Http\Controllers\UserLogController;
use App\Http\Controllers\DetailController;
use Illuminate\Support\Facades\DB;

// Route utama - Welcome Page dengan monitoring
Route::get('/', function() {
    $setting = DB::table('gas_settings')->first();
    $gasNormal  = $setting->gas_normal ?? 500;
    $gasDarurat = $setting->gas_darurat ?? 600;
    
    return view('welcome', compact('gasNormal', 'gasDarurat'));
});

// Sensor Routes
Route::post('/sensor', [SensorController::class, 'store']);
Route::post('/gas-darurat', [SensorController::class, 'gasDarurat']);
Route::get('/sensor/latest', [SensorController::class, 'latest']);
Route::get('/monitoring', [SensorController::class, 'monitoring']);

// RFID / User Log Routes
Route::post('/rfid', [UserLogController::class, 'store']);
Route::get('/user-log/data', [UserLogController::class, 'data']);
Route::get('/user-log', [UserLogController::class, 'index']);

// Gas Control Routes
Route::get('/gas-control', [GasControlController::class, 'index']);
Route::post('/gas-control', [GasControlController::class, 'update']);
Route::get('/iot/gas-config', [GasControlController::class, 'gasConfig']);

//detail
Route::get('/detail/gas', [DetailController::class, 'gas']);
Route::get('/detail/sampah', [DetailController::class, 'sampah']);
Route::get('/detail/pengguna', [DetailController::class, 'pengguna']);
