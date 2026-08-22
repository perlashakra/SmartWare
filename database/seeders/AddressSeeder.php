<?php

namespace Database\Seeders;

use App\Models\Address;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
{
    /**
     * A spread of real, DISTINCT coordinates across Syria.
     *
     * The original seeder gave every address the same lat/lng, which makes
     * every Haversine distance in DistanceMatrixService collapse to 0 and
     * makes the VAM route optimizer (VamSolverService/MultiSkuRouteAggregator)
     * meaningless to test. Distinct coordinates are required for that flow
     * to produce a real "generate plan" result.
     */
    public function run(): void
    {
        $addresses = [
            ['address' => 'Mezzeh, Damascus, Syria', 'latitude' => '33.5024', 'longitude' => '36.2650'],
            ['address' => 'Dummar, Damascus, Syria', 'latitude' => '33.5560', 'longitude' => '36.2582'],
            ['address' => 'Sahnaya, Rif Dimashq, Syria', 'latitude' => '33.4200', 'longitude' => '36.2118'],
            ['address' => 'Adra, Rif Dimashq, Syria', 'latitude' => '33.5772', 'longitude' => '36.5031'],
            ['address' => 'Old City, Damascus, Syria', 'latitude' => '33.5117', 'longitude' => '36.3050'],
            ['address' => 'Kafr Sousa, Damascus, Syria', 'latitude' => '33.4886', 'longitude' => '36.2792'],
            ['address' => 'Jaramana, Rif Dimashq, Syria', 'latitude' => '33.4838', 'longitude' => '36.3378'],
            ['address' => 'Douma, Rif Dimashq, Syria', 'latitude' => '33.5719', 'longitude' => '36.4028'],
            ['address' => 'Qudsaya, Rif Dimashq, Syria', 'latitude' => '33.5289', 'longitude' => '36.2153'],
            ['address' => 'Harasta, Rif Dimashq, Syria', 'latitude' => '33.5636', 'longitude' => '36.3628'],
            ['address' => 'Al-Zahraa, Aleppo, Syria', 'latitude' => '36.2021', 'longitude' => '37.1343'],
            ['address' => 'Sheikh Najjar Industrial City, Aleppo, Syria', 'latitude' => '36.1425', 'longitude' => '37.1656'],
            ['address' => 'City Center, Homs, Syria', 'latitude' => '34.7324', 'longitude' => '36.7137'],
            ['address' => 'Al-Waer, Homs, Syria', 'latitude' => '34.7473', 'longitude' => '36.6636'],
            ['address' => 'Port Area, Lattakia, Syria', 'latitude' => '35.5317', 'longitude' => '35.7913'],
            ['address' => 'City Center, Lattakia, Syria', 'latitude' => '35.5211', 'longitude' => '35.7822'],
            ['address' => 'City Center, Tartus, Syria', 'latitude' => '34.8892', 'longitude' => '35.8866'],
            ['address' => 'City Center, Hama, Syria', 'latitude' => '35.1318', 'longitude' => '36.7500'],
            ['address' => 'City Center, Daraa, Syria', 'latitude' => '32.6189', 'longitude' => '36.1021'],
            ['address' => 'City Center, Idlib, Syria', 'latitude' => '35.9306', 'longitude' => '36.6339'],
            ['address' => 'City Center, Deir ez-Zor, Syria', 'latitude' => '35.3359', 'longitude' => '40.1408'],
            ['address' => 'City Center, Al-Hasakah, Syria', 'latitude' => '36.5024', 'longitude' => '40.7477'],
            ['address' => 'City Center, Raqqa, Syria', 'latitude' => '35.9500', 'longitude' => '39.0089'],
            ['address' => 'City Center, As-Suwayda, Syria', 'latitude' => '32.7094', 'longitude' => '36.5697'],
            ['address' => 'Quneitra Town, Quneitra, Syria', 'latitude' => '33.1264', 'longitude' => '35.8244'],
            ['address' => 'Zabadani, Rif Dimashq, Syria', 'latitude' => '33.7256', 'longitude' => '36.1000'],
            ['address' => 'Yabroud, Rif Dimashq, Syria', 'latitude' => '33.9678', 'longitude' => '36.6578'],
            ['address' => 'An-Nabek, Rif Dimashq, Syria', 'latitude' => '33.9942', 'longitude' => '36.7256'],
            ['address' => 'Qatana, Rif Dimashq, Syria', 'latitude' => '33.4394', 'longitude' => '36.0803'],
            ['address' => 'Jableh, Lattakia, Syria', 'latitude' => '35.3616', 'longitude' => '35.9256'],
        ];

        foreach ($addresses as $address) {
            Address::create($address);
        }
    }
}
