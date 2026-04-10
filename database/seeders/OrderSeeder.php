<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get a staff user's college
        $staffUser = User::where('role', 'staff')->first();
        $collegeCode = $staffUser->college ?? 'ceit';

        // Get some student users
        $students = User::where('role', 'student')->limit(5)->get();

        if ($students->isEmpty()) {
            $this->command->info('No student users found. Please create students first.');
            return;
        }

        $menuItems = [
            ['name' => 'Chicken Adobo Meal', 'price' => 65.00],
            ['name' => 'Pork Fried Rice', 'price' => 55.00],
            ['name' => 'Iced Coffee', 'price' => 35.00],
            ['name' => 'Lumpia', 'price' => 30.00],
            ['name' => 'Halo-Halo', 'price' => 45.00],
        ];

        $statuses = ['pending', 'preparing', 'ready', 'completed'];

        foreach ($students as $index => $student) {
            // Create 2-3 orders per student
            for ($i = 0; $i < rand(2, 3); $i++) {
                $order = Order::create([
                    'user_id' => $student->id,
                    'order_number' => 'ORD-' . date('Ymd') . '-' . str_pad($index * 10 + $i + 1, 3, '0', STR_PAD_LEFT),
                    'status' => $statuses[array_rand($statuses)],
                    'canteen_id' => $collegeCode,
                    'total' => 0,
                ]);

                // Add random items to order
                $itemCount = rand(1, 3);
                $total = 0;
                $selectedItems = array_rand($menuItems, $itemCount);
                if (!is_array($selectedItems)) {
                    $selectedItems = [$selectedItems];
                }

                foreach ($selectedItems as $itemIndex) {
                    $item = $menuItems[$itemIndex];
                    OrderItem::create([
                        'order_id' => $order->id,
                        'name' => $item['name'],
                        'price' => $item['price'],
                        'qty' => rand(1, 3),
                    ]);
                    $total += $item['price'] * rand(1, 3);
                }

                // Update order total
                $order->update(['total' => $total]);
            }
        }

        $this->command->info('Orders seeded successfully!');
    }
}
