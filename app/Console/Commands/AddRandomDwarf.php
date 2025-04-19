<?php

namespace App\Console\Commands;

use App\Trading\Champion;
use Illuminate\Console\Command;

class AddRandomDwarf extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dwarf:add-random';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a random dwarf to the database';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->info('Creating a random dwarf...');

        $symbols = ['BTCUSDT', 'ETHUSDT', 'BNBUSDT', 'ADAUSDT', 'DOGEUSDT'];
        $randomSymbol = $symbols[array_rand($symbols)];

        $capital = rand(100, 1000) * 100; // Random capital between 10,000 and 100,000
        $grind = $capital * 0.1; // 10% of capital

        $dwarf = Champion::create([
            'name' => 'Dwarf_' . strtoupper(substr(uniqid(), -6)),
            'archetype' => 'dwarf',
            'status' => 'active',
            'symbol' => $randomSymbol,
            'capital' => $capital,
            'grind' => $grind,
            'onduty' => 0,
            'profit' => 0,
            'roi' => 0,
            'fee' => 0,
            'income' => 0,
            'entry' => 0,
            'realm' => 'spot',
        ]);

        $this->info('Dwarf created successfully!');
        $this->table(
            ['Field', 'Value'],
            [
                ['Name', $dwarf->name],
                ['Symbol', $dwarf->symbol],
                ['Capital', number_format($dwarf->capital, 2)],
                ['Grind', number_format($dwarf->grind, 2)],
                ['Status', $dwarf->status],
                ['Realm', $dwarf->realm],
            ]
        );
    }
} 