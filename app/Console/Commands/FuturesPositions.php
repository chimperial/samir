<?php

namespace App\Console\Commands;

use App\Trading\ChampionManager;
use App\Trading\TradingManager;
use Exception;
use Illuminate\Console\Command;

class FuturesPositions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'futures:positions';

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
        $this->info('Starting futures positions process...');
        
        $champions = $championManager->getActiveFarmers();
        $this->info(sprintf('Found %d active farmers', $champions->count()));

        $champions->each(function ($champion) {
            $this->info(sprintf('Processing champion: %s', $champion->name));
            
            try {
                TradingManager::useChampion($champion);
                $positions = TradingManager::positions();
                
                $this->info(sprintf('Positions for %s:', $champion->name));
                $this->info(json_encode($positions, JSON_PRETTY_PRINT));
                
                $hasShortPosition = $positions->contains(function ($position) {
                    return $position['positionSide'] === 'SHORT' && $position['positionAmt'] != 0;
                });
                
                if ($hasShortPosition) {
                    $this->info(sprintf('Champion %s has a short position', $champion->name));
                } else {
                    $this->info(sprintf('Champion %s has no short position', $champion->name));
                }
                
                $this->info(sprintf('Successfully processed positions for %s', $champion->name));
            } catch (Exception $e) {
                $this->error(sprintf('Error processing champion %s: %s', $champion->name, $e->getMessage()));
            }
        });
    }
}