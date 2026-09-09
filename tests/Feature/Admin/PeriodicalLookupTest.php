<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PeriodicalLookupTest extends TestCase
{
    use RefreshDatabase;

    public function test_series_management_navigation_crud_and_delete_protection(): void
    {
        $role = DB::table('roles')->where('name', 'Admin')->value('id')
            ?? DB::table('roles')->insertGetId(['name' => 'Admin']);
        $this->actingAs(User::factory()->create(['role_id' => $role]));
        foreach (['magazine' => \App\Models\Magazine::class, 'newspaper' => \App\Models\Newspaper::class] as $type => $model) {
            $lookup = $type.'-series';
            $table = $type.'_series';
            $index = route('admin.lookups.index', $lookup);
            $this->get(route('admin.dashboard'))->assertOk()->assertSee($index, false);
            $this->get($index)->assertOk();
            $this->post(route('admin.lookups.store', $lookup), ['name' => 'Series'])->assertSessionHasNoErrors()->assertRedirect();
            $id = DB::table($table)->where('name', 'Series')->value('id');
            $this->assertNotNull($id);
            $this->patch(route('admin.lookups.update', [$lookup, $id]), ['name' => 'Renamed'])->assertSessionHasNoErrors()->assertRedirect();
            $record = $model::create(['title' => 'Periodical', 'series_id' => $id]);
            $this->get(route('admin.'.$type.'s.create'))->assertOk()->assertSee('Renamed');
            $this->get(route('admin.'.$type.'s.edit', $record))->assertOk()->assertSee('Renamed');
            $this->delete(route('admin.lookups.destroy', [$lookup, $id]))->assertSessionHas('error');
            $this->assertDatabaseHas($table, ['id' => $id, 'deleted_at' => null]);
            $record->update(['series_id' => null]);
            $this->delete(route('admin.lookups.destroy', [$lookup, $id]))->assertSessionHas('success');
            $this->assertSoftDeleted($table, ['id' => $id]);
        }
    }

    public function test_series_management_requires_admin(): void
    {
        $role = DB::table('roles')->where('name', 'User')->value('id')
            ?? DB::table('roles')->insertGetId(['name' => 'User']);
        $this->actingAs(User::factory()->create(['role_id' => $role]));
        foreach (['magazine-series', 'newspaper-series'] as $type) {
            $this->get(route('admin.lookups.index', $type))->assertForbidden();
            $this->post(route('admin.lookups.store', $type), ['name' => 'Forbidden'])->assertForbidden();
            $this->patch(route('admin.lookups.update', [$type, 1]), ['name' => 'Forbidden'])->assertForbidden();
            $this->delete(route('admin.lookups.destroy', [$type, 1]))->assertForbidden();
        }
    }
}
