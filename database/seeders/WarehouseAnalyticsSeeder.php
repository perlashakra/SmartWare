<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Facility;
use App\Models\InBook;
use App\Models\InBookProduct;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Section;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class WarehouseAnalyticsSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            /*
            |--------------------------------------------------------------------------
            | USERS
            |--------------------------------------------------------------------------
            */

            $warehouseAdmin1 = User::create([
                'first_name' => 'Warehouse',
                'last_name' => 'Admin One',
                'email' => 'warehouse1@test.com',
                'phone_number' => '0600000001',
                'password' => Hash::make('password'),
                'role' => 'warehouse_admin',
                'account_status' => 'approved',
            ]);

            $warehouseAdmin2 = User::create([
                'first_name' => 'Warehouse',
                'last_name' => 'Admin Two',
                'email' => 'warehouse2@test.com',
                'phone_number' => '0600000002',
                'password' => Hash::make('password'),
                'role' => 'warehouse_admin',
                'account_status' => 'approved',
            ]);

            $businessOwner1 = User::create([
                'first_name' => 'bb1',
                'last_name' => 'bb1',
                'email' => 'bb1@bb1.com',
                'phone_number' => '0600000003',
                'password' => Hash::make('password'),
                'role' => 'client',
                'account_status' => 'approved',
            ]);

            $businessOwner2 = User::create([
                'first_name' => 'bb2',
                'last_name' => 'bb2',
                'email' => 'bb2@bb2.com',
                'phone_number' => '0600000004',
                'password' => Hash::make('password'),
                'role' => 'client',
                'account_status' => 'approved',
            ]);


            $worker = User::create([
                'manager_id'=>$warehouseAdmin1->id,
                'first_name' => 'ww',
                'last_name' => 'ww',
                'email' => 'ww@ww.com',
                'phone_number' => '0600000005',
                'password' => Hash::make('password'),
                'role' => 'client',
                'account_status' => 'approved',
            ]);

            /*
            |--------------------------------------------------------------------------
            | ADDRESS
            |--------------------------------------------------------------------------
            |
            | Replace these with Address::factory()->create() if you have
            | an Address factory.
            |
            */

            $address1 = \App\Models\Address::create([
                'name'          =>'first_address',
                'latitude'      =>'first_address',
                'longitude'     =>'first_address', 
            ]);
            $address2 = \App\Models\Address::create([
                'name'          =>'second_address',
                'latitude'      =>'second_address',
                'longitude'     =>'second_address', 
            ]);

            $address3 = \App\Models\Address::create([
                'name'          =>'address_3',
                'latitude'      =>'address_3',
                'longitude'     =>'address_3', 
            ]);

            $address4 = \App\Models\Address::create([
                'name'          =>'address_4',
                'latitude'      =>'address_4',
                'longitude'     =>'address_4', 
            ]);

            /*
            |--------------------------------------------------------------------------
            | COMPANIES
            |--------------------------------------------------------------------------
            |
            | Sections require company_id.
            |
            */



            /*
            |--------------------------------------------------------------------------
            | WAREHOUSE 1
            |--------------------------------------------------------------------------
            */

            $warehouse1 = Facility::create([
                'address_id' => $address1->id,
                'user_id' => $warehouseAdmin1->id,
                'facility_name_en' => 'Main Warehouse',
                'facility_name_ar' => 'المستودع الرئيسي',
                'facility_type' => 'warehouse',
                'facility_status' => 'approved',
                'business_type' => 'warehouse',
            ]);


            /*
            |--------------------------------------------------------------------------
            | WAREHOUSE 2
            |--------------------------------------------------------------------------
            */

            $warehouse2 = Facility::create([
                'address_id' => $address2->id,
                'user_id' => $warehouseAdmin2->id,
                'facility_name_en' => 'Second Warehouse',
                'facility_name_ar' => 'المستودع الثاني',
                'facility_type' => 'warehouse',
                'facility_status' => 'approved',
                'business_type' => 'warehouse',
            ]);

            $store1 = Facility::create([
                'address_id' => $address3->id,
                'user_id' => $businessOwner1->id,
                'facility_name_en' => 'first store',
                'facility_name_ar' => 'المحل الأول',
                'facility_type' => 'business',
                'facility_status' => 'approved',
                'business_type' => 'restaurant',
            ]);

            $store2 = Facility::create([
                'address_id' => $address4->id,
                'user_id' => $businessOwner2->id,
                'facility_name_en' => 'second store',
                'facility_name_ar' => 'المحل الأول',
                'facility_type' => 'business',
                'facility_status' => 'approved',
                'business_type' => 'clothing_store',
            ]);

            /*
            |--------------------------------------------------------------------------
            | SECTIONS - WAREHOUSE 1
            |--------------------------------------------------------------------------
            */

            $warehouse1SectionA = Section::create([
                'warehouse_id' => $warehouse1->id,
                'name' => 'Section A',
                'capacity' => '1000',
            ]);

            $warehouse1SectionB = Section::create([
                'warehouse_id' => $warehouse1->id,
                'name' => 'Section B',
                'capacity' => '1000',
            ]);


            /*
            |--------------------------------------------------------------------------
            | SECTION - WAREHOUSE 2
            |--------------------------------------------------------------------------
            */

            $warehouse2SectionA = Section::create([
                'warehouse_id' => $warehouse2->id,
                'name' => 'Section A',
                'capacity' => '2000',
            ]);


            /*
            |--------------------------------------------------------------------------
            | CATEGORIES
            |--------------------------------------------------------------------------
            */

            $beverages = Category::create([
                'name' => 'Beverages',
            ]);

            $food = Category::create([
                'name' => 'Food',
            ]);

            $cleaning = Category::create([
                'name' => 'Cleaning',
            ]);


            /*
            |--------------------------------------------------------------------------
            | PRODUCTS
            |--------------------------------------------------------------------------
            */

            $water = Product::create([
                'sku' => 'WATER-001',
                'name_en' => 'Mineral Water',
                'name_ar' => 'مياه معدنية',
                'unit' => 'bottle',
                'description_en' => 'Mineral water',
                'description_ar' => 'مياه معدنية',
            ]);

            $cola = Product::create([
                'sku' => 'COLA-001',
                'name_en' => 'Cola',
                'name_ar' => 'كولا',
                'unit' => 'bottle',
                'description_en' => 'Cola drink',
                'description_ar' => 'مشروب كولا',
            ]);

            $juice = Product::create([
                'sku' => 'JUICE-001',
                'name_en' => 'Orange Juice',
                'name_ar' => 'عصير برتقال',
                'unit' => 'bottle',
                'description_en' => 'Orange juice',
                'description_ar' => 'عصير برتقال',
            ]);

            $rice = Product::create([
                'sku' => 'RICE-001',
                'name_en' => 'Rice',
                'name_ar' => 'أرز',
                'unit' => 'bag',
                'description_en' => 'Rice',
                'description_ar' => 'أرز',
            ]);

            $soap = Product::create([
                'sku' => 'SOAP-001',
                'name_en' => 'Liquid Soap',
                'name_ar' => 'صابون سائل',
                'unit' => 'bottle',
                'description_en' => 'Liquid soap',
                'description_ar' => 'صابون سائل',
            ]);


            /*
            |--------------------------------------------------------------------------
            | CATEGORIES
            |--------------------------------------------------------------------------
            */

            $water->categories()->attach($beverages->id);
            $cola->categories()->attach($beverages->id);
            $juice->categories()->attach($beverages->id);
            $rice->categories()->attach($food->id);
            $soap->categories()->attach($cleaning->id);


            /*
            |--------------------------------------------------------------------------
            | WAREHOUSE 1 INVENTORY
            |--------------------------------------------------------------------------
            */

            Inventory::create([
                'section_id' => $warehouse1SectionA->id,
                'product_id' => $water->id,
                'quantity' => 50,
                'unit_price' => 1.50,
            ]);

            Inventory::create([
                'section_id' => $warehouse1SectionB->id,
                'product_id' => $water->id,
                'quantity' => 30,
                'unit_price' => 1.50,
            ]);

            Inventory::create([
                'section_id' => $warehouse1SectionA->id,
                'product_id' => $cola->id,
                'quantity' => 5,
                'unit_price' => 2.00,
            ]);

            Inventory::create([
                'section_id' => $warehouse1SectionB->id,
                'product_id' => $juice->id,
                'quantity' => 100,
                'unit_price' => 3.00,
            ]);

            Inventory::create([
                'section_id' => $warehouse1SectionA->id,
                'product_id' => $rice->id,
                'quantity' => 50,
                'unit_price' => 10.00,
            ]);

            Inventory::create([
                'section_id' => $warehouse1SectionB->id,
                'product_id' => $soap->id,
                'quantity' => 2,
                'unit_price' => 5.00,
            ]);


            /*
            |--------------------------------------------------------------------------
            | WAREHOUSE 2 INVENTORY
            |--------------------------------------------------------------------------
            */

            Inventory::create([
                'section_id' => $warehouse2SectionA->id,
                'product_id' => $water->id,
                'quantity' => 500,
                'unit_price' => 1.50,
            ]);

            Inventory::create([
                'section_id' => $warehouse2SectionA->id,
                'product_id' => $cola->id,
                'quantity' => 300,
                'unit_price' => 2.00,
            ]);

            Inventory::create([
                'section_id' => $warehouse2SectionA->id,
                'product_id' => $rice->id,
                'quantity' => 200,
                'unit_price' => 10.00,
            ]);


            /*
            |--------------------------------------------------------------------------
            | INBOOK - WAREHOUSE 1
            |--------------------------------------------------------------------------
            */

            $inbook1 = InBook::create([
                'user_id' => $worker->id,
                'storage_date' => '2026-08-01',
            ]);

            InBookProduct::create([
                'inbook_id' => $inbook1->id,
                'product_id' => $water->id,
                'quantity' => 100,
                'section_id' => $warehouse1SectionA->id,
            ]);

            InBookProduct::create([
                'inbook_id' => $inbook1->id,
                'product_id' => $cola->id,
                'quantity' => 50,
                'section_id' => $warehouse1SectionA->id,
            ]);


            $inbook2 = InBook::create([
                'user_id' => $worker->id,
                'storage_date' => '2026-08-05',
            ]);

            InBookProduct::create([
                'inbook_id' => $inbook2->id,
                'product_id' => $juice->id,
                'quantity' => 150,
                'section_id' => $warehouse1SectionB->id,
            ]);

            InBookProduct::create([
                'inbook_id' => $inbook2->id,
                'product_id' => $soap->id,
                'quantity' => 20,
                'section_id' => $warehouse1SectionB->id,
            ]);


            $inbook3 = InBook::create([
                'user_id' => $worker->id,
                'storage_date' => '2026-08-10',
            ]);

            InBookProduct::create([
                'inbook_id' => $inbook3->id,
                'product_id' => $water->id,
                'quantity' => 200,
                'section_id' => $warehouse1SectionA->id,
            ]);


            /*
            |--------------------------------------------------------------------------
            | INBOOK - WAREHOUSE 2
            |--------------------------------------------------------------------------
            */

            $inbook4 = InBook::create([
                'user_id' => $warehouseAdmin2->id,
                'storage_date' => '2026-08-05',
            ]);

            InBookProduct::create([
                'inbook_id' => $inbook4->id,
                'product_id' => $water->id,
                'quantity' => 1000,
                'section_id' => $warehouse2SectionA->id,
            ]);


            /*
            |--------------------------------------------------------------------------
            | ORDERS - WAREHOUSE 1
            |--------------------------------------------------------------------------
            */

            $order1 = Order::create([
                'src_facility_id' => $warehouse1->id,
                'dest_facility_id' => null,
                'user_id' => $warehouseAdmin1->id,
                'order_type' => 'business_purchase',
                'expected_price' => 500,
                'status' => 'delivered',
                'order_date' => '2026-08-02',
                'notes' => 'Warehouse 1 outgoing order',
            ]);

            OrderItem::create([
                'order_id' => $order1->id,
                'product_id' => $water->id,
                'quantity' => 30,
                'unit_price'=>30.3
            ]);

            OrderItem::create([
                'order_id' => $order1->id,
                'product_id' => $cola->id,
                'quantity' => 10,
                'unit_price'=>35
            ]);


            $order2 = Order::create([
                'src_facility_id' => $warehouse1->id,
                'dest_facility_id' => null,
                'user_id' => $warehouseAdmin1->id,
                'order_type' => 'business_purchase',
                'expected_price' => 300,
                'status' => 'shipping',
                'order_date' => '2026-08-06',
                'notes' => 'Warehouse 1 outgoing order',
            ]);

            OrderItem::create([
                'order_id' => $order2->id,
                'product_id' => $water->id,
                'quantity' => 50,
                'unit_price'=>25.3
            ]);

            OrderItem::create([
                'order_id' => $order2->id,
                'product_id' => $juice->id,
                'quantity' => 20,
                'unit_price'=>36
            ]);


            $order3 = Order::create([
                'src_facility_id' => $warehouse1->id,
                'dest_facility_id' => null,
                'user_id' => $warehouseAdmin1->id,
                'order_type' => 'business_purchase',
                'expected_price' => 200,
                'status' => 'delivered',
                'order_date' => '2026-08-10',
                'notes' => 'Warehouse 1 outgoing order',
            ]);

            OrderItem::create([
                'order_id' => $order3->id,
                'product_id' => $water->id,
                'quantity' => 80,
                'unit_price'=>74
            ]);

            OrderItem::create([
                'order_id' => $order3->id,
                'product_id' => $rice->id,
                'quantity' => 5,
                'unit_price'=>19
            ]);


            /*
            |--------------------------------------------------------------------------
            | CANCELLED ORDER
            |--------------------------------------------------------------------------
            |
            | Should NOT appear in stock movement.
            |
            */

            $cancelledOrder = Order::create([
                'src_facility_id' => $warehouse1->id,
                'dest_facility_id' => null,
                'user_id' => $warehouseAdmin1->id,
                'order_type' => 'business_purchase',
                'expected_price' => 1000,
                'status' => 'cancelled',
                'order_date' => '2026-08-11',
                'notes' => 'Cancelled order',
            ]);

            OrderItem::create([
                'order_id' => $cancelledOrder->id,
                'product_id' => $water->id,
                'quantity' => 999,
                'unit_price'=>57
            ]);


            /*
            |--------------------------------------------------------------------------
            | ORDERS - WAREHOUSE 2
            |--------------------------------------------------------------------------
            |
            | Should NOT appear in warehouse 1 analytics.
            |
            */

            $warehouse2Order = Order::create([
                'src_facility_id' => $warehouse2->id,
                'dest_facility_id' => null,
                'user_id' => $warehouseAdmin2->id,
                'order_type' => 'business_purchase',
                'expected_price' => 5000,
                'status' => 'delivered',
                'order_date' => '2026-08-05',
                'notes' => 'Warehouse 2 outgoing order',
            ]);

            OrderItem::create([
                'order_id' => $warehouse2Order->id,
                'product_id' => $water->id,
                'quantity' => 1000,
                'unit_price'=>23
            ]);

            OrderItem::create([
                'order_id' => $warehouse2Order->id,
                'product_id' => $cola->id,
                'quantity' => 500,
                'unit_price'=>12
            ]);
        });
    }
}