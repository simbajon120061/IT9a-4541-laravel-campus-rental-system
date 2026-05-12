<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Features;
use Laravel\Jetstream\Jetstream;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        if (! Features::enabled(Features::registration())) {
            $this->markTestSkipped('Registration support is not enabled.');
        }

        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('First Name');
        $response->assertSee('Last Name');
        $response->assertSee('Phone Number');
        $response->assertSee('maxlength="11"', false);
        $response->assertSee('pattern="[0-9]{11}"', false);
        $response->assertSee('Program');
        $response->assertSee('Year Level');
        $response->assertSee('Computing Education');
        $response->assertSee('1st Year');
    }

    public function test_registration_screen_cannot_be_rendered_if_support_is_disabled(): void
    {
        if (Features::enabled(Features::registration())) {
            $this->markTestSkipped('Registration support is enabled.');
        }

        $response = $this->get('/register');

        $response->assertStatus(404);
    }

    public function test_new_users_can_register(): void
    {
        if (! Features::enabled(Features::registration())) {
            $this->markTestSkipped('Registration support is not enabled.');
        }

        $response = $this->post('/register', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@umindanao.edu.ph',
            'phone_number' => '09123456789',
            'course' => 'Computing Education',
            'year_level' => '3rd Year',
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature(),
        ]);

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@umindanao.edu.ph',
            'phone_number' => '09123456789',
            'course' => 'Computing Education',
            'year_level' => '3rd Year',
        ]);
        $response->assertRedirect(route('portal.choose', absolute: false));
    }

    public function test_registered_users_choose_how_to_continue(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('portal.choose'))
            ->assertOk()
            ->assertSee('How would you like to continue?')
            ->assertSee('Continue as Lister')
            ->assertSee('Continue as Renter');

        $this->actingAs($user)
            ->post(route('portal.continue', ['portal' => 'lister']))
            ->assertRedirect(route('lister.dashboard', absolute: false))
            ->assertSessionHas('active_portal', 'lister');

        $this->actingAs($user)
            ->post(route('portal.continue', ['portal' => 'renter']))
            ->assertRedirect(route('renter.dashboard', absolute: false))
            ->assertSessionHas('active_portal', 'renter');
    }

    public function test_users_with_non_umindanao_email_cannot_register(): void
    {
        if (! Features::enabled(Features::registration())) {
            $this->markTestSkipped('Registration support is not enabled.');
        }

        $response = $this->from('/register')->post('/register', [
            'first_name' => 'External',
            'last_name' => 'User',
            'email' => 'external@example.com',
            'phone_number' => '09123456789',
            'course' => 'Computing Education',
            'year_level' => '1st Year',
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature(),
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_users_must_select_valid_program_and_school_level_to_register(): void
    {
        if (! Features::enabled(Features::registration())) {
            $this->markTestSkipped('Registration support is not enabled.');
        }

        $response = $this->from('/register')->post('/register', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'invalid-options@umindanao.edu.ph',
            'phone_number' => '09123456789',
            'course' => 'Invalid Program',
            'year_level' => '6th Year',
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature(),
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors(['course', 'year_level']);
        $this->assertGuest();
    }

    public function test_users_must_provide_phone_number_to_register(): void
    {
        if (! Features::enabled(Features::registration())) {
            $this->markTestSkipped('Registration support is not enabled.');
        }

        $response = $this->from('/register')->post('/register', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'missing-phone@umindanao.edu.ph',
            'course' => User::PROGRAMS[0],
            'year_level' => User::SCHOOL_LEVELS[0],
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature(),
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors('phone_number');
        $this->assertGuest();
    }

    public function test_users_must_provide_an_eleven_digit_phone_number_to_register(): void
    {
        if (! Features::enabled(Features::registration())) {
            $this->markTestSkipped('Registration support is not enabled.');
        }

        $response = $this->from('/register')->post('/register', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'invalid-phone@umindanao.edu.ph',
            'phone_number' => '091234567890',
            'course' => User::PROGRAMS[0],
            'year_level' => User::SCHOOL_LEVELS[0],
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature(),
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors('phone_number');
        $this->assertGuest();
    }

    public function test_users_must_accept_terms_and_privacy_to_register(): void
    {
        if (! Features::enabled(Features::registration())) {
            $this->markTestSkipped('Registration support is not enabled.');
        }

        if (! Jetstream::hasTermsAndPrivacyPolicyFeature()) {
            $this->markTestSkipped('Terms and privacy feature is not enabled.');
        }

        $response = $this->from('/register')->post('/register', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test2@umindanao.edu.ph',
            'phone_number' => '09123456789',
            'course' => User::PROGRAMS[0],
            'year_level' => User::SCHOOL_LEVELS[0],
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors('terms');
        $this->assertGuest();
    }
}
