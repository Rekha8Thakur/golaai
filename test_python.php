<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\YouTubeService;
use Illuminate\Support\Facades\Process;

$service = new YouTubeService();
// Reflect or access protected getPythonPath
$reflector = new ReflectionClass(YouTubeService::class);
$method = $reflector->getMethod('getPythonPath');
$method->setAccessible(true);
$pythonPath = $method->invoke($service);

echo "Resolved Python Path: " . $pythonPath . PHP_EOL;

// Test running the python command directly
$scriptPath = base_path('get_transcript_api.py');
$videoId = 'dQw4w9WgXcQ';
$result = Process::run([$pythonPath, $scriptPath, $videoId]);

echo "Successful: " . ($result->successful() ? 'YES' : 'NO') . PHP_EOL;
echo "Exit Code: " . $result->exitCode() . PHP_EOL;
echo "Output: " . $result->output() . PHP_EOL;
echo "Error Output: " . $result->errorOutput() . PHP_EOL;
