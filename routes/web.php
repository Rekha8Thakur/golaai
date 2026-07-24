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

Route::get('/migrate', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        return 'Migrations ran successfully: <br><pre>' . \Illuminate\Support\Facades\Artisan::output() . '</pre>';
    } catch (\Exception $e) {
        return 'Error running migrations: ' . $e->getMessage();
    }
});

Route::get('/pip-install', function () {
    try {
        $result = \Illuminate\Support\Facades\Process::run(['python3', '-m', 'pip', 'install', '--user', 'youtube-transcript-api']);
        if ($result->successful()) {
            return 'Pip install completed successfully: <br><pre>' . $result->output() . '</pre>';
        }
        
        $result2 = \Illuminate\Support\Facades\Process::run(['pip', 'install', '--user', 'youtube-transcript-api']);
        if ($result2->successful()) {
            return 'Pip install fallback completed successfully: <br><pre>' . $result2->output() . '</pre>';
        }
        
        return 'Failed to install: <br>Output: <pre>' . $result->output() . '</pre><br>Error: <pre>' . $result->errorOutput() . '</pre><br>Fallback Error: <pre>' . $result2->errorOutput() . '</pre>';
    } catch (\Exception $e) {
        return 'Error running pip install: ' . $e->getMessage();
    }
});

Route::get('/check-python', function () {
    try {
        $result = \Illuminate\Support\Facades\Process::run(['python3', '-c', 'import youtube_transcript_api; print(dir(youtube_transcript_api.YouTubeTranscriptApi))']);
        return 'Python Output: <br><pre>' . $result->output() . '</pre><br>Error: <pre>' . $result->errorOutput() . '</pre>';
    } catch (\Exception $e) {
        return 'Error checking python: ' . $e->getMessage();
    }
});


