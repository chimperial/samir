<?php

namespace App\Binance;

use Binance\API;
use Exception;
use Illuminate\Support\Collection;

class FuturesClient extends API
{
    protected ?string $symbol = null;

    public function useSymbol($symbol): void
    {
        $this->symbol = $symbol;
    }

    public function account(): array
    {
        return $this->httpRequest(
            'fapi/v2/account',
            'GET',
            [
                'fapi' => true
            ],
            true
        );
    }

    /**
     * @throws Exception
     */
    public function balance(): array
    {
        return $this->httpRequest(
            'fapi/v2/balance',
            'GET',
            [
                'fapi' => true
            ],
            true
        );
    }

    /**
     * @throws Exception
     */
    public function userTrades($startTime = null): array
    {
        // info('Making userTrades API call:', [
        //     'symbol' => $this->symbol,
        //     'startTime' => $startTime
        // ]);
        
        try {
            return $this->httpRequest(
                'fapi/v1/userTrades',
                'GET',
                [
                    'fapi' => true,
                    'symbol' => $this->symbol,
                    'startTime' => $startTime
                ],
                true
            );
        } catch (Exception $e) {
            info('userTrades API error:', [
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
            throw $e;
        }
    }

    /**
     * @throws Exception
     */
    public function positions(): Collection
    {
        return collect($this->httpRequest(
            'fapi/v2/positionRisk',
            'GET',
            [
                'fapi' => true,
                'symbol' => $this->symbol
            ],
            true
        ));
    }

    /**
     * @throws Exception
     */
    public function openLong(float $size, float $entry): array
    {
        return $this->httpRequest(
            'fapi/v1/order',
            'POST',
            [
                'fapi' => true,
                'symbol' => $this->symbol,
                'side' => 'BUY',
                'quantity' => $size,
                'type' => 'MARKET',
                'positionSide' => 'LONG'
            ],
            true
        );
    }

    public function orders(string $symbol = null, $limit = 500, $fromOrderId = 0, $params = []): array
    {
        return $this->httpRequest(
            'fapi/v1/openOrders',
            'GET',
            [
                'fapi' => true,
                'symbol' => $symbol ?: $this->symbol
            ],
            true
        );
    }

    /**
     * @throws Exception
     */
    public function allOrders(string $symbol = null, ?string $updateTime = null): array
    {
        $params = [
            'fapi' => true,
            'symbol' => $symbol ?: $this->symbol,
            'limit' => 1000
        ];

        if ($updateTime) {
            $params['startTime'] = (int)$updateTime;
        }

        return $this->httpRequest(
            'fapi/v1/allOrders',
            'GET',
            $params,
            true
        );
    }

    /**
     * @throws Exception
     */
    public function cancelAllOrders(): array
    {
        return $this->httpRequest(
            'fapi/v1/allOpenOrders',
            'DELETE',
            [
                'fapi' => true,
                'symbol' => $this->symbol
            ],
            true
        );
    }

    /**
     * @throws Exception
     */
    public function getOrder($orderId): array
    {
        return $this->httpRequest(
            'fapi/v1/order',
            'GET',
            [
                'fapi' => true,
                'symbol' => $this->symbol,
                'orderId' => $orderId
            ],
            true
        );
    }

    /**
     * @throws Exception
     */
    public function closeLong(float $size, float $entry): array
    {
        return $this->httpRequest(
            'fapi/v1/order',
            'POST',
            [
                'fapi' => true,
                'symbol' => $this->symbol,
                'side' => 'SELL',
                'quantity' => $size,
                'type' => 'MARKET',
                'positionSide' => 'LONG'
            ],
            true
        );
    }

    /**
     * @throws Exception
     */
    public function openShort(float $size, float $entry): array
    {
        return $this->httpRequest(
            'fapi/v1/order',
            'POST',
            [
                'fapi' => true,
                'symbol' => $this->symbol,
                'side' => 'SELL',
                'quantity' => $size,
                'type' => 'MARKET',
                'positionSide' => 'SHORT'
            ],
            true
        );
    }

    /**
     * @throws Exception
     */
    public function closeShort(float $size, float $entry): array
    {
        return $this->httpRequest(
            'fapi/v1/order',
            'POST',
            [
                'fapi' => true,
                'symbol' => $this->symbol,
                'side' => 'BUY',
                'quantity' => $size,
                'type' => 'MARKET',
                'positionSide' => 'SHORT'
            ],
            true
        );
    }

    /**
     * @throws Exception
     */
    public function income($time = null): array
    {
        return $this->httpRequest(
            'fapi/v1/income',
            'GET',
            [
                'fapi' => true,
                'symbol' => $this->symbol,
                'incomeType' => 'FUNDING_FEE',
                'startTime' => $time
            ],
            true
        );
    }

    public function openLongWithSLTP(float $size, float $entry, ?float $sl = null, ?float $tp = null): array
    {
        info('=== openLongWithSLTP Debug ===');
        info('Input parameters:', [
            'symbol' => $this->symbol,
            'size' => $size,
            'entry' => $entry,
            'stop_loss' => $sl,
            'take_profit' => $tp
        ]);

        // Entry order (LIMIT)
        // See: https://developers.binance.com/docs/derivatives/usds-margined-futures/trade/rest-api#http-request
        $entryOrder = $this->httpRequest(
            'fapi/v1/order',
            'POST',
            [
                'fapi' => true,
                'symbol' => $this->symbol,
                'side' => 'BUY',
                'quantity' => $size,
                'type' => 'LIMIT', // LIMIT order per Binance API
                'price' => $entry,
                'timeInForce' => 'GTC',
                'positionSide' => 'LONG',
            ],
            true
        );

        info('Entry order placed:', [
            'order_id' => $entryOrder['orderId'] ?? 'unknown',
            'status' => $entryOrder['status'] ?? 'unknown',
            'symbol' => $entryOrder['symbol'] ?? 'unknown',
            'side' => $entryOrder['side'] ?? 'unknown',
            'quantity' => $entryOrder['origQty'] ?? 'unknown',
            'price' => $entryOrder['price'] ?? 'unknown',
            'type' => $entryOrder['type'] ?? 'unknown'
        ]);

        // Stop Loss (STOP_MARKET, close all)
        if ($sl) {
            info('Placing stop loss order:', [
                'stop_price' => $sl,
                'type' => 'STOP_MARKET',
                'side' => 'SELL',
                'position_side' => 'LONG'
            ]);

            $slOrder = $this->httpRequest(
                'fapi/v1/order',
                'POST',
                [
                    'fapi' => true,
                    'symbol' => $this->symbol,
                    'side' => 'SELL',
                    'type' => 'STOP_MARKET', // STOP_MARKET order per Binance API
                    'stopPrice' => $sl,
                    'positionSide' => 'LONG',
                    'closePosition' => 'true', // Use string 'true' per Binance API
                    'timeInForce' => 'GTE_GTC',
                ],
                true
            );

            info('Stop loss order placed:', [
                'order_id' => $slOrder['orderId'] ?? 'unknown',
                'status' => $slOrder['status'] ?? 'unknown',
                'stop_price' => $slOrder['stopPrice'] ?? 'unknown',
                'type' => $slOrder['type'] ?? 'unknown'
            ]);
        } else {
            info('No stop loss order placed (sl parameter is null)');
        }

        // Take Profit (TAKE_PROFIT_MARKET, close all)
        if ($tp) {
            info('Placing take profit order:', [
                'stop_price' => $tp,
                'type' => 'TAKE_PROFIT_MARKET',
                'side' => 'SELL',
                'position_side' => 'LONG'
            ]);

            $tpOrder = $this->httpRequest(
                'fapi/v1/order',
                'POST',
                [
                    'fapi' => true,
                    'symbol' => $this->symbol,
                    'side' => 'SELL',
                    'type' => 'TAKE_PROFIT_MARKET', // TAKE_PROFIT_MARKET order per Binance API
                    'stopPrice' => $tp,
                    'positionSide' => 'LONG',
                    'closePosition' => 'true', // Use string 'true' per Binance API
                    'timeInForce' => 'GTE_GTC',
                ],
                true
            );

            info('Take profit order placed:', [
                'order_id' => $tpOrder['orderId'] ?? 'unknown',
                'status' => $tpOrder['status'] ?? 'unknown',
                'stop_price' => $tpOrder['stopPrice'] ?? 'unknown',
                'type' => $tpOrder['type'] ?? 'unknown'
            ]);
        } else {
            info('No take profit order placed (tp parameter is null)');
        }

        info('=== End openLongWithSLTP Debug ===');
        return $entryOrder;
    }
}