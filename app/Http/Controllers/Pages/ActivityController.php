<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $logEntries = [];
        $logFile = storage_path('logs/laravel.log');
        $page = (int)$request->input('page', 1);
        $perPage = 5;
        
        if (File::exists($logFile)) {
            $logContent = File::get($logFile);
            $lines = explode("\n", $logContent);
            $matchingLines = [];
            
            foreach ($lines as $line) {
                if (strpos($line, 'production.INFO') !== false) {
                    $matchingLines[] = $line;
                }
            }
            
            // Get the current page's lines (newest first)
            $matchingLines = array_reverse($matchingLines);
            
            // Calculate total pages
            $totalLines = count($matchingLines);
            $totalPages = ceil($totalLines / $perPage);
            
            // Ensure page is within valid range
            $page = max(1, min($page, $totalPages));
            
            // Get the current page's lines
            $startIndex = ($page - 1) * $perPage;
            $currentPageLines = array_slice($matchingLines, $startIndex, $perPage);
            
            // Sort current page lines in ascending order
            usort($currentPageLines, function($a, $b) {
                preg_match('/\[(.*?)\]/', $a, $matchesA);
                preg_match('/\[(.*?)\]/', $b, $matchesB);
                return strtotime($matchesA[1]) - strtotime($matchesB[1]);
            });
            
            foreach ($currentPageLines as $line) {
                // Extract timestamp and message
                if (preg_match('/\[(.*?)\].*?production\.INFO: (.*)/', $line, $matches)) {
                    $timestamp = Carbon::createFromFormat('Y-m-d H:i:s', $matches[1]);
                    $logEntries[] = [
                        'timestamp' => $timestamp->diffForHumans(),
                        'message' => $matches[2]
                    ];
                }
            }
        }
        
        return inertia('Activities', [
            'logEntries' => $logEntries,
            'currentPage' => $page,
            'hasMore' => $page < $totalPages
        ]);
    }
} 