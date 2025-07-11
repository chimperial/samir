<?php

namespace App\Console\Commands;

use App\Trading\PositionSizeCalculator;
use App\Trading\TradingManager;
use App\Trading\Champion;
use Illuminate\Console\Command;

class TestPositionSizeCalculator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:position-size';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the PositionSizeCalculator with real champion data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->setupChampion();
        $this->runTest();
    }

    private function setupChampion()
    {
        // Get the real champion from database
        $champion = Champion::find(9);
        
        if (!$champion) {
            $this->error('Champion with ID 9 not found in database');
            return;
        }

        $this->info('=== Using Champion Data ===');
        $this->info("ID: {$champion->id}");
        $this->info("Symbol: {$champion->symbol}");
        $this->info("Base Capital: {$champion->capital}");
        $this->info("Current Capital: {$champion->current_capital}");
        $this->info("Status: {$champion->status}");
        $this->info("Can Trade: " . ($champion->can_trade ? 'Yes' : 'No'));
        $this->info('');

        // Set the champion in TradingManager
        TradingManager::useChampion($champion);
    }

    private function runTest()
    {
        $this->info('=== Testing Position Size Calculator ===');
        
        $champion = Champion::find(9);
        if (!$champion) {
            return;
        }

        $calculator = new PositionSizeCalculator();

        // Test with current market price
        try {
            $currentPrice = TradingManager::currentPrice();
            $this->info("Current Market Price: $currentPrice");
            
            // Test scenarios with real data
            $testCases = [
                [
                    'name' => 'Test with 1% stop loss',
                    'entry' => $currentPrice,
                    'stopLoss' => $currentPrice * 0.99, // 1% below entry
                    'takeProfit' => $currentPrice * 1.02, // 2% above entry
                ],
                [
                    'name' => 'Test with 2% stop loss',
                    'entry' => $currentPrice,
                    'stopLoss' => $currentPrice * 0.98, // 2% below entry
                    'takeProfit' => $currentPrice * 1.03, // 3% above entry
                ],
                [
                    'name' => 'Test with no stop loss (min size)',
                    'entry' => $currentPrice,
                    'stopLoss' => null,
                    'takeProfit' => $currentPrice * 1.02,
                ],
                [
                    'name' => 'Test with entry equals stop loss (min size)',
                    'entry' => $currentPrice,
                    'stopLoss' => $currentPrice,
                    'takeProfit' => $currentPrice * 1.02,
                ],
                [
                    'name' => 'Test with specific values',
                    'entry' => 0.27113,
                    'stopLoss' => 0.27013,
                    'takeProfit' => 0.27213,
                ],
                [
                    'name' => 'Test with specific values',
                    'entry' => 0.28688,
                    'stopLoss' => 0.28408,
                    'takeProfit' => 0.28964,
                ],
            ];

            foreach ($testCases as $index => $testCase) {
                $this->info('');
                $this->info(($index + 1) . '. ' . $testCase['name']);
                $this->info("   Entry: " . number_format($testCase['entry'], 5));
                $this->info("   Stop Loss: " . ($testCase['stopLoss'] ? number_format($testCase['stopLoss'], 5) : 'None'));
                $this->info("   Take Profit: " . number_format($testCase['takeProfit'], 5));

                $size = $calculator->calculateSize(
                    $champion->current_capital,
                    $testCase['entry'],
                    $testCase['stopLoss'],
                    $testCase['takeProfit']
                );

                $this->info("   Calculated Size: $size");
                
                if ($testCase['stopLoss'] && $testCase['entry'] != $testCase['stopLoss']) {
                    $positionNotional = $size * $testCase['entry'];
                    $this->info("   Position Value: $" . number_format($positionNotional, 2));
                    
                    // Calculate risk percentage
                    $riskPercent = abs($testCase['entry'] - $testCase['stopLoss']) / $testCase['entry'];
                    $this->info("   Risk Percentage: " . number_format($riskPercent * 100, 2) . '%');
                }
            }

        } catch (\Exception $e) {
            $this->error("Error during testing: " . $e->getMessage());
        }
    }
} 