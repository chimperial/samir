<?php

namespace App\Console\Commands;

use App\Trading\ChampionManager;
use App\Trading\TradingManager;
use Exception;
use Illuminate\Console\Command;

class FuturesStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'futures:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Interacts With Binance FuturesStatus API (Development)';

    /**
     * Execute the console command.
     *
     * @return void
     * @throws Exception
     */
    public function handle(ChampionManager $championManager): void
    {
        $this->info('Starting futures status check...');
        
        $champions = $championManager->getActiveFarmers();
        $this->info(sprintf('Found %d active farmers', $champions->count()));

        $champions->each(function ($champion) {
            $this->info(sprintf('Processing champion: %s', $champion->name));
            
            try {
                TradingManager::useChampion($champion);
                TradingManager::importRecentTrades();
                $this->info(sprintf('Successfully imported trades for %s', $champion->name));
            } catch (Exception $e) {
                $this->error(sprintf('Error processing champion %s: %s', $champion->name, $e->getMessage()));
            }
        });

        $this->info('Futures status check completed');
    }
}