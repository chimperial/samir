<?php

namespace App\Console\Commands;

use App\Trading\ChampionManager;
use App\Trading\SpotTradingManager;
use Exception;
use Illuminate\Console\Command;

class SpotDown extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spot:down';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Executes spot trading down operations for active dwarfs using Binance API';

    /**
     * Execute the console command.
     *
     * @param ChampionManager $championManager
     * @return void
     * @throws Exception
     */
    public function handle(ChampionManager $championManager): void
    {
        $this->info('Starting spot trading down operations...');
        
        $champions = $championManager->getActiveDwarfs();
        $count = $champions->count();
        
        $this->info("Found {$count} active dwarfs to process");
        
        if ($count === 0) {
            $this->warn('No active dwarfs found. Exiting...');
            return;
        }

        $champions->each(function ($champion) {
            $this->line("Processing dwarf: {$champion->name}");
            
            try {
                SpotTradingManager::useChampion($champion);
                SpotTradingManager::handleDown();
                $this->info("Successfully processed dwarf: {$champion->name}");
            } catch (Exception $e) {
                $this->error("Error processing dwarf {$champion->name}: {$e->getMessage()}");
            }
        });

        $this->info('Spot trading down operations completed');
    }
} 