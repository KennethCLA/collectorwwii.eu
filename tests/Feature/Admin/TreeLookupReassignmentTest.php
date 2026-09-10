<?php

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\LookupIndexController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TreeLookupReassignmentTest extends TestCase
{
    use RefreshDatabase;

    public static function treeTypes(): array
    {
        $types = [];
        foreach (LookupIndexController::types() as $type => $config) {
            if ($config['tree'] ?? false) {
                $types[$type] = [$type, $config['table']];
            }
        }

        return $types;
    }

    private function makeTree(string $table): array
    {
        $this->actingAs(User::factory()->create(['role_id' => 1]));
        $insert = fn ($name, $parent = null) => DB::table($table)->insertGetId([
            'name' => $name, 'parent_id' => $parent, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $root = $insert('Source root');
        $child = $insert('Child', $root);
        $grandchild = $insert('Grandchild', $child);
        $destination = $insert('Destination root');

        return [$root, $child, $grandchild, $destination];
    }

    #[DataProvider('treeTypes')]
    public function test_moves_preserve_ids_subtrees_and_allow_returning_to_root(string $type, string $table): void
    {
        [$root, $child, $grandchild, $destination] = $this->makeTree($table);
        $ids = DB::table($table)->orderBy('id')->pluck('id')->all();
        $update = route('admin.lookups.update', [$type, $root]);

        $this->patch($update, ['name' => 'Source root', 'parent_id' => $destination])
            ->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseHas($table, ['id' => $root, 'parent_id' => $destination]);
        $this->assertDatabaseHas($table, ['id' => $child, 'parent_id' => $root]);
        $this->assertDatabaseHas($table, ['id' => $grandchild, 'parent_id' => $child]);

        $this->patch(route('admin.lookups.update', [$type, $child]), ['name' => 'Child', 'parent_id' => null])
            ->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseHas($table, ['id' => $child, 'parent_id' => null]);
        $this->assertDatabaseHas($table, ['id' => $grandchild, 'parent_id' => $child]);
        $this->assertSame($ids, DB::table($table)->orderBy('id')->pluck('id')->all());
    }

    #[DataProvider('treeTypes')]
    public function test_server_rejects_self_and_descendants_as_parents(string $type, string $table): void
    {
        [$root, $child, $grandchild] = $this->makeTree($table);
        foreach ([$root, $child, $grandchild] as $invalidParent) {
            $this->from(route('admin.lookups.index', $type))
                ->patch(route('admin.lookups.update', [$type, $root]), [
                    'name' => 'Source root', 'parent_id' => $invalidParent,
                ])->assertSessionHasErrors('parent_id');
            $this->assertDatabaseHas($table, ['id' => $root, 'parent_id' => null]);
        }
    }

    #[DataProvider('treeTypes')]
    public function test_parent_options_include_complete_tree_even_when_searching(string $type, string $table): void
    {
        [$root, $child, $grandchild, $destination] = $this->makeTree($table);
        $response = $this->get(route('admin.lookups.index', ['type' => $type, 'q' => 'Source root']))
            ->assertOk()->assertSee('— Root level —')->assertSee('Move under parent');

        $parents = $response->viewData('parent_rows')->keyBy('id');
        $this->assertSame([], $parents[$root]['ancestor_ids']);
        $this->assertSame([$root], $parents[$child]['ancestor_ids']);
        $this->assertSame([$root, $child], $parents[$grandchild]['ancestor_ids']);
        $this->assertSame([], $parents[$destination]['ancestor_ids']);
        $this->assertFalse($response->viewData('tree_rows')->contains('id', $destination));

        $document = new \DOMDocument;
        @$document->loadHTML($response->getContent());
        $xpath = new \DOMXPath($document);
        $options = $xpath->query('//select[@id="lookup-edit-parent"]/option');
        $this->assertCount($parents->count() + 1, $options);
        $this->assertSame('', $options[0]->getAttribute('value'));
        foreach (iterator_to_array($options) as $option) {
            if ($option->getAttribute('value') !== '') {
                $this->assertNotSame('', $option->getAttribute(':disabled'));
            }
        }

        $this->getJson(route('admin.lookups.ajax.parents', $type))->assertOk()
            ->assertJsonFragment(['id' => $grandchild, 'name' => '— — Grandchild'])
            ->assertJsonFragment(['id' => $destination, 'name' => 'Destination root']);
    }

    public function test_flat_lookup_edit_is_unaffected(): void
    {
        $this->actingAs(User::factory()->create(['role_id' => 1]));
        $id = DB::table('book_covers')->insertGetId(['name' => 'Hardback']);
        $this->get(route('admin.lookups.index', 'book-covers'))->assertOk()
            ->assertDontSee('lookup-edit-parent')->assertDontSee('— Root level —');
        $this->patch(route('admin.lookups.update', ['book-covers', $id]), ['name' => 'Paperback'])
            ->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseHas('book_covers', ['id' => $id, 'name' => 'Paperback']);
    }
}
