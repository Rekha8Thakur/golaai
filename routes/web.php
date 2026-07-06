<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\VideoController;

Route::get('/', [VideoController::class, 'index'])->name('videos.index');
Route::post('/videos', [VideoController::class, 'store'])->name('videos.store');
Route::get('/videos/{video}', [VideoController::class, 'show'])->name('videos.show');
Route::get('/videos/{video}/pdf', [VideoController::class, 'downloadPdf'])->name('videos.pdf');

Route::get('/debug-python', function() {
    $service = new \App\Services\YouTubeService();
    $reflector = new ReflectionClass(\App\Services\YouTubeService::class);
    $method = $reflector->getMethod('getPythonPath');
    $method->setAccessible(true);
    $pythonPath = $method->invoke($service);
    
    $whereResult = \Illuminate\Support\Facades\Process::run(['where.exe', 'python']);
    
    return response()->json([
        'os' => PHP_OS,
        'resolved_python_path' => $pythonPath,
        'where_successful' => $whereResult->successful(),
        'where_output' => explode("\n", $whereResult->output()),
        'where_error' => $whereResult->errorOutput(),
        'env_path' => getenv('PATH'),
    ]);
});
