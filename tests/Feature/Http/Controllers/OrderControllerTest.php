<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function verificar_que_los_no_logeados_no_entran_a_ordenes(){
        $res = $this->get(route('home'));
        $res->assertStatus(302);
        $res->assertRedirect(route('login'));
    }
    /**
     * @test
     */
    public function verificar_lista_de_ordenes(){
        $user = User::factory()->create();
        $order = Order::factory()->create();

        $res = $this->actingAs($user)->get(route('home'));
        $res->assertStatus(200);
        $res->assertSee($order->customer_name);
    }
}
