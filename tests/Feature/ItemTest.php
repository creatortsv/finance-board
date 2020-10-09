<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Label;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;

class ItemTest extends TestCase
{
    use RefreshDatabase,
        WithFaker;

    const GUARD = 'api';
    
    /**
     * @return array
     */
    public function dataProvider(): array
    {
        return [
            Expense::class => [Expense::factory(), 3],
            Income::class => [Income::factory(), 3],
        ];
    }

    /**
     * GET /api/:route [expenses|incomes]
     * 
     * @dataProvider dataProvider
     * @param Factory $factory
     * @param int $count
     * @return void
     */
    public function testGetItemsWithoutRelations(Factory $factory, int $count): void
    {
        $some = User::factory()->create();
        $user = User::factory()
            ->has($factory->count($count))
            ->create();

        $table = $factory
            ->newModel()
            ->getTable();

        $this
        /** Unauthorized */
            ->json(...($args = [Request::METHOD_GET, '/' . static::GUARD . '/' . $table]))
            ->assertStatus(Response::HTTP_UNAUTHORIZED);

        $this
        /** Get by some user */
            ->actingAs($some, static::GUARD)
            ->json(...$args)
            ->assertOk()
            ->assertJsonCount(0, 'data');

        $this
        /** Get by author */
            ->actingAs($user, static::GUARD)
            ->json(...$args)
            ->assertOk()
            ->assertJsonCount($count, 'data')
            ->assertJsonStructure(['data' => [
                '*' => [
                    'id',
                    'comment',
                    'quantity',
                    'date',
                    'user_id',
                    'activity_id',
                ],
            ]])
            ->assertJsonFragment([
                'user_id' => $user->id,
                'activity_id' => null,
            ]);
    }

    /**
     * GET /api/:route/:id?_with=activity,labels [expenses|incomes]
     * 
     * @dataProvider dataProvider
     * @param Factory $factory
     * @param int $count
     * @return void
     */
    public function testGetItemWithRelations(Factory $factory, int $count): void
    {
        $activity = Activity::factory()
            ->forOwner()
            ->has($factory
                ->count($count)
                ->state(function (array $attributes, Activity $activity): array {
                    return ['user_id' => $activity->owner_id];
                })
                ->hasAttached(Label::factory()
                    ->count(2)
                    ->state(function (array $attributes, Model $item): array {
                        return ['owner_id' => $item->user_id];
                    }), [
                        'item_model' => get_class($factory->newModel()),
                    ]))
            ->create();

        $some = User::factory()->create();
        $user = $activity->owner;
        $table = $factory
            ->newModel()
            ->getTable();

        $item = $user
            ->$table
            ->first();

        $this->json(...($args = [Request::METHOD_GET, sprintf('/%s/%s/%s?', static::GUARD, $table, $item->id) . http_build_query([
            '_with' => 'user,activity,labels',
        ])]))
            ->assertStatus(Response::HTTP_UNAUTHORIZED);

        $this
            ->actingAs($some, static::GUARD)
            ->json(...$args)
            ->assertStatus(Response::HTTP_NOT_FOUND);

        $this
            ->actingAs($user, static::GUARD)
            ->json(...$args)
            ->assertOk()
            ->assertJsonStructure(['data' => [
                'id',
                'comment',
                'quantity',
                'date',
                'user_id',
                'user',
                'activity_id',
                'activity',
                'labels',
            ]])
            ->assertJsonFragment([
                'id' => $item->id,
                'quantity' => $item->quantity,
                'date' => $item->date->format('Y-m-d H:i:s'),
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'activity' => [
                    'id' => $activity->id,
                    'name' => $activity->name,
                    'owner_id' => $activity->owner_id,
                ],
            ])
            ->assertJsonCount(2, 'data.labels');
    }

    /**
     * POST /api/:route [expenses|incomes]
     * 
     * @dataProvider dataProvider
     * @param Factory $factory
     * @param int $count
     * @return void
     */
    public function testCreateItem(Factory $factory, int $count): void
    {
        [
            $some,
            $user,
        ] = User::factory()
            ->has(Activity::factory())
            ->has(Label::factory()->count($count))
            ->count(2)
            ->create()
            ->values();

        $guard = [$user, static::GUARD];
        $table = $factory
            ->newModel()
            ->getTable();

        $this
            ->json(...($args = [Request::METHOD_POST, sprintf('/%s/%s', static::GUARD, $table)]))
            ->assertStatus(Response::HTTP_UNAUTHORIZED);

        $this
            ->actingAs(...$guard)
            ->json(...$args)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors([
                'date' => 'The date field is required.',
                'quantity' => 'The quantity field is required.',
            ])
            ->assertJsonMissingValidationErrors([
                'labels',
                'comment',
                'activity_id',
            ]);

        $this
            ->actingAs(...$guard)
            ->json(...array_merge($args, [[
                'date' => '1 may',
                'quantity' => 'five',
                'activity_id' => $some
                    ->activities
                    ->first()
                    ->id,
                'labels' => $some
                    ->labels
                    ->pluck('id'),
            ]]))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors([
                'date' => 'The date is not a valid date.',
                'quantity' => 'The quantity must be a number.',
                'activity_id' => 'The selected activity id is invalid.',
                'labels.0' => 'The selected labels.0 is invalid.',
                'labels.1' => 'The selected labels.1 is invalid.',
                'labels.2' => 'The selected labels.2 is invalid.',
            ])
            ->assertJsonMissingValidationErrors([
                'comment',
            ]);

        $this
            ->actingAs(...$guard)
            ->json(...array_merge($args, [[
                'date' => Carbon::now()->format('Y-m-d H:i:s'),
                'quantity' => 4500,
                'activity_id' => $user
                    ->activities
                    ->first()
                    ->id,
                'labels' => $user
                    ->labels
                    ->pluck('id'),
            ]]))
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJsonMissingValidationErrors([
                'date',
                'comment',
                'quantity',
                'activity_id',
                'labels',
            ])
            ->assertExactJson(['data' => [
                'id' => ($item = $user->$table()->first())->id,
                'date' => $item->date->format('Y-m-d H:i:s'),
                'quantity' => (int)$item->quantity,
                'comment' => null,
                'user_id' => $user->id,
                'activity_id' => $user
                    ->activities
                    ->first()
                    ->id,
            ]]);
    }

    /**
     * PUT|PATCH /api/:route/:id [expenses|incomes]
     * 
     * @dataProvider dataProvider
     * @param Factory $factory
     * @param int $count
     * @return void
     */
    public function testUpdateItem(Factory $factory, int $count): void
    {
        [
            $some,
            $user,
        ] = User::factory()
            ->has(Activity::factory()->count($count))
            ->has(Label::factory()->count($count))
            ->has($factory->count($count))
            ->count(2)
            ->create()
            ->values();

        $guard = [$user, static::GUARD];
        $table = $factory
            ->newModel()
            ->getTable();

        $item = $user
            ->$table()
            ->first();

        $this->assertNotEmpty($item->activity);
        $this->assertFalse($item->activity->exists);
        $this->assertCount(0, $item->labels()->get());

        $this
            ->json(...($args = [Request::METHOD_PUT, sprintf('/%s/%s/%s', static::GUARD, $table, $item->id)]))
            ->assertStatus(Response::HTTP_UNAUTHORIZED);

        $this
            ->actingAs(...$guard)
            ->json(...$args)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors([
                'date' => 'The date field is required.',
                'quantity' => 'The quantity field is required.',
            ])
            ->assertJsonMissingValidationErrors([
                'labels',
                'comment',
                'activity_id',
            ]);

        $this
            ->actingAs(...$guard)
            ->json(...array_merge($args, [[
                'date' => '1 may',
                'quantity' => 'five',
                'activity_id' => $some
                    ->activities
                    ->first()
                    ->id,
                'labels' => $some
                    ->labels
                    ->pluck('id'),
            ]]))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors([
                'date' => 'The date is not a valid date.',
                'quantity' => 'The quantity must be a number.',
                'activity_id' => 'The selected activity id is invalid.',
                'labels.0' => 'The selected labels.0 is invalid.',
                'labels.1' => 'The selected labels.1 is invalid.',
                'labels.2' => 'The selected labels.2 is invalid.',
            ])
            ->assertJsonMissingValidationErrors([
                'comment',
            ]);

        $this
            ->actingAs(...$guard)
            ->json(...array_merge($args, [[
                'date' => Carbon::now()->format('Y-m-d H:i:s'),
                'quantity' => 4500,
                'comment' => null,
                'activity_id' => $user
                    ->activities
                    ->first()
                    ->id,
                'labels' => $user
                    ->labels
                    ->pluck('id'),
            ]]))
            ->assertOk()
            ->assertJsonMissingValidationErrors([
                'date',
                'comment',
                'quantity',
                'activity_id',
                'labels',
            ])
            ->assertExactJson(['data' => [
                'id' => ($item = $user->$table()->first())->id,
                'date' => $item->date->format('Y-m-d H:i:s'),
                'quantity' => (int)$item->quantity,
                'comment' => null,
                'user_id' => $user->id,
                'activity_id' => $user
                    ->activities
                    ->first()
                    ->id,
            ]]);

        $item = get_class($item)::find($item->id);
        $this->assertNotEmpty($item->activity);
        $this->assertTrue($item->activity->exists);
        $this->assertCount($count, $item->labels()->get());
    }

    /**
     * DELETE /api/:route/:id [expenses|incomes]
     * 
     * @dataProvider dataProvider
     * @param Factory $factory
     * @param int $count
     * @return void
     */
    public function testDeleteItem(Factory $factory, int $count): void
    {
        $item = $factory
            ->forUser()
            ->count($count)
            ->create()
            ->first();

        $user = $item->user;
        $some = User::factory()->create();
        $class = get_class($factory->newModel());
        $table = (new $class)->getTable();

        $this->assertCount($count, $class::all());
        $this
        /** Unauthorized */
            ->json(...($args = [Request::METHOD_DELETE, sprintf('/%s/%s/%s', static::GUARD, $table, $item->id)]))
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
            ->assertJson(['message' => basename(str_replace('\\', '/', $class)) . ' deleted']);
    
        $this->assertCount($count - 1, $class::all());
    }
}
