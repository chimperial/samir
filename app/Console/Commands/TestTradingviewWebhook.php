<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\HandleTradingviewHookController;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use App\Trading\TradingManager;

class TestTradingviewWebhook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:tradingview-webhook';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test TradingView webhook payload for long entry with SL/TP';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $payload = [
            'direction' => 'up',
            'client' => 'samir',
            'symbol' => 'TONUSDT',
            'indicator' => 'zen_pulse_long_5m',
            'price' => '2.8349',
            'sl' => '2.7843',
            'tp' => '3.1',
        ];

        // Set up champion context for TradingManager
        // (simulate what the controller does)
        $request = new Request([
            'payloads' => $payload
        ]);

        // Use the same rounding as in the controller
        // This will only work if TradingManager::useChampion is called before
        // (which the controller does)
        // So we let the controller handle the rounding
        $controller = new HandleTradingviewHookController();
        $result = $controller($request);
        $this->info('Webhook processed. Result: ' . json_encode($result));
    }
} 