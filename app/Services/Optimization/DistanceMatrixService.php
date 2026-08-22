<?php

namespace App\Services\Optimization;

use App\Models\Order;
use App\Models\Facility;

class DistanceMatrixService
{
    /**
     * Build an N x N straight-line (Haversine) distance matrix (in KM)
     * for all facilities associated with the provided orders.
     */
    public function generateForOrders(array $orderIds): array
    {
        $orders = Order::whereIn('id', $orderIds)->get();
        $coordinates = [];

        foreach ($orders as $order) {
            // 1. Source Facility (Warehouse)
            if ($order->src_facility_id) {
                $whKey = "WH-{$order->src_facility_id}";
                if (!isset($coordinates[$whKey])) {
                    $facility = Facility::with('address')->find($order->src_facility_id);
                    if ($facility && $facility->address) {
                        $coordinates[$whKey] = [
                            'lat' => (float) $facility->address->latitude,
                            'lng' => (float) $facility->address->longitude,
                        ];
                    }
                }
            }

            // 2. Destination Facility (Warehouse Restock OR Client Destination)
            if ($order->order_type === 'warehouse_restock' && $order->dest_facility_id) {
                $whKey = "WH-{$order->dest_facility_id}";
                if (!isset($coordinates[$whKey])) {
                    $facility = Facility::with('address')->find($order->dest_facility_id);
                    if ($facility && $facility->address) {
                        $coordinates[$whKey] = [
                            'lat' => (float) $facility->address->latitude,
                            'lng' => (float) $facility->address->longitude,
                        ];
                    }
                }
            } elseif ($order->user_id) {
                $clientKey = "CLIENT-{$order->user_id}";
                if (!isset($coordinates[$clientKey])) {
                    $facilityId = $order->dest_facility_id;
                    $facility = $facilityId ? Facility::with('address')->find($facilityId) : null;

                    if ($facility && $facility->address) {
                        $coordinates[$clientKey] = [
                            'lat' => (float) $facility->address->latitude,
                            'lng' => (float) $facility->address->longitude,
                        ];
                    }
                }
            }
        }

        return $this->buildMatrix($coordinates);
    }

    private function buildMatrix(array $coordinates): array
    {
        $matrix = [];
        $keys = array_keys($coordinates);

        foreach ($keys as $fromKey) {
            $matrix[$fromKey] = [];
            foreach ($keys as $toKey) {
                if ($fromKey === $toKey) {
                    $matrix[$fromKey][$toKey] = 0.0;
                    continue;
                }

                $distance = $this->haversine(
                    $coordinates[$fromKey]['lat'],
                    $coordinates[$fromKey]['lng'],
                    $coordinates[$toKey]['lat'],
                    $coordinates[$toKey]['lng']
                );

                $matrix[$fromKey][$toKey] = round($distance, 2);
            }
        }

        return $matrix;
    }

    private function haversine(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
