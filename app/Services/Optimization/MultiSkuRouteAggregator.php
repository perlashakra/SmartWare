<?php

namespace App\Services\Optimization;

class MultiSkuRouteAggregator
{
    protected VamSolverService $vamSolver;

    public function __construct(VamSolverService $vamSolver)
    {
        $this->vamSolver = $vamSolver;
    }

    /**
     * Processes selected items, runs VAM per SKU, and aggregates into a unified trip sequence.
     *
     * @param array $selectedItems List of SKUs with demand demands & warehouse inventory levels
     * @param array $costMatrix Distance/Cost matrix between all facilities
     * @return array Standardized payload formatted for front-end consumption
     */
    public function aggregate(array $selectedItems, array $costMatrix): array
    {
        $rawAllocations = [];
        $unfulfilledWarnings = [];

        foreach ($selectedItems as $item) {
            $skuId = $item['sku_id'];
            $rawInventory = $item['inventory'] ?? []; // e.g. ['WH-01' => 100]
            $demands = $item['demands'] ?? [];         // e.g. [['facility_id' => 'CLIENT-A', 'required_qty' => 50]]

            // 1. Enforce 30% Safety Stock Buffer on Inventory
            $usableSupply = [];
            foreach ($rawInventory as $whId => $totalQty) {
                $usableQty = (int) floor($totalQty * 0.70);
                if ($usableQty > 0) {
                    $usableSupply[$whId] = $usableQty;
                }
            }

            // 2. Prepare Demand Matrix & Check 50% Minimum Fulfillment Threshold
            $activeDemand = [];
            $totalUsableStock = array_sum($usableSupply);

            foreach ($demands as $demand) {
                $facilityId = $demand['facility_id'];
                $requiredQty = $demand['required_qty'];

                // If available network stock can't satisfy at least 50% of this demand line, warn and flag
                if ($totalUsableStock < ($requiredQty * 0.50)) {
                    $unfulfilledWarnings[] = [
                        'sku_id' => $skuId,
                        'facility_id' => $facilityId,
                        'reason' => "Insufficient stock to meet 50% minimum fulfillment threshold."
                    ];
                    continue; // Skip from automatic allocation
                }

                $activeDemand[$facilityId] = $requiredQty;
            }

            if (empty($usableSupply) || empty($activeDemand)) {
                continue;
            }

            // 3. Solve VAM for this SKU
            $skuAllocations = $this->vamSolver->solve($usableSupply, $activeDemand, $costMatrix);

            // 4. Group results by SKU
            foreach ($skuAllocations as $alloc) {
                $rawAllocations[] = [
                    'sku_id' => $skuId,
                    'from' => $alloc['from'],
                    'to' => $alloc['to'],
                    'quantity' => $alloc['quantity']
                ];
            }
        }

        // 5. Sequence and aggregate allocations into ordered stops
        $routeSequence = $this->buildRouteSequence($rawAllocations, $costMatrix);

        return [
            'status' => 'success',
            'summary' => [
                'total_stops' => count($routeSequence),
                'total_units_moved' => array_sum(array_column($rawAllocations, 'quantity')),
                'estimated_total_distance_km' => $this->calculateTotalDistance($routeSequence, $costMatrix)
            ],
            'route_sequence' => $routeSequence,
            'unfulfilled_warnings' => $unfulfilledWarnings
        ];
    }

    /**
     * Organizes raw allocations into logical sequence:
     * Origins (Pickups) -> Warehouse Transfers -> Client Destinations (Drop-offs)
     */
    private function buildRouteSequence(array $allocations, array $costMatrix): array
    {
        $facilityData = [];

        foreach ($allocations as $alloc) {
            $from = $alloc['from'];
            $to = $alloc['to'];
            $skuId = $alloc['sku_id'];
            $qty = $alloc['quantity'];

            // Outgoing (Pickups from warehouse)
            if (!isset($facilityData[$from])) {
                $facilityData[$from] = [
                    'facility_id' => $from,
                    'is_warehouse' => str_starts_with($from, 'WH'),
                    'picked' => [],
                    'dropped' => []
                ];
            }
            $facilityData[$from]['picked'][$skuId] = ($facilityData[$from]['picked'][$skuId] ?? 0) + $qty;

            // Incoming (Drop-offs to warehouse or client)
            if (!isset($facilityData[$to])) {
                $facilityData[$to] = [
                    'facility_id' => $to,
                    'is_warehouse' => str_starts_with($to, 'WH'),
                    'picked' => [],
                    'dropped' => []
                ];
            }
            $facilityData[$to]['dropped'][$skuId] = ($facilityData[$to]['dropped'][$skuId] ?? 0) + $qty;
        }

        // Sort sequence: Pure Origins first, then Intermediate Transfers, then Client Destinations
        uasort($facilityData, function ($a, $b) {
            if ($a['is_warehouse'] && !$b['is_warehouse']) return -1;
            if (!$a['is_warehouse'] && $b['is_warehouse']) return 1;
            return 0;
        });

        $sequence = [];
        $order = 1;

        foreach ($facilityData as $facilityId => $data) {
            $pickedFormatted = [];
            foreach ($data['picked'] as $sku => $q) {
                $pickedFormatted[] = ['sku_id' => $sku, 'quantity' => $q];
            }

            $droppedFormatted = [];
            foreach ($data['dropped'] as $sku => $q) {
                $droppedFormatted[] = ['sku_id' => $sku, 'quantity' => $q];
            }

            $type = 'client_destination';
            $action = 'dropoff';

            if ($data['is_warehouse']) {
                if (!empty($pickedFormatted) && empty($droppedFormatted)) {
                    $type = 'warehouse_origin';
                    $action = 'pickup';
                } elseif (!empty($pickedFormatted) && !empty($droppedFormatted)) {
                    $type = 'warehouse_transfer';
                    $action = 'dropoff_and_pickup';
                } else {
                    $type = 'warehouse_transfer';
                    $action = 'dropoff';
                }
            }

            $sequence[] = [
                'stop_order' => $order++,
                'facility_id' => $facilityId,
                'type' => $type,
                'action' => $action,
                'items_picked' => $pickedFormatted,
                'items_dropped' => $droppedFormatted
            ];
        }

        return $sequence;
    }

    private function calculateTotalDistance(array $sequence, array $costMatrix): float
    {
        $totalDistance = 0.0;
        $count = count($sequence);

        for ($i = 0; $i < $count - 1; $i++) {
            $from = $sequence[$i]['facility_id'];
            $to = $sequence[$i + 1]['facility_id'];
            $totalDistance += $costMatrix[$from][$to] ?? 0.0;
        }

        return round($totalDistance, 2);
    }
}
