<?php

namespace App\Console\Commands;

use App\Trading\Champion;
use App\Trading\SpotTradingManager;
use Exception;
use Illuminate\Console\Command;

use App\Trading\ChampionManager;

class AccountCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'account {--sync : Sync all active accounts} {--farmers : Sync only active farmers} {--lootcycles : Sync only active lootcycles}';

    protected ChampionManager $championManager;

    protected SpotTradingManager $spotTradingManager;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage and sync trading accounts. This command handles synchronization of active farmers and lootcycles, including order syncing and trade collection.';

    public function __construct(ChampionManager $championManager, SpotTradingManager $spotTradingManager)
    {
        $this->championManager = $championManager;
        $this->spotTradingManager = $spotTradingManager;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     * @throws Exception
     */
    public function handle(): void
    {
        if ($this->option('sync') || (!$this->option('farmers') && !$this->option('lootcycles'))) {
            $this->syncActiveFarmers();
            $this->syncActiveLootcycles();
            $this->info('Successfully synced all active accounts');
            return;
        }

        if ($this->option('farmers')) {
            $this->syncActiveFarmers();
            $this->info('Successfully synced active farmers');
        }

        if ($this->option('lootcycles')) {
            $this->syncActiveLootcycles();
            $this->info('Successfully synced active lootcycles');
        }
    }

    public function syncActiveLootcycles()
    {
        $champions = $this->championManager->getActiveLootcycles();

        $champions->each(function($champion) {
            $this->spotTradingManager
                ->useChampion($champion)
                ->syncOrdersFromExchange()
                ->collectTrades();

            $this->championManager->syncLootcycle($champion);
        });
    }

    public function syncActiveFarmers()
    {
        $champions = $this->championManager->getActiveFarmers();

        $champions->each(function($champion) {
            $this->championManager->sync($champion);
        });
    }
}
