<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\User;
use App\Models\Expense;
use App\Models\Label;
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
     * GET api/expenses/:id
     * @return void
     */
    public function testGetExpense(): void
    {
        $some = User::factory()->create();
        $user = User::factory()
            ->has(Expense::factory())
            ->create();

        $expense = $user
            ->expenses
            ->first();

        $this->assertCount(1, Expense::all());
        $this->assertNotEmpty($expense);

        $this
            ->json(...($args = [Request::METHOD_GET, self::API_URL . '/' . $expense->id]))
            ->assertStatus(Response::HTTP_UNAUTHORIZED);

        $this 
            ->actingAs($some, 'api')
            ->json(...$args)
            ->assertStatus(Response::HTTP_NOT_FOUND);

        $this 
            ->actingAs($user, 'api')
            ->json(...$args)
            ->assertOk()
            ->assertJsonFragment([
                'id' => $expense->id,
                'user_id' => $user->id,
            ]);
    }

    /**
     * POST /api/expenses
     * @return void
     */
    public function testCreateExpense(): void
    {
        $some = User::factory()
            ->has(Activity::factory())
            ->has(Label::factory()->count(2))
            ->create();

        $user = User::factory()
            ->has(Activity::factory())
            ->create();

        $this->assertNotEmpty($activity = $some
            ->activities
            ->first());

        $this->assertCount(2, $labels = $some->labels);

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
                'date',
                'quantity',
            ]);

        $this
        /** Empty */
            ->actingAs(...$guard)
            ->json(Request::METHOD_POST, self::API_URL, [
                'date' => 'some',
                'quantity' => 3400,
                'activity_id' => $activity->id,
                'labels' => $labels->pluck('id'),
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors([
                'date' => 'The date is not a valid date.',
                'activity_id' => 'The selected activity id is invalid.',
                'labels.0' => 'The selected labels.0 is invalid.',
                'labels.1' => 'The selected labels.1 is invalid.',
            ])
            ->assertJsonMissingValidationErrors([
                'quantity',
            ]);

        $this
        /** Create */
            ->actingAs(...$guard)
            ->json(Request::METHOD_POST, self::API_URL, [
                'date' => '2020-10-01',
                'quantity' => 3400,
                'labels' => $user
                    ->labels()
                    ->pluck('id'),
                'activity_id' => $user
                    ->activities
                    ->first()
                    ->id,
            ])
            ->assertStatus(Response::HTTP_CREATED);
    }

    /**
     * PUT|PATCH api/expenses/:id
     * @return void
     */
    public function testUpdateExpense(): void
    {
        $some = User::factory()
            ->has(Activity::factory())
            ->has(Label::factory()->count(2))
            ->create();

        $user = User::factory()
            ->has(Activity::factory())
            ->has(Expense::factory())
            ->create();

        $this->assertNotEmpty($activity = $some
            ->activities
            ->first());

        $this->assertCount(2, $labels = $some->labels);

        $expense = $user->expenses->first();
        $guard = [$user, 'api'];

        $this
        /** Unauthorized */
            ->json(Request::METHOD_PATCH, self::API_URL . '/' . $expense->id)
            ->assertStatus(Response::HTTP_UNAUTHORIZED);

        $this
            ->actingAs($some, 'api')
            ->json(Request::METHOD_PATCH, self::API_URL . '/' . $expense->id, [
                'date' => $expense->date,
                'quantity' => $expense->quantity,
            ])
            ->assertStatus(Response::HTTP_NOT_FOUND);

        $this
            ->actingAs(...$guard)
            ->json(Request::METHOD_PATCH, self::API_URL . '/' . $expense->id, [
                'comment' => 'test',
                'date' => $expense->date,
                'quantity' => $expense->quantity,
                'activity_id' => $activity->id,
                'labels' => $labels->pluck('id'),
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonMissingValidationErrors([
                'date',
                'comment',
                'quantity',
            ])
            ->assertJsonValidationErrors([
                'activity_id' => 'The selected activity id is invalid.',
                'labels.0' => 'The selected labels.0 is invalid.',
                'labels.1' => 'The selected labels.1 is invalid.',
            ]);

        $this
            ->actingAs(...$guard)
            ->json(Request::METHOD_PATCH, self::API_URL . '/' . $expense->id, [
                'comment' => 'test',
                'date' => $expense->date,
                'quantity' => 3400,
                'activity_id' => $user
                    ->activities
                    ->first()
                    ->id,
            ])
            ->assertOk()
            ->assertJsonMissingValidationErrors([
                'date',
                'comment',
                'quantity',
                'activity_id',
                'labels',
            ])
            ->assertJsonFragment([
                'comment' => 'test',
                'quantity' => 3400,
            ]);
    }

    /**
     * DELETE /api/expenses/:id
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
        $this->assertCount(1, Expense::all());
        $this
        /** Unauthorized */
            ->json(...($args = [Request::METHOD_DELETE, self::API_URL . '/' . $expense->id]))
            ->assertStatus(Response::HTTP_UNAUTHORIZED);

        $this
        /** Delete with different user */
            ->actingAs($some, 'api')
            ->json(...$args)
            ->assertStatus(Response::HTTP_NOT_FOUND);

        $this
        /** Delete by author */
            ->actingAs($user, 'api')
            ->json(...$args)
            ->assertOk()
            ->assertJson(['message' => 'Expense deleted']);

        $this->assertCount(0, Expense::all());
    }
}
