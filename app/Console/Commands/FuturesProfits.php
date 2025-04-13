<?php

namespace App\Console\Commands;

use App\Trading\Champion;
use App\Trading\ChampionManager;
use App\Trading\TradingManager;
use Exception;
use Illuminate\Console\Command;

class FuturesProfits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'futures:profits';

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
        $this->info('Starting futures profits collection...');

        $champions = $championManager->getActiveFarmers();
        $this->info(sprintf('Found %d active farming champions', $champions->count()));

        $champions->each(function($champion) {
            try {
                $this->info(sprintf('Processing champion ID: %d, Symbol: %s', $champion->id, $champion->symbol));
                
                TradingManager::useChampion($champion);
                
                $this->info('Collecting profits...');
                TradingManager::collectProfits();
                
                $this->info('Collecting recent incomes...');
                TradingManager::collectRecentIncomes();
                
                $this->info(sprintf('Completed processing champion ID: %d', $champion->id));
            } catch (Exception $e) {
                $this->error(sprintf('Error processing champion ID: %d - %s', $champion->id, $e->getMessage()));
                report($e);
            }
        });

        $this->info('Futures profits collection completed');
    }
}
