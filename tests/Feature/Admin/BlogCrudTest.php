<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class BlogCrudTest extends TestCase
{
    use RefreshDatabase;

    private array $posts;

    protected function setUp(): void
    {
        parent::setUp();
        $this->posts = [
            ['date' => '2020-01-01', 'content' => ['en' => 'Legacy post']],
            ['date' => '2020-01-01', 'content' => ['en' => 'Legacy post']],
            ['id' => 'existing-id', 'date' => '2024-01-01', 'content' => ['en' => 'Existing post']],
        ];
        // In-memory JSON: never write the application's actual blog file.
        $path = storage_path('app/public/blog.json');
        File::partialMock();
        File::shouldReceive('exists')->with($path)->andReturnTrue();
        File::shouldReceive('get')->with($path)->andReturnUsing(fn () => json_encode($this->posts));
        File::shouldReceive('ensureDirectoryExists')->with(dirname($path))->andReturnNull();
        File::shouldReceive('put')->with($path, \Mockery::type('string'))->andReturnUsing(function ($actualPath, $json) {
            $this->posts = json_decode($json, true);
            return strlen($json);
        });
    }

    private function user(string $role): User
    {
        $id = DB::table('roles')->where('name', $role)->value('id')
            ?? DB::table('roles')->insertGetId(['name' => $role, 'created_at' => now(), 'updated_at' => now()]);
        return User::factory()->create(['role_id' => $id]);
    }

    public function test_admin_can_edit_update_and_delete_legacy_and_existing_posts(): void
    {
        $this->actingAs($this->user('Admin'));
        $original = $this->posts;
        $posts = $this->get(route('admin.blog.index'))->assertOk()->viewData('posts');
        $this->assertSame($original, $this->posts);
        $this->assertCount(3, array_unique(array_column($posts, 'id')));
        foreach ($posts as $post) {
            $id = $post['id'];
            $this->get(route('admin.blog.edit', $id))->assertOk()->assertSee('name="_token"', false)->assertSee('value="PUT"', false);
            $this->put(route('admin.blog.update', $id), ['date' => '2025-01-01', 'content_en' => 'Updated '.$id])
                ->assertRedirect(route('admin.blog.edit', $id));
            $this->get(route('admin.blog.edit', $id))->assertOk()->assertSee('Updated '.$id);
        }
        $this->get(route('blog'))->assertOk()->assertSee('Updated existing-id');
        foreach ($posts as $post) {
            $this->delete(route('admin.blog.destroy', $post['id']))->assertRedirect(route('admin.blog.index'));
            $this->get(route('admin.blog.edit', $post['id']))->assertNotFound();
        }
        $this->assertSame([], $this->posts);
    }

    public function test_blog_admin_routes_reject_guests_and_non_admins(): void
    {
        foreach ([null, $this->user('User')] as $user) {
            if ($user) {
                $this->actingAs($user);
            }
            foreach ([
                ['get', route('admin.blog.index')],
                ['get', route('admin.blog.edit', 'existing-id')],
                ['put', route('admin.blog.update', 'existing-id')],
                ['delete', route('admin.blog.destroy', 'existing-id')],
                ['post', route('admin.blog.store')],
            ] as [$method, $url]) {
                $response = $this->$method($url);
                $user ? $response->assertForbidden() : $response->assertRedirect(route('login'));
            }
        }
        $this->assertCount(3, $this->posts);
    }
}
