<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\VideoController;

Route::get('/', [VideoController::class, 'index'])->name('videos.index');
Route::post('/videos', [VideoController::class, 'store'])->name('videos.store');
Route::get('/videos/{video}', [VideoController::class, 'show'])->name('videos.show');
Route::get('/videos/{video}/pdf', [VideoController::class, 'downloadPdf'])->name('videos.pdf');

Route::get('/debug-python', function() {
    $python3Result = \Illuminate\Support\Facades\Process::run(['which', 'python3']);
    $pythonResult = \Illuminate\Support\Facades\Process::run(['which', 'python']);
    
    $python3Version = \Illuminate\Support\Facades\Process::run(['python3', '--version']);
    $pythonVersion = \Illuminate\Support\Facades\Process::run(['python', '--version']);
    
    return response()->json([
        'os' => PHP_OS,
        'which_python3' => trim($python3Result->output()),
        'which_python3_successful' => $python3Result->successful(),
        'which_python' => trim($pythonResult->output()),
        'which_python_successful' => $pythonResult->successful(),
        'python3_version' => trim($python3Version->output() ?: $python3Version->errorOutput()),
        'python_version' => trim($pythonVersion->output() ?: $pythonVersion->errorOutput()),
    ]);
});
