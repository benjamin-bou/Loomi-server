<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Order;
use App\Models\User;
use App\Models\Box;
use App\Models\BoxCategory;
use App\Models\Subscription;
use App\Models\PaymentMethod;
use App\Models\PaymentMethodType;
use App\Models\BoxOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class OrderModelTest extends TestCase
{
    use RefreshDatabase;

    private function createTestBox($overrides = [])
    {
        $category = BoxCategory::factory()->create();

        return Box::create(array_merge([
            'name' => 'Test Box',
            'description' => 'Test description',
            'base_price' => 29.90,
            'active' => true,
            'quantity' => 10,
            'available_from' => now(),
            'box_category_id' => $category->id
        ], $overrides));
    }

    #[Test]
    public function order_belongs_to_user()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $order->user);
        $this->assertEquals($user->id, $order->user->id);
    }

    #[Test]
    public function order_belongs_to_subscription()
    {
        $subscription = Subscription::factory()->create();
        $order = Order::factory()->create(['subscription_id' => $subscription->id]);

        $this->assertInstanceOf(Subscription::class, $order->subscription);
        $this->assertEquals($subscription->id, $order->subscription->id);
    }
    #[Test]
    public function order_has_many_payment_methods()
    {
        $order = Order::factory()->create();

        // Create payment method types first to avoid duplicates
        $paymentMethodType1 = PaymentMethodType::factory()->create(['name' => 'Credit Card']);
        $paymentMethodType2 = PaymentMethodType::factory()->create(['name' => 'PayPal']);

        // Create payment methods with specific payment method types
        $paymentMethods = [
            PaymentMethod::factory()->create([
                'order_id' => $order->id,
                'payment_method_type_id' => $paymentMethodType1->id
            ]),
            PaymentMethod::factory()->create([
                'order_id' => $order->id,
                'payment_method_type_id' => $paymentMethodType2->id
            ])
        ];

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $order->paymentMethods);
        $this->assertCount(2, $order->paymentMethods);
        $this->assertInstanceOf(PaymentMethod::class, $order->paymentMethods->first());
    }

    #[Test]
    public function order_has_many_box_orders()
    {
        $order = Order::factory()->create();
        $boxOrders = BoxOrder::factory()->count(3)->create(['order_id' => $order->id]);

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $order->boxOrders);
        $this->assertCount(3, $order->boxOrders);
        $this->assertInstanceOf(BoxOrder::class, $order->boxOrders->first());
    }

    #[Test]
    public function order_belongs_to_many_boxes()
    {
        $order = Order::factory()->create();
        $boxes = collect([
            $this->createTestBox(['name' => 'Box 1']),
            $this->createTestBox(['name' => 'Box 2'])
        ]);

        // Créer des BoxOrders pour lier l'ordre aux boîtes
        foreach ($boxes as $index => $box) {
            BoxOrder::factory()->create([
                'order_id' => $order->id,
                'box_id' => $box->id,
                'quantity' => $index + 1
            ]);
        }

        $orderBoxes = $order->boxes;

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $orderBoxes);
        $this->assertCount(2, $orderBoxes);
        $this->assertInstanceOf(Box::class, $orderBoxes->first());
    }

    #[Test]
    public function order_fillable_attributes_are_correct()
    {
        $fillable = [
            'user_id',
            'order_number',
            'total_amount',
            'status',
            'delivery_date',
            'gift_card_id',
            'subscription_id',
            'active',
        ];

        $order = new Order();

        $this->assertEquals($fillable, $order->getFillable());
    }

    #[Test]
    public function order_number_is_unique()
    {
        $order1 = Order::factory()->create(['order_number' => 'ORD001']);

        // Tenter de créer un autre ordre avec le même numéro devrait échouer
        $this->expectException(\Illuminate\Database\QueryException::class);
        Order::factory()->create(['order_number' => 'ORD001']);
    }

    #[Test]
    public function order_total_amount_is_stored_as_decimal()
    {
        $order = Order::factory()->create(['total_amount' => 29.99]);

        $this->assertEquals(29.99, $order->total_amount);
        $this->assertIsFloat($order->total_amount);
    }

    #[Test]
    public function order_has_default_status()
    {
        $order = Order::factory()->create();

        $this->assertEquals('pending', $order->status);
    }

    #[Test]
    public function order_has_default_active_status()
    {
        $order = Order::factory()->create();

        $this->assertTrue($order->active);
    }

    #[Test]
    public function order_can_be_inactive()
    {
        $order = Order::factory()->create(['active' => false]);

        $this->assertFalse($order->active);
    }

    #[Test]
    public function order_delivery_date_can_be_null()
    {
        $order = Order::factory()->create(['delivery_date' => null]);

        $this->assertNull($order->delivery_date);
    }

    #[Test]
    public function order_can_calculate_total_from_box_orders()
    {
        $order = Order::factory()->create(['total_amount' => 0]);
        $box1 = $this->createTestBox(['base_price' => 15.00]);
        $box2 = $this->createTestBox(['base_price' => 20.00]);

        BoxOrder::factory()->create([
            'order_id' => $order->id,
            'box_id' => $box1->id,
            'quantity' => 2
        ]);

        BoxOrder::factory()->create([
            'order_id' => $order->id,
            'box_id' => $box2->id,
            'quantity' => 1
        ]);

        $expectedTotal = (15.00 * 2) + (20.00 * 1); // 50.00

        $calculatedTotal = $order->boxOrders->sum(function ($boxOrder) {
            return $boxOrder->box->base_price * $boxOrder->quantity;
        });

        $this->assertEquals($expectedTotal, $calculatedTotal);
    }
}
