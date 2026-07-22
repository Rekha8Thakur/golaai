<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\VideoController;

Route::get('/', [VideoController::class, 'index'])->name('videos.index');
Route::post('/videos', [VideoController::class, 'store'])->name('videos.store');
Route::get('/videos/{video}', [VideoController::class, 'show'])->name('videos.show');
Route::get('/videos/{video}/pdf', [VideoController::class, 'downloadPdf'])->name('videos.pdf');

Route::get('/debug-python', function() {
    $results = [];
    $versions = ['python3.12', 'python3.11', 'python3.10', 'python3.9', 'python3.8', 'python3', 'python'];
    foreach ($versions as $cmd) {
        $run = \Illuminate\Support\Facades\Process::run([$cmd, '--version']);
        $results[$cmd] = [
            'exists' => $run->successful(),
            'version' => trim($run->output() ?: $run->errorOutput()),
        ];
    }
    return response()->json([
        'os' => PHP_OS,
        'results' => $results,
    ]);
});
