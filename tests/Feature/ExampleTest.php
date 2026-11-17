<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    private const ENDPOINT = '/api/generate-password';
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_it_generates_a_password_with_default_length(): void
    {
        $response = $this->getJson(self::ENDPOINT);

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'password',
                'length',
            ])
            ->assertJsonPath('success', true)
            ->assertJsonPath('length', 20);

        $password = $response->json('password');

        $this->assertIsString($password);
        $this->assertSame(20, strlen($password));
    }
    public function test_it_generates_a_password_with_custom_length(): void
    {
        foreach ([12, 16, 24, 64, 128] as $length) {
            $response = $this->getJson(
                self::ENDPOINT . '?length=' . $length
            );

            $response->assertOk()
                ->assertJsonPath('success', true)
                ->assertJsonPath('length', $length);

            $this->assertSame(
                $length,
                strlen($response->json('password'))
            );
        }
    }

    public function test_it_rejects_passwords_shorter_than_12(): void
    {
        foreach ([1, 8, 11] as $length) {
            $this->getJson(
                self::ENDPOINT . '?length=' . $length
            )
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['length']);
        }
    }

    public function test_it_rejects_passwords_longer_than_128(): void
    {
        foreach ([129, 150, 256] as $length) {
            $this->getJson(
                self::ENDPOINT . '?length=' . $length
            )
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['length']);
        }
    }

    public function test_it_rejects_non_integer_lengths(): void
    {
        foreach (['abc', '12.5', '-5'] as $length) {
            $this->getJson(
                self::ENDPOINT . '?length=' . $length
            )
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['length']);
        }
    }

    public function test_it_generates_a_password_without_spaces(): void
    {
        $response = $this->getJson(
            self::ENDPOINT . '?length=128'
        );

        $response->assertOk();

        $password = $response->json('password');

        $this->assertDoesNotMatchRegularExpression(
            '/\s/',
            $password
        );
    }

    public function test_generated_password_uses_supported_character_types(): void
    {
        $response = $this->getJson(
            self::ENDPOINT . '?length=128'
        );

        $response->assertOk();

        $password = $response->json('password');

        $this->assertMatchesRegularExpression(
            '/^[A-Za-z0-9\W_]+$/',
            $password
        );
    }

    public function test_it_generates_different_passwords(): void
    {
        $first = $this->getJson(self::ENDPOINT . '?length=32');
        $second = $this->getJson(self::ENDPOINT . '?length=32');

        $first->assertOk();
        $second->assertOk();

        $this->assertNotSame(
            $first->json('password'),
            $second->json('password')
        );
    }
}
