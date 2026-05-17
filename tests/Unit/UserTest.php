<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_name_accessor_returns_full_name(): void
    {
        $user = User::factory()->create([
            'first_name' => 'Alex',
            'last_name' => 'Morgan',
        ]);

        $this->assertSame('Alex Morgan', $user->name);
    }

    public function test_name_accessor_trims_extra_whitespace(): void
    {
        $user = User::factory()->create([
            'first_name' => 'Alex',
            'last_name' => '',
        ]);

        $this->assertSame('Alex', $user->name);
    }

    public function test_name_is_not_mass_assignable(): void
    {
        $user = User::factory()->create([
            'first_name' => 'Before',
            'last_name' => 'User',
        ]);

        $user->fill(['name' => 'Ignored Name']);
        $user->save();
        $user->refresh();

        $this->assertSame('Before User', $user->name);
        $this->assertSame('Before', $user->first_name);
        $this->assertSame('User', $user->last_name);
    }
}
