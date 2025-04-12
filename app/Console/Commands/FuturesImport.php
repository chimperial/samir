<?php

namespace App\Console\Commands;

use App\Trading\ChampionManager;
use App\Trading\TradingManager;
use Exception;
use Illuminate\Console\Command;

class FuturesImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'futures:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Interacts With Binance FuturesStatus API (Development)';

    /**
     * Execute the console command.
     *
     * @param ChampionManager $championManager
     * @return void
     * @throws Exception
     */
    public function handle(ChampionManager $championManager): void
    {
        $this->info('Starting futures import process...');
        
        $champions = $championManager->getActiveFarmers();
        $this->info(sprintf('Found %d active farmers', $champions->count()));

        $champions->each(function ($champion) {
            $this->info(sprintf('Processing champion: %s', $champion->name));
            
            try {
                TradingManager::useChampion($champion);
                $result = TradingManager::importRecentOrders();
                $this->info(sprintf('Successfully imported orders for %s', $champion->name));
            } catch (Exception $e) {
                $this->error(sprintf('Error processing champion %s: %s', $champion->name, $e->getMessage()));
            }
        });

        $this->info('Futures import process completed');
    }
}