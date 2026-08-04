<?php

namespace App\Services;

class BillCalculatorService
{
    /**
     * Mirror of the original `includes/functions.php::calculate_bill()`.
     *
     * Tariff table (ETB per m³) — based on customer type.
     * Meter rent cost (monthly) — based on meter size.
     * Service cost: flat 2.00.
     * Water fund: 5% of water bill cost.
     * Deposit: 2% of water bill cost.
     */
    public function calculate(
        float $prevReading,
        float $currentReading,
        string $meterSize,
        string $customerType
    ): array {
        $consumption = max(0, $currentReading - $prevReading);

        $tariff = 4.50; // default — Dhunfaa (private household)
        if ($customerType === 'Daldaltoota fi Industry') {
            $tariff = 8.00;
        } elseif ($customerType === 'Waajjira Motummaa') {
            $tariff = 6.00;
        } elseif ($customerType === 'Waajjira Miti-Motummaa') {
            $tariff = 6.00;
        } elseif ($customerType === 'Boonoo') {
            $tariff = 5.00;
        }

        $meterRent = 5.00; // 1/2"
        if ($meterSize === '3/4"') {
            $meterRent = 8.00;
        } elseif ($meterSize === '1"') {
            $meterRent = 12.00;
        } elseif ($meterSize === '1 and 1/2"') {
            $meterRent = 20.00;
        } elseif ($meterSize === '2"') {
            $meterRent = 35.00;
        }

        $serviceCost     = 2.00;
        $consumptionCost = round($consumption * $tariff, 2);
        $penaltyCost     = 0.00;
        $communityCost   = 1.00;
        $waterFund      = round($consumptionCost * 0.05, 2); // 5% water fund
        $deposit         = round($consumptionCost * 0.02, 2); // 2% deposit interest

        $total = round(
            $meterRent + $serviceCost + $consumptionCost
            + $penaltyCost + $communityCost + $waterFund + $deposit,
            2
        );

        return [
            'meter_price'        => $meterRent,
            'service_price'      => $serviceCost,
            'consumption'        => $consumption,
            'consumption_cost'   => $consumptionCost,
            'penalty_cost'       => $penaltyCost,
            'community_cost'     => $communityCost,
            'state_price'        => $waterFund,        // water fund
            'deposited_cost'     => $deposit,
            'total_monthly_cost' => $total,
        ];
    }
}
