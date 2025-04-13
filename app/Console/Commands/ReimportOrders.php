<?php

namespace App\Console\Commands;

use App\Trading\Champion;
use App\Trading\TradingManager;
use Illuminate\Console\Command;

class ReimportOrders extends Command
{
    protected $signature = 'orders:reimport {symbol}';
    protected $description = 'Reimport orders for a specific symbol from Binance';

    public function handle()
    {
        $symbol = $this->argument('symbol');
        
        $champion = Champion::where('symbol', $symbol)->first();
        
        if (!$champion) {
            $this->error("No champion found for symbol: {$symbol}");
            return 1;
        }

        $this->info("Reimporting orders for {$symbol}...");
        
        try {
            // First delete existing orders
            $deleted = \App\Trading\Order::where('symbol', $symbol)->delete();
            $this->info("Deleted {$deleted} existing orders");
            
            // Use the champion and import orders
            TradingManager::useChampion($champion);
            
            // Import orders using the base import method
            $this->info("Importing orders from Binance...");
            TradingManager::importOrders();
            
            // Get count of imported orders
            $count = \App\Trading\Order::where('symbol', $symbol)->count();
            $this->info("Imported {$count} orders");
            
            // Show sample of imported orders
            $sample = \App\Trading\Order::where('symbol', $symbol)
                ->orderBy('update_time', 'desc')
                ->first();
            
            if ($sample) {
                $this->info("Sample order:");
                $this->info("Order ID: " . $sample->order_id);
                $this->info("Price: " . $sample->price);
                $this->info("Quantity: " . $sample->orig_qty);
                $this->info("Status: " . $sample->status);
            }
            
            $this->info("Successfully completed import process");
        } catch (\Exception $e) {
            $this->error("Error reimporting orders: " . $e->getMessage());
            $this->error("Stack trace: " . $e->getTraceAsString());
            return 1;
        }

        return 0;
    }
} 