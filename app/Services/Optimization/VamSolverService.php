<?php

namespace App\Services\Optimization;

class VamSolverService
{
    /**
     * Solves the transportation problem for a single SKU using Vogel's Approximation Method (VAM).
     *
     * @param array $supply Map of warehouse IDs to usable stock e.g. ['WH-01' => 70]
     * @param array $demand Map of destination IDs to required qty e.g. ['CLIENT-A' => 50]
     * @param array $costMatrix 2D array of distances e.g. ['WH-01' => ['CLIENT-A' => 32.5]]
     * @return array Matrix of allocated shipments e.g. [['from' => 'WH-01', 'to' => 'CLIENT-A', 'qty' => 50]]
     */
    public function solve(array $supply, array $demand, array $costMatrix): array
    {
        $allocations = [];

        // Deep copy matrices to mutate during calculation
        $remainingSupply = $supply;
        $remainingDemand = $demand;

        // Balance check: Create dummy demand/supply if necessary for VAM execution
        $totalSupply = array_sum($remainingSupply);
        $totalDemand = array_sum($remainingDemand);

        if ($totalSupply > $totalDemand) {
            $remainingDemand['DUMMY_SINK'] = $totalSupply - $totalDemand;
            foreach ($remainingSupply as $whId => $val) {
                $costMatrix[$whId]['DUMMY_SINK'] = 0; // Free cost to dummy node
            }
        } elseif ($totalDemand > $totalSupply) {
            $remainingSupply['DUMMY_SOURCE'] = $totalDemand - $totalSupply;
            foreach ($remainingDemand as $destId => $val) {
                $costMatrix['DUMMY_SOURCE'][$destId] = 999999; // High penalty for unfulfilled demand
            }
        }

        // VAM Main Loop
        while ($this->hasRemainingStockAndDemand($remainingSupply, $remainingDemand)) {
            // 1. Calculate penalties for rows and columns
            $rowPenalties = $this->calculateRowPenalties($remainingSupply, $remainingDemand, $costMatrix);
            $colPenalties = $this->calculateColPenalties($remainingSupply, $remainingDemand, $costMatrix);

            // 2. Find maximum penalty
            $maxPenalty = -1;
            $selectedRow = null;
            $selectedCol = null;

            foreach ($rowPenalties as $row => $penalty) {
                if ($penalty > $maxPenalty) {
                    $maxPenalty = $penalty;
                    $selectedRow = $row;
                    $selectedCol = $this->getMinCostColInRow($row, $remainingDemand, $costMatrix);
                }
            }

            foreach ($colPenalties as $col => $penalty) {
                if ($penalty > $maxPenalty) {
                    $maxPenalty = $penalty;
                    $selectedCol = $col;
                    $selectedRow = $this->getMinCostRowInCol($col, $remainingSupply, $costMatrix);
                }
            }

            // Fallback if no penalties could be computed (e.g., 1 row/col remaining)
            if ($selectedRow === null || $selectedCol === null) {
                [$selectedRow, $selectedCol] = $this->getFirstValidPair($remainingSupply, $remainingDemand);
            }

            // 3. Allocate as much as possible to the lowest cost cell
            $allocatedQty = min($remainingSupply[$selectedRow], $remainingDemand[$selectedCol]);

            if ($allocatedQty > 0 && $selectedRow !== 'DUMMY_SOURCE' && $selectedCol !== 'DUMMY_SINK') {
                $allocations[] = [
                    'from' => $selectedRow,
                    'to' => $selectedCol,
                    'quantity' => $allocatedQty
                ];
            }

            // 4. Update remaining stock and demand
            $remainingSupply[$selectedRow] -= $allocatedQty;
            $remainingDemand[$selectedCol] -= $allocatedQty;

            if ($remainingSupply[$selectedRow] <= 0) {
                unset($remainingSupply[$selectedRow]);
            }
            if ($remainingDemand[$selectedCol] <= 0) {
                unset($remainingDemand[$selectedCol]);
            }
        }

        return $allocations;
    }

    private function hasRemainingStockAndDemand(array $supply, array $demand): bool
    {
        return array_sum($supply) > 0 && array_sum($demand) > 0;
    }

    private function calculateRowPenalties(array $supply, array $demand, array $costMatrix): array
    {
        $penalties = [];
        foreach (array_keys($supply) as $row) {
            $costs = [];
            foreach (array_keys($demand) as $col) {
                $costs[] = $costMatrix[$row][$col] ?? 0;
            }
            sort($costs);
            $penalties[$row] = count($costs) >= 2 ? ($costs[1] - $costs[0]) : ($costs[0] ?? 0);
        }
        return $penalties;
    }

    private function calculateColPenalties(array $supply, array $demand, array $costMatrix): array
    {
        $penalties = [];
        foreach (array_keys($demand) as $col) {
            $costs = [];
            foreach (array_keys($supply) as $row) {
                $costs[] = $costMatrix[$row][$col] ?? 0;
            }
            sort($costs);
            $penalties[$col] = count($costs) >= 2 ? ($costs[1] - $costs[0]) : ($costs[0] ?? 0);
        }
        return $penalties;
    }

    private function getMinCostColInRow(string $row, array $demand, array $costMatrix): string
    {
        $minCost = INF;
        $bestCol = key($demand);

        foreach (array_keys($demand) as $col) {
            $cost = $costMatrix[$row][$col] ?? INF;
            if ($cost < $minCost) {
                $minCost = $cost;
                $bestCol = $col;
            }
        }
        return $bestCol;
    }

    private function getMinCostRowInCol(string $col, array $supply, array $costMatrix): string
    {
        $minCost = INF;
        $bestRow = key($supply);

        foreach (array_keys($supply) as $row) {
            $cost = $costMatrix[$row][$col] ?? INF;
            if ($cost < $minCost) {
                $minCost = $cost;
                $bestRow = $row;
            }
        }
        return $bestRow;
    }

    private function getFirstValidPair(array $supply, array $demand): array
    {
        return [key($supply), key($demand)];
    }
}
