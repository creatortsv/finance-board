<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\User;
use App\Models\Expense;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;

class ExpenseTest extends TestCase
{
    use RefreshDatabase;

    const API_URL = '/api/expenses';

    /**
     * GET /api/expenses
     * @return void
     */
    public function testGetExpensesWithoutParams(): void
    {
        $this
        /** Unauthorized */
            ->json(...($args = [Request::METHOD_GET, self::API_URL]))
            ->assertStatus(Response::HTTP_UNAUTHORIZED);

        $some = User::factory()->create();
        $user = User::factory()
            ->has(Expense::factory()
            ->count(3))
            ->create();

        $this
        /** Get expenses with some user */
            ->actingAs($some, 'api')
            ->json(...$args)
            ->assertOk()
            ->assertJsonCount(0, 'data');

        $this
        /** Get by author */
            ->actingAs($user, 'api')
            ->json(...$args)
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure(['data' => [
                '*' => [
                    'id',
                    'comment',
                    'quantity',
                    'date',
                    'user_id',
                    'activity_id',
                ],
            ]]);
    }

    /**
     * POST /api/expenses
     * @return void
     */
    public function testCreateExpense(): void
    {
        $user = User::factory()
            ->has(Activity::factory())
            ->create();

        $guard = [$user, 'api'];
        $this
        /** Unauthorized */
            ->json(Request::METHOD_POST, self::API_URL)
            ->assertStatus(Response::HTTP_UNAUTHORIZED);

        $this
        /** Empty */
            ->actingAs(...$guard)
            ->json(Request::METHOD_POST, self::API_URL, [])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors([
                'quantity'
            ]);

        $this
        /** Empty */
            ->actingAs(...$guard)
            ->json(Request::METHOD_POST, self::API_URL, [
                'date' => 'some',
                'quantity' => 3400,
                'activity_id' => 4,
                'labels' => [4, 5],
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors([
                'date' => [],
                'activity_id' => [],
                'labels' => [],
            ])
            ->assertJsonMissingValidationErrors([
                'quantity',
            ]);

        $this
        /** Create */
            ->actingAs(...$guard)
            ->json(Request::METHOD_POST, self::API_URL, [
                'date' => '1 may',
                'quantity' => 3400,
                'labels' => $user
                    ->labels()
                    ->pluck('id'),
                'activity_id' => $user
                    ->activities
                    ->first()
                    ->id,
            ])
            ->assertOk();
    }

    /**
     * DELETE /api/expenses
     * @return void
     */
    public function testDeleteExpense(): void
    {
        $expense = Expense::factory()
            ->forUser()
            ->create();

        $user = $expense->user;
        $some = User::factory()->create();

        $this->assertNotEmpty($user);
        $this
        /** Unauthorized */
            ->json(...($args = [Request::METHOD_DELETE, self::API_URL . '/' . $expense->id]))
            ->assertStatus(Response::HTTP_UNAUTHORIZED);

        $this
        /** Delete with different user */
            ->actingAs($some, 'api')
            ->json(...$args)
            ->assertStatus(Response::HTTP_FORBIDDEN);

        $this
        /** Delete by author */
            ->actingAs($user, 'api')
            ->json(...$args)
            ->assertOk()
            ->assertJson(['message' => 'Expense deleted']);
    }
}
