<?php

namespace App\Trading;

use App\Binance\Binance;
use Carbon\Carbon;
use Exception;

class TradingManager
{
    private static ?Binance $binance = null;

    private static ?Champion $champion = null;

    /**
     * @throws Exception
     */
    public static function handleDown(): void
    {
        info('Use champion ID: ' . self::$champion->id);
        info('Handle Down');
        if (self::binance()->hasShortPosition()) {
            info('Has short position');
            self::maybeTakeShortProfit();
        }

        if (self::shouldOpenLong()) {
            info('Should open long');
            self::openLong();
        }
        info('Handle Down End');
    }

    /**
     * @throws Exception
     */
    public static function handleUp(): void
    {
        info('Starting handleUp process for champion ID: ' . self::$champion->id);
        info('Current market state: UP');

        if (self::binance()->hasLongPosition()) {
            info('Found existing long position - checking for profit taking opportunity');
            self::maybeTakeLongProfit();
        } else {
            info('No active long position found');
        }

        if (self::shouldOpenShort()) {
            info('Market conditions met for opening short position');
            self::openShort();
        } else {
            info('Market conditions not suitable for opening short position');
        }

        info('Completed handleUp process');
    }

    /**
     * @throws Exception
     */
    public static function import(): void
    {
        $orders = self::binance()->orders();

        foreach ($orders as $order) {
            $order['cumQty'] = $order['executedQty'];
            self::upsertOrder($order);
            self::collectTrades($order['orderId']);
        }
    }

    /**
     * @throws Exception
     */
    public static function importTrades(): void
    {
        $orders = Order::all()->pluck('order_id');

        $orders->each(function ($orderId) {
            self::collectTrades($orderId);
        });
    }

    /**
     * @throws Exception
     */
    public static function importRecentTrades(): array
    {
        try {
            $trades = self::collectTrades(null);
            return $trades;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @throws Exception
     */
    public static function importOrders(): void
    {
        $orders = self::binance()->orders();

        foreach ($orders as $order) {
            $order['cumQty'] = $order['executedQty'];
            self::upsertOrder($order);
        }
    }

    /**
     * @throws Exception
     */
    public static function importRecentOrders(): void
    {
        info('Starting importRecentOrders for champion ID: ' . self::$champion->id);
        
        tap(
            Order::query()
                ->where('status', '=', Order::STATUS_NEW)
                ->where('champion_id', '=', self::$champion->id)
                ->orderBy('order_id')
                ->first(),

            function ($latestOrder) {
                if ($latestOrder) {
                    info('Found latest order:', [
                        'order_id' => $latestOrder->order_id,
                        'update_time' => $latestOrder->update_time,
                        'symbol' => $latestOrder->symbol
                    ]);
                    
                    try {
                        $orders = self::binance()->orders(self::$champion->symbol, (int)($latestOrder->update_time - 1000));

                        info('Raw Binance response:', $orders);
                        info('Retrieved ' . count($orders) . ' orders from Binance');
                        
                        foreach ($orders as $order) {
                            $order['cumQty'] = $order['executedQty'];
                            self::upsertOrder($order);
                            info('Upserted order:', [
                                'order_id' => $order['orderId'],
                                'status' => $order['status'],
                                'executed_qty' => $order['executedQty']
                            ]);
                        }
                    } catch (Exception $e) {
                        info('Error importing recent orders: ' . $e->getMessage());
                        throw $e;
                    }
                } else {
                    info('No latest order found for champion ID: ' . self::$champion->id);
                }
            }
        );
        
        info('Completed importRecentOrders for champion ID: ' . self::$champion->id);
    }

    /**
     * @throws Exception
     */
    public static function positions()
    {
        return self::binance()->positions();
    }

    /**
     * @throws Exception
     */
    private static function openLong(): void
    {
        $binanceOrder = self::binance()->openLong(
            self::minSize(),
            round(self::currentPrice() - self::priceGap(), self::getPrecision('price')),
        );

        self::upsertOrder($binanceOrder);
    }

    /**
     * @throws Exception
     */
    private static function openShort(): void
    {
        $binanceOrder = self::binance()->openShort(
            self::minSize(),
            round(self::currentPrice() + self::priceGap(), self::getPrecision('price')),
        );

        self::upsertOrder($binanceOrder);
    }

    /**
     * Get precision for quantity or price based on symbol
     * 
     * @param string $type 'quantity' for quantity precision, 'price' for price precision
     * @return int
     */
    public static function getPrecision(string $type = 'quantity'): int
    {
        $precisionMap = [
            'BTCUSDT' => ['quantity' => 5, 'price' => 1],
            'ETHUSDT' => ['quantity' => 3, 'price' => 2],
            'BNBUSDT' => ['quantity' => 2, 'price' => 2],
            'SOLUSDT' => ['quantity' => 2, 'price' => 4],
            'TONUSDT' => ['quantity' => 1, 'price' => 8],
            'AAVEUSDT' => ['quantity' => 1, 'price' => 3],
            'DOGEUSDT' => ['quantity' => 0, 'price' => 5],
            'TRXUSDT' => ['quantity' => 0, 'price' => 5],
        ];

        $symbol = self::$champion->symbol;
        
        if (isset($precisionMap[$symbol])) {
            return $precisionMap[$symbol][$type];
        }

        // Default precision for unknown symbols
        return $type === 'price' ? 2 : 2;
    }

    /**
     * @deprecated Use getPrecision('price') instead
     */
    public static function getPricePrecision(): int
    {
        return self::getPrecision('price');
    }

    /**
     * @throws Exception
     */
    public static function minSize(): float
    {
        return round(self::$champion->grind / self::currentPrice(), self::getPrecision('quantity'));
    }

    /**
     * @throws Exception
     */
    public static function currentPrice(): float
    {
        return round(self::binance()->getMarkPrice(), self::getPrecision('price'));
    }

    /**
     * @throws Exception
     */
    private static function binance(): ?Binance
    {
        if (!self::$binance) {
            self::$binance = new Binance(self::$champion->symbol);
        }

        return self::$binance;
    }

    private static function upsertOrder(array $data): void
    {
        Order::query()->upsert([
            [
                'order_id' => $data['orderId'],
                'symbol' => $data['symbol'],
                'status' => Order::STATUS[$data['status']],
                'client_order_id' => $data['clientOrderId'],
                'price' => $data['price'],
                'avg_price' => $data['avgPrice'],
                'orig_qty' => $data['origQty'],
                'executed_qty' => $data['executedQty'],
                'cum_qty' => $data['cumQty'],
                'cum_quote' => $data['cumQuote'],
                'time_in_force' => $data['timeInForce'],
                'type' => $data['type'],
                'reduce_only' => $data['reduceOnly'],
                'close_position' => $data['closePosition'],
                'side' => $data['side'],
                'position_side' => $data['positionSide'],
                'stop_price' => $data['stopPrice'],
                'working_type' => $data['workingType'],
                'price_protect' => $data['priceProtect'],
                'orig_type' => $data['origType'],
                'price_match' => $data['priceMatch'],
                'self_trade_prevention_mode' => $data['selfTradePreventionMode'],
                'good_till_date' => $data['goodTillDate'],
                'update_time' => $data['updateTime'],
                'champion_id' => self::$champion->id
            ]
        ],
            ['order_id'],
            ['status', 'avg_price', 'cum_qty', 'cum_quote', 'executed_qty', 'update_time', 'champion_id']
        );
    }

    public static function test(): void
    {
        dd(self::$champion);
    }

    /**
     * @throws Exception
     */
    private static function collectTrades(?string $time): array
    {
        try {
            $binanceTrades = self::binance()->collectTrades($time);
            
            foreach ($binanceTrades as $binanceTrade) {
                self::upsertTrade($binanceTrade);
            }
            
            info('Successfully processed all trades');
            return $binanceTrades;
        } catch (Exception $e) {
            info('Error collecting trades: ' . $e->getMessage());
            throw $e;
        }
    }

    private static function upsertTrade(array $data): void
    {
        $tradeData = [
            'id' => $data['id'],
            'symbol' => $data['symbol'],
            'order_id' => $data['orderId'],
            'side' => $data['side'],
            'price' => $data['price'],
            'qty' => $data['qty'],
            'realized_pnl' => $data['realizedPnl'] ?? 0,
            'margin_asset' => $data['marginAsset'] ?? 'USDT',
            'quote_qty' => $data['quoteQty'],
            'commission' => $data['commission'],
            'commission_asset' => $data['commissionAsset'],
            'time' => $data['time'],
            'position_side' => $data['positionSide'],
            'maker' => $data['maker'],
            'buyer' => $data['buyer'],
        ];

        Trade::query()->upsert(
            [$tradeData],
            ['id'],
            ['time']
        );
    }

    public static function status()
    {
        $orders = Order::query()
            ->where('status', '=', Order::STATUS_FILLED)
            ->get();

        $longs = $orders->where('position_side', '=', Order::POSITION_SIDE_LONG);
        $shorts = $orders->where('position_side', '=', Order::POSITION_SIDE_SHORT);

        dd([
            'long' => $longs->map(function ($order) {
                $order->cum_qty = $order->reduce_only ? $order->cum_qty * -1 : $order->cum_qty;
                return $order;
            })->sum('cum_qty'),
            'short' => $shorts->map(function ($order) {
                $order->cum_qty = $order->reduce_only ? $order->cum_qty * -1 : $order->cum_qty;
                return $order;
            })->sum('cum_qty'),
        ]);
    }

    /**
     * @throws Exception
     */
    public static function hasLongProfit(): bool
    {
        if (!self::binance()->hasLongPosition()) {
            return false;
        }
        
        $fee = self::$champion->grind * 0.0005; // 0.05% fee based on champion's grind
        return self::binance()->positions()->get(0)['unRealizedProfit'] - $fee > 0;
    }

    /**
     * @throws Exception
     */
    public static function hasShortProfit(): bool
    {
        if (!self::binance()->hasShortPosition()) {
            return false;
        }
        
        $fee = self::$champion->grind * 0.0005; // 0.05% fee based on champion's grind
        return self::binance()->positions()->get(1)['unRealizedProfit'] - $fee > 0;
    }

    /**
     * @throws Exception
     */
    private static function maybeTakeLongProfit(): void
    {
        if (self::hasLongProfit()) {
            info('long profit');
            self::takeLongProfit();
        }
    }

    /**
     * @throws Exception
     */
    private static function maybeTakeShortProfit(): void
    {
        if (self::hasShortProfit()) {
            info('short profit');
            self::takeShortProfit();
        }
    }

    /**
     * @throws Exception
     */
    private static function takeLongProfit(): void
    {
        $longPosition = self::binance()->positions()->get(0);
        $positionNotional = abs((float)$longPosition['notional']);
        $grind = self::$champion->grind;
        
        info('Taking long profit - Position details:', [
            'position_notional' => $positionNotional,
            'champion_grind' => $grind,
            'unrealized_profit' => $longPosition['unRealizedProfit'],
            'leverage' => $longPosition['leverage'],
            'entry_price' => $longPosition['entryPrice'],
            'current_price' => self::currentPrice()
        ]);
        
        // If position notional is close to grind (within 20%), use position notional instead
        $size = abs($positionNotional - $grind) / $grind <= 0.20 
            ? round($positionNotional / self::currentPrice(), self::getPrecision('quantity'))
            : self::minSize();

        info('Calculated trade size:', [
            'size' => $size,
            'using_position_notional' => abs($positionNotional - $grind) / $grind <= 0.20,
            'quantity_precision' => self::getPrecision('quantity'),
            'current_price' => self::currentPrice()
        ]);

        $binanceOrder = self::binance()->closeLong(
            $size,
            self::currentPrice() + self::priceGap(),
        );

        info('Closing long position - Order details:', [
            'order_id' => $binanceOrder['orderId'],
            'size' => $size,
            'price' => self::currentPrice() + self::priceGap(),
            'price_gap' => self::priceGap()
        ]);

        self::upsertOrder($binanceOrder);
    }

    /**
     * @throws Exception
     */
    private static function takeShortProfit(): void
    {
        $shortPosition = self::binance()->positions()->get(1);
        $positionNotional = abs((float)$shortPosition['notional']);
        $grind = self::$champion->grind;
        
        info('Taking short profit - Position details:', [
            'position_notional' => $positionNotional,
            'champion_grind' => $grind,
            'unrealized_profit' => $shortPosition['unRealizedProfit'],
            'leverage' => $shortPosition['leverage'],
            'entry_price' => $shortPosition['entryPrice'],
            'current_price' => self::currentPrice()
        ]);
        
        // If position notional is close to grind (within 20%), use position notional instead
        $size = abs($positionNotional - $grind) / $grind <= 0.20 
            ? round($positionNotional / self::currentPrice(), self::getPrecision('quantity'))
            : self::minSize();

        info('Calculated trade size:', [
            'size' => $size,
            'using_position_notional' => abs($positionNotional - $grind) / $grind <= 0.20,
            'quantity_precision' => self::getPrecision('quantity'),
            'current_price' => self::currentPrice()
        ]);

        $binanceOrder = self::binance()->closeShort(
            $size,
            self::currentPrice() - self::priceGap(),
        );

        info('Closing short position - Order details:', [
            'order_id' => $binanceOrder['orderId'],
            'size' => $size,
            'price' => self::currentPrice() - self::priceGap(),
            'price_gap' => self::priceGap()
        ]);

        self::upsertOrder($binanceOrder);
    }

    /**
     * @throws Exception
     */
    private static function getClosableLongOrder(): ?object
    {
        return Order::query()
            ->where('status', '=', Order::STATUS_FILLED)
            ->where('position_side', '=', Order::POSITION_SIDE_LONG)
            ->where('side', '=', Order::SIDE_BUY)
            ->where('avg_price', '<=', self::currentPrice())
            ->where('champion_id', '=', self::$champion->id)
            ->orderByDesc('update_time')
            ->first();
    }

    /**
     * @throws Exception
     */
    private static function getClosableShortOrder(): ?object
    {
        return Order::query()
            ->where('status', '=', Order::STATUS_FILLED)
            ->where('position_side', '=', Order::POSITION_SIDE_SHORT)
            ->where('side', '=', Order::SIDE_SELL)
            ->where('avg_price', '>=', self::currentPrice())
            ->where('champion_id', '=', self::$champion->id)
            ->orderByDesc('update_time')
            ->first();
    }

    /** @noinspection DuplicatedCode */
    /**
     * @throws Exception
     */
    private static function shouldOpenShort(): bool
    {
        if (self::binance()->hasShortProfit() || self::$champion->can_trade === false) {
            return false;
        }

        // Get the short position
        $shortPosition = self::binance()->positions()->get(1);
        
        if (!$shortPosition || $shortPosition['positionAmt'] == 0) {
            return true;
        }

        // Check if short position notional plus grind is within 50% of champion's capital
        $shortNotional = abs((float)$shortPosition['notional']) + self::$champion->grind;
        if ($shortNotional > (self::$champion->capital * 0.5)) {
            info('Short position notional plus grind exceeds 50% of champion capital', [
                'short_notional' => abs((float)$shortPosition['notional']),
                'grind' => self::$champion->grind,
                'total_notional' => $shortNotional,
                'champion_capital' => self::$champion->capital,
                'max_allowed' => self::$champion->capital * 0.5
            ]);
            return false;
        }

        // Get the latest filled short order
        $latestOrder = Order::query()
            ->where('status', '=', Order::STATUS_FILLED)
            ->where('position_side', '=', Order::POSITION_SIDE_SHORT)
            ->where('side', '=', Order::SIDE_SELL)
            ->where('champion_id', '=', self::$champion->id)
            ->orderByDesc('update_time')
            ->first();

        if (!$latestOrder) {
            return true;
        }

        // Check if price has increased 1% from latest filled order price
        $currentPrice = self::currentPrice();
        $orderPrice = (float)$latestOrder->avg_price;
        $priceIncreasePercentage = (($currentPrice - $orderPrice) / $orderPrice) * 100;

        info('Price check for short position:', [
            'order_price' => $orderPrice,
            'current_price' => $currentPrice,
            'price_increase_percentage' => $priceIncreasePercentage,
            'should_open' => $priceIncreasePercentage >= 2
        ]);

        return $priceIncreasePercentage >= 2;
    }

    /** @noinspection DuplicatedCode */
    /**
     * @throws Exception
     */
    public static function shouldOpenLong(): bool
    {
        if (self::binance()->hasLongProfit() || self::$champion->can_trade === false) {
            info('Cannot open long: has profit or trading disabled');
            return false;
        }

        // Get the long position
        $longPosition = self::binance()->positions()->get(0);
        
        if (!$longPosition || $longPosition['positionAmt'] == 0) {
            info('No previous long position found, can open new position');
            return true;
        }

        // Check if long position notional plus grind is within 50% of champion's capital
        $longNotional = abs((float)$longPosition['notional']) + self::$champion->grind;
        if ($longNotional > (self::$champion->capital * 0.5)) {
            info('Long position notional plus grind exceeds 50% of champion capital', [
                'long_notional' => abs((float)$longPosition['notional']),
                'grind' => self::$champion->grind,
                'total_notional' => $longNotional,
                'champion_capital' => self::$champion->capital,
                'max_allowed' => self::$champion->capital * 0.5
            ]);
            return false;
        }

        // Get the latest filled long order
        $latestOrder = Order::query()
            ->where('status', '=', Order::STATUS_FILLED)
            ->where('position_side', '=', Order::POSITION_SIDE_LONG)
            ->where('side', '=', Order::SIDE_BUY)
            ->where('champion_id', '=', self::$champion->id)
            ->orderByDesc('update_time')
            ->first();

        if (!$latestOrder) {
            info('No previous long order found, can open new position');
            return true;
        }

        // Check if price has dropped 1% from latest filled order price
        $currentPrice = self::currentPrice();
        $orderPrice = (float)$latestOrder->avg_price;
        $priceDropPercentage = (($orderPrice - $currentPrice) / $orderPrice) * 100;

        info('Price check for long position:', [
            'order_price' => $orderPrice,
            'current_price' => $currentPrice,
            'price_drop_percentage' => $priceDropPercentage,
            'should_open' => $priceDropPercentage >= 2
        ]);

        return $priceDropPercentage >= 2;
    }

    private static function last2Hours(): int
    {
        return Carbon::now()->subHours(2)->timestamp * 1000;
    }

    /**
     * @throws Exception
     */
    public static function collectProfits(): void
    {
        self::importRecentTrades();

        $trades = Trade::query()
            ->where('realized_pnl', '>', 0)
            ->where('is_profit_collected', '=', false)
            ->get();

        if ($trades->count() > 0) {
            $trades->each(function ($trade) {
                ProfitManager::makeFromTrade($trade);
                $trade->update(['is_profit_collected' => true]);
            });
        }
    }

    /**
     * @throws Exception
     */
    public static function collectIncomes(): void
    {
        $incomes = self::binance()->income();

        foreach ($incomes as $income) {
            $income = [
                'tran_id' => $income['tranId'],
                'symbol' => $income['symbol'],
                'income_type' => $income['incomeType'],
                'income' => $income['income'],
                'asset' => $income['asset'],
                'time' => $income['time'],
                'trade_id' => $income['tradeId'] ?? null,
                'info' => $income['info'] ?? null,
                'created_at' => Carbon::createFromTimestampMs($income['time'])->toDateTimeString(),
            ];

            self::upsertIncome($income);
        }
    }

    /**
     * @throws Exception
     * @noinspection DuplicatedCode
     */
    public static function collectRecentIncomes(): void
    {
        tap(Income::query()
            ->orderByDesc('time')
            ->first(), function ($latestIncome) {
            $incomes = self::binance()->income($latestIncome->time);

            foreach ($incomes as $income) {
                $income = [
                    'tran_id' => $income['tranId'],
                    'symbol' => $income['symbol'],
                    'income_type' => $income['incomeType'],
                    'income' => $income['income'],
                    'asset' => $income['asset'],
                    'time' => $income['time'],
                    'trade_id' => $income['tradeId'] ?? null,
                    'info' => $income['info'] ?? null,
                ];

                self::upsertIncome($income);
            }
        });

    }

    private static function upsertIncome(array $income): void
    {
        Income::query()->upsert(
            $income,
            ['tran_id'],
            ['symbol', 'income_type', 'income', 'asset', 'time', 'trade_id', 'info', 'created_at']
        );
    }

    /**
     * @throws Exception
     */
    public static function useChampion(Champion $champion): void
    {
        self::$champion = $champion;

        self::updateBinanceInstance();
    }

    /**
     * @throws Exception
     */
    private static function updateBinanceInstance(): void
    {
        self::$binance = new Binance(self::$champion->symbol);
    }

    /**
     * @throws Exception
     */
    private static function priceGap(): float
    {
        return round(0.0004 * self::currentPrice(), 2);
    }

    public static function openLongWithSLTP(float $size, float $entry, ?float $sl = null, ?float $tp = null): void
    {
        $binanceOrder = self::binance()->openLongWithSLTP($size, $entry, $sl, $tp);
        self::upsertOrder($binanceOrder);
    }

    public static function hasOpenLongPosition(): bool
    {
        return self::binance()->hasLongPosition();
    }
}
