<?php

namespace Tests\Feature\Http\Controllers\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class LoginControllerTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testExample()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function mostrar_formulario_de_login(){
        $res = $this->get(route('login'));
        $res->assertStatus(200);
        $res->assertViewIs('auth.login');
    }

    /**
     * @test
     */
    public function mostrar_error_de_validacion_en_login(){
        $res = $this->post(route('login'),[]);
        $res->assertStatus(302);
        $res->assertSessionHasErrors('email');
    }

    /**
     * @test
     */
    public function loguear_un_usuario(){
        $user = User::factory()->create();
        $res = $this->post(route('login'),[
            'email'=>$user->email,
            'password'=>'password'
        ]);
        $res->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
    }

}
