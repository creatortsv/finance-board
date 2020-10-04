<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Label;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;

class LabelTest extends TestCase
{
    use RefreshDatabase;

    const API_URL = '/api/labels';

    /**
     * GET api/labels
     * @return void
     */
    public function testGetLabelsWithoutParameters(): void
    {
        $this
            ->json(...($args = [Request::METHOD_GET, self::API_URL]))
            ->assertStatus(Response::HTTP_UNAUTHORIZED);

        $user = User::factory()
            ->has(Label::factory()->count(3))
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
     * GET api/labels/:id
     * @return void
     */
    public function testGetLabel(): void
    {
        $some = User::factory()->create();
        $user = User::factory()
            ->has(Label::factory())
            ->create();

        $label = $user
            ->labels
            ->first();

        $this->assertCount(1, Label::all());
        $this->assertNotEmpty($label);

        $this
            ->json(...($args = [Request::METHOD_GET, self::API_URL . '/' . $label->id]))
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
                'id' => $label->id,
                'owner_id' => $user->id,
            ]);
    }

    /**
     * POST api/labels/:id
     * @return void
     */
    public function testCreateLabel(): void
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
            ])
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJsonFragment([
                'name' => 'test',
            ]);
    }

    /**
     * PUT|PATCH api/labels/:id
     * @return void
     */
    public function testUpdateLabel(): void
    {
        $some = User::factory()->create();
        $user = User::factory()
            ->has(Label::factory())
            ->create();

        $label = $user->labels->first();
        $guard = [$user, 'api'];

        $this
        /** Unauthorized */
            ->json(Request::METHOD_PATCH, self::API_URL . '/' . $label->id)
            ->assertStatus(Response::HTTP_UNAUTHORIZED);

        $this
            ->actingAs($some, 'api')
            ->json(Request::METHOD_PATCH, self::API_URL . '/' . $label->id, [
                'name' => $label->name,
            ])
            ->assertStatus(Response::HTTP_NOT_FOUND);

        $this
            ->actingAs(...$guard)
            ->json(Request::METHOD_PATCH, self::API_URL . '/' . $label->id, [
                'name' => 'test',
            ])
            ->assertOk()
            ->assertJsonMissingValidationErrors([
                'name',
            ])
            ->assertJsonFragment([
                'name' => 'test',
            ]);
    }

    /**
     * DELETE api/labels/:id
     * @return void
     */
    public function testDeleteLabel(): void
    {
        $label = Label::factory()
            ->forOwner()
            ->create();

        $user = $label->owner;
        $some = User::factory()->create();

        $this->assertNotEmpty($user);
        $this->assertCount(1, Label::all());
        $this
        /** Unauthorized */
            ->json(...($args = [Request::METHOD_DELETE, self::API_URL . '/' . $label->id]))
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
            ->assertJson(['message' => 'Label deleted']);

        $this->assertCount(0, Label::all());
    }
}
