<?php

namespace App\Console\Commands;

use App\Models\BookTopic;
use App\Models\ItemCategory;
use App\Models\ItemOrganization;
use App\Models\Location;
use App\Models\MagazineSeries;
use App\Models\NewspaperSeries;
use App\Models\Origin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LookupsFlattenToTree extends Command
{
    protected $signature = 'lookups:flatten-to-tree {type : Lookup type slug, or "all" to run every tree type}';

    protected $description = 'Convert flat dash-separated lookup names into a proper parent/child tree.';

    private array $typeMap = [
        'book-topics'        => 'book_topics',
        'item-categories'    => 'item_categories',
        'item-organizations' => 'item_organizations',
        'locations'          => 'locations',
        'origins'            => 'origins',
        'magazine-series'    => 'magazine_series',
        'newspaper-series'   => 'newspaper_series',
    ];

    private array $modelMap = [
        'book-topics'        => BookTopic::class,
        'item-categories'    => ItemCategory::class,
        'item-organizations' => ItemOrganization::class,
        'locations'          => Location::class,
        'origins'            => Origin::class,
        'magazine-series'    => MagazineSeries::class,
        'newspaper-series'   => NewspaperSeries::class,
    ];

    public function handle(): int
    {
        $type = $this->argument('type');

        if ($type === 'all') {
            foreach (array_keys($this->typeMap) as $t) {
                $this->newLine();
                $this->line("━━━ <options=bold>{$t}</> ━━━");
                $this->processType($t);
            }

            return 0;
        }

        if (! isset($this->typeMap[$type])) {
            $this->error("Unknown type '{$type}'. Valid values: all, " . implode(', ', array_keys($this->typeMap)));

            return 1;
        }

        return $this->processType($type);
    }

    private function processType(string $type): int
    {
        $table = $this->typeMap[$type];

        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'parent_id')) {
            $this->warn("  Skipping '{$type}': table or parent_id column not found.");

            return 0;
        }

        // Load all flat (not yet parented) entries
        $entries = DB::table($table)
            ->whereNull('parent_id')
            ->when(Schema::hasColumn($table, 'deleted_at'), fn ($q) => $q->whereNull('deleted_at'))
            ->orderBy('name')
            ->get();

        if ($entries->isEmpty()) {
            $this->line("  No flat entries in {$type}.");

            return 0;
        }

        // Count how many entries share each possible " - " prefix
        $prefixCounts = [];
        foreach ($entries as $entry) {
            $parts = explode(' - ', $entry->name);
            for ($i = 1; $i < count($parts); $i++) {
                $prefix = implode(' - ', array_slice($parts, 0, $i));
                $prefixCounts[$prefix] = ($prefixCounts[$prefix] ?? 0) + 1;
            }
        }

        // Phase 1: auto-plan entries whose prefix is shared by ≥ 2 entries
        $plan       = [];
        $unique     = []; // first-segment → [entries]
        $noDash     = [];

        foreach ($entries as $entry) {
            $parts = explode(' - ', $entry->name);

            if (count($parts) === 1) {
                $noDash[] = $entry->name;
                continue;
            }

            $splitAt = $this->findSplitAt($parts, $prefixCounts, 2);

            if ($splitAt > 0) {
                $plan[] = $this->makePlanItem($entry, $parts, $splitAt);
            } else {
                // Unique prefix — ask per first-segment group
                $firstSegment = $parts[0];
                $unique[$firstSegment][] = $entry;
            }
        }

        // Phase 2: interactive per unique-prefix group
        if (! empty($unique)) {
            $this->newLine();
            $this->line("  The following entries have <comment>unique prefixes</comment> (not shared by other entries).");
            $this->line("  Review each group and decide whether to split it.");

            foreach ($unique as $firstSegment => $groupEntries) {
                $this->newLine();
                $this->line("  Prefix group: <comment>\"{$firstSegment}\"</comment>");

                $proposed = [];
                foreach ($groupEntries as $entry) {
                    $parts   = explode(' - ', $entry->name);
                    // For unique groups: split at max depth (all segments)
                    $splitAt = count($parts) - 1;
                    $proposed[] = $this->makePlanItem($entry, $parts, $splitAt);
                    $chain   = array_slice($parts, 0, $splitAt);
                    $parent  = end($chain);
                    $newName = implode(' - ', array_slice($parts, $splitAt));
                    $this->line("    \"{$entry->name}\"  →  \"{$newName}\" (under {$parent})");
                }

                if ($this->confirm("    Split this group?", false)) {
                    foreach ($proposed as $item) {
                        $plan[] = $item;
                    }
                }
            }
        }

        if (empty($plan)) {
            $this->newLine();
            $this->info("  Nothing to migrate for {$type}.");

            return 0;
        }

        // Show indented tree preview
        $this->newLine();
        $this->line("  <options=bold>Plan for {$type}:</>");
        $this->newLine();
        $displayTree = $this->buildDisplayTree($plan);
        $this->printDisplayTree($displayTree, 1);

        $this->newLine();
        $this->line('  <options=bold>' . count($plan) . ' entries will be renamed.</>');
        $this->newLine();

        if (! $this->confirm("  Proceed with {$type}?", false)) {
            $this->info("  Skipped {$type}.");

            return 0;
        }

        // Execute
        $nodeCache = [];

        DB::transaction(function () use ($plan, $table, &$nodeCache) {
            foreach ($plan as $item) {
                $parentId = null;
                $chainKey = '';

                foreach ($item['parentChain'] as $segment) {
                    $chainKey = $chainKey === '' ? $segment : "{$chainKey} | {$segment}";

                    if (! isset($nodeCache[$chainKey])) {
                        $q = DB::table($table)->where('name', $segment);
                        if ($parentId === null) {
                            $q->whereNull('parent_id');
                        } else {
                            $q->where('parent_id', $parentId);
                        }
                        if (Schema::hasColumn($table, 'deleted_at')) {
                            $q->whereNull('deleted_at');
                        }
                        $existing = $q->first();

                        if ($existing) {
                            $nodeCache[$chainKey] = $existing->id;
                        } else {
                            $payload = ['name' => $segment, 'parent_id' => $parentId];
                            if (Schema::hasColumn($table, 'created_at')) {
                                $payload['created_at'] = now();
                            }
                            if (Schema::hasColumn($table, 'updated_at')) {
                                $payload['updated_at'] = now();
                            }
                            $nodeCache[$chainKey] = DB::table($table)->insertGetId($payload);
                        }
                    }

                    $parentId = $nodeCache[$chainKey];
                }

                $update = ['name' => $item['newName'], 'parent_id' => $parentId];
                if (Schema::hasColumn($table, 'updated_at')) {
                    $update['updated_at'] = now();
                }
                DB::table($table)->where('id', $item['entry']->id)->update($update);
            }
        });

        Cache::forget($this->modelMap[$type].'::flatTree');

        $this->info("  Done. " . count($plan) . " entries migrated for {$type}.");

        return 0;
    }

    private function findSplitAt(array $parts, array $prefixCounts, int $threshold): int
    {
        $splitAt = 0;
        for ($i = 1; $i < count($parts); $i++) {
            $prefix = implode(' - ', array_slice($parts, 0, $i));
            if (($prefixCounts[$prefix] ?? 0) >= $threshold) {
                $splitAt = $i;
            } else {
                break;
            }
        }

        return $splitAt;
    }

    private function makePlanItem(object $entry, array $parts, int $splitAt): array
    {
        return [
            'entry'       => $entry,
            'parentChain' => array_slice($parts, 0, $splitAt),
            'newName'     => implode(' - ', array_slice($parts, $splitAt)),
        ];
    }

    private function buildDisplayTree(array $plan): array
    {
        $tree = [];

        foreach ($plan as $item) {
            $node = &$tree;
            foreach ($item['parentChain'] as $segment) {
                if (! isset($node[$segment])) {
                    $node[$segment] = ['_renames' => [], '_children' => []];
                }
                $node = &$node[$segment]['_children'];
            }
            $leaf = &$tree;
            foreach ($item['parentChain'] as $segment) {
                $leaf = &$leaf[$segment];
            }
            $leaf['_renames'][] = ['from' => $item['entry']->name, 'to' => $item['newName']];
        }

        return $tree;
    }

    private function printDisplayTree(array $tree, int $depth = 0): void
    {
        $pad = str_repeat('  ', $depth);

        foreach ($tree as $name => $node) {
            if (str_starts_with((string) $name, '_')) {
                continue;
            }

            $this->line("{$pad}<info>CREATE</info>  \"{$name}\"");

            foreach ($node['_renames'] as $rename) {
                $this->line("{$pad}  <fg=cyan>RENAME</>  \"{$rename['from']}\"  →  \"{$rename['to']}\"");
            }

            $this->printDisplayTree($node['_children'], $depth + 1);
        }
    }
}
