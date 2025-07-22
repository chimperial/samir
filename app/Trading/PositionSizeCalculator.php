<?php

namespace App\Trading;

class PositionSizeCalculator
{
    /**
     * Calculate the position size based on risk parameters
     *
     * @param float $capital The available capital
     * @param float $entry The entry price
     * @param float|null $stopLoss The stop loss price
     * @param float|null $takeProfit The take profit price
     * @param float $riskMultiplier The risk multiplier (default 75)
     * @param float $riskPercentage The risk percentage (default 0.001)
     * @return float
     */
    public function calculateSize(
        float $capital,
        float $entry,
        ?float $stopLoss = null,
        ?float $takeProfit = null,
        float $riskMultiplier = 75,
        float $riskPercentage = 0.005
    ): float {
        info('=== PositionSizeCalculator Debug ===');
        info("Input parameters:");
        info("  Capital: $capital");
        info("  Entry: $entry");
        info("  Stop Loss: " . ($stopLoss ?? 'null'));
        info("  Take Profit: " . ($takeProfit ?? 'null'));
        info("  Risk Multiplier: $riskMultiplier");
        info("  Risk Percentage: $riskPercentage");

        // If no stop loss is provided or entry equals stop loss, use minimum size
        if (!$stopLoss || $entry == $stopLoss) {
            $minSize = TradingManager::minSize();
            $precision = TradingManager::getPrecision('quantity');
            info("Using minimum size (no stop loss or entry equals stop loss)");
            info("  Min Size: $minSize");
            info("  Precision: $precision");
            info("  Final Size: " . round($minSize, $precision));
            return round($minSize, $precision);
        }

        // Calculate risk percentage
        $riskPercent = abs($entry - $stopLoss) / $entry;
        info("Risk calculation:");
        info("  Risk Percent: " . number_format($riskPercent * 100, 4) . '%');
        
        // Calculate risk amount
        $riskAmount = $capital * $riskMultiplier * $riskPercentage;
        info("  Risk Amount: $riskAmount");
        
        // Calculate position notional
        $positionNotional = $riskAmount / $riskPercent;
        info("  Position Notional (before cap): $positionNotional");
        
        // Calculate maximum notional
        $maxNotional = $capital * $riskMultiplier;
        info("  Max Notional: $maxNotional");
        
        // Cap position notional to maximum
        if ($positionNotional > $maxNotional) {
            info("  Position Notional capped to Max Notional");
            $positionNotional = $maxNotional;
        }
        info("  Final Position Notional: $positionNotional");
        
        // Calculate size
        $size = $positionNotional / $entry;
        $precision = TradingManager::getPrecision('quantity');
        $finalSize = round($size, $precision);
        
        info("Size calculation:");
        info("  Raw Size: $size");
        info("  Precision: $precision");
        info("  Final Size: $finalSize");
        info('=== End PositionSizeCalculator Debug ===');
        
        return $finalSize;
    }

    /**
     * Calculate size with default parameters
     *
     * @param float $capital
     * @param float $entry
     * @param float|null $stopLoss
     * @param float|null $takeProfit
     * @return float
     */
    public function calculateSizeWithDefaults(
        float $capital,
        float $entry,
        ?float $stopLoss = null,
        ?float $takeProfit = null
    ): float {
        return $this->calculateSize($capital, $entry, $stopLoss, $takeProfit);
    }
} 