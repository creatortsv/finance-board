<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Label;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;

class ActivityTest extends TestCase
{
    use RefreshDatabase;

    const API_URL = '/api/activities';

    /**
     * GET api/activities
     * @return void
     */
    public function testGetActivitiesWithoutParameters(): void
    {
        $this
            ->json(...($args = [Request::METHOD_GET, self::API_URL]))
            ->assertStatus(Response::HTTP_UNAUTHORIZED);

        $user = User::factory()
            ->has(Activity::factory()->count(3))
            ->create();

        $some = User::factory()
            ->create();

        $this
            ->actingAs($some, 'api')
            ->json(...$args)
            ->assertOk()
            ->assertJsonCount(0, 'data');

        $this
            ->actingAs($user, 'api')
            ->json(...$args)
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure(['data' => [
                '*' => [
                    'id',
                    'name',
                    'owner_id',
                ],
            ]]);
    }

    /**
     * GET api/activities/:id
     * @return void
     */
    public function testGetActivity(): void
    {
        $some = User::factory()->create();
        $user = User::factory()
            ->has(Activity::factory())
            ->create();

        $activity = $user
            ->activities
            ->first();

        $this->assertCount(1, Activity::all());
        $this->assertNotEmpty($activity);

        $this
            ->json(...($args = [Request::METHOD_GET, self::API_URL . '/' . $activity->id]))
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
                'id' => $activity->id,
                'owner_id' => $user->id,
            ]);
    }

    /**
     * POST api/activities/:id
     * @return void
     */
    public function testCreateActivity(): void
    {
        $user = User::factory()->create();
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
                'name',
            ]);

        $this
        /** Empty */
            ->actingAs(...$guard)
            ->json(Request::METHOD_POST, self::API_URL, [
                'name' => 'test',
                'start' => 'start',
                'finish' => 'finish',
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors([
                'start' => 'The start is not a valid date.',
                'finish' => 'The finish is not a valid date.',
            ])
            ->assertJsonMissingValidationErrors([
                'name',
            ]);

        $this
        /** Create */
            ->actingAs(...$guard)
            ->json(Request::METHOD_POST, self::API_URL, [
                'start' => '2020-10-01',
                'finish' => '2020-10-02',
                'name' => 'test',
            ])
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJsonFragment([
                'name' => 'test',
            ]);
    }

    /**
     * PUT|PATCH api/activities/:id
     * @return void
     */
    public function testUpdateActivity(): void
    {
        $some = User::factory()->create();
        $user = User::factory()
            ->has(Activity::factory())
            ->create();

        $activity = $user->activities->first();
        $guard = [$user, 'api'];

        $this
        /** Unauthorized */
            ->json(Request::METHOD_PATCH, self::API_URL . '/' . $activity->id)
            ->assertStatus(Response::HTTP_UNAUTHORIZED);

        $this
            ->actingAs($some, 'api')
            ->json(Request::METHOD_PATCH, self::API_URL . '/' . $activity->id, [
                'name' => $activity->name,
                'start' => $activity->start,
                'finish' => $activity->finish,
            ])
            ->assertStatus(Response::HTTP_NOT_FOUND);

        $this
            ->actingAs(...$guard)
            ->json(Request::METHOD_PATCH, self::API_URL . '/' . $activity->id, [
                'name' => 'test',
                'start' => $activity->start,
                'finish' => $activity->finish,
            ])
            ->assertOk()
            ->assertJsonMissingValidationErrors([
                'name',
                'start',
                'finish',
            ])
            ->assertJsonFragment([
                'name' => 'test',
            ]);
    }

    /**
     * DELETE api/activities/:id
     * @return void
     */
    public function testDeleteActivity(): void
    {
        $activity = Activity::factory()
            ->forOwner()
            ->create();

        $user = $activity->owner;
        $some = User::factory()->create();

        $this->assertNotEmpty($user);
        $this->assertCount(1, Activity::all());
        $this
        /** Unauthorized */
            ->json(...($args = [Request::METHOD_DELETE, self::API_URL . '/' . $activity->id]))
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
            ->assertJson(['message' => 'Activity deleted']);

        $this->assertCount(0, Activity::all());
    }
}
