@extends('layouts.admin')

@section('admin-content')
<div x-data="lookupSidebar()" class="space-y-5">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-white">{{ $label }}</h1>
            <p class="mt-1 text-sm text-white/70">{{ $description }}</p>
        </div>
        @if($type === 'locations')
        <a href="{{ route('admin.lookups.locations.labels') }}"
            class="rounded-md bg-white/10 px-4 py-2 text-sm font-medium text-white hover:bg-white/15">
            Print QR labels
        </a>
        @endif
    </div>

    <div class="rounded-lg border border-blue-500/20 bg-blue-500/10 px-4 py-3 text-sm text-blue-100">
        Options in use by active records cannot be deleted. Remove all references first.
    </div>

    @if(session('success'))
    <div class="rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-100">
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-100">
        {{ session('error') }}
    </div>
    @endif

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-[1fr_320px]">
        <div class="rounded-xl border border-black/20 bg-black/15 p-4">
            <form method="GET" action="{{ route('admin.lookups.index', ['type' => $type]) }}"
                class="mb-4 flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[220px]">
                    <label class="mb-1 block text-xs text-white/60">Search</label>
                    <input type="text" name="q" value="{{ $search }}" placeholder="Search name..."
                        class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/45 focus:outline-none focus:ring-2 focus:ring-white/20">
                </div>
                @if(!$is_tree)
                <input type="hidden" name="sort" value="{{ $sort }}">
                @endif
                <button type="submit"
                    class="rounded-md bg-white/10 px-4 py-2 text-sm font-medium text-white hover:bg-white/15">
                    Filter
                </button>
                @if($search !== '')
                <a href="{{ route('admin.lookups.index', ['type' => $type]) }}"
                    class="rounded-md bg-white/5 px-4 py-2 text-sm text-white/70 hover:text-white">
                    Clear
                </a>
                @endif
            </form>

            <div class="overflow-x-auto rounded-xl border border-black/25 bg-black/10">
                <table class="w-full text-sm text-white">
                    <thead class="border-b border-white/10 text-xs uppercase text-white/60">
                        <tr>
                            @if($is_tree)
                            <th class="px-4 py-3 text-left">Name</th>
                            <th class="px-4 py-3 text-left">In use (total)</th>
                            <th class="px-4 py-3 text-left">Created</th>
                            @else
                            @php
                                $sortLink = fn(string $col) => route('admin.lookups.index', ['type' => $type, 'q' => $search, 'sort' => $col]);
                                $arrow = fn(string $asc, string $desc) => $sort === $asc ? '↑' : ($sort === $desc ? '↓' : '');
                            @endphp
                            <th class="px-4 py-3 text-left">
                                <a href="{{ $sortLink($sort === 'name_asc' ? 'name_desc' : 'name_asc') }}"
                                    class="inline-flex items-center gap-1 hover:text-white {{ in_array($sort, ['name_asc','name_desc']) ? 'text-white' : '' }}">
                                    Name <span>{{ $arrow('name_asc','name_desc') }}</span>
                                </a>
                            </th>
                            <th class="px-4 py-3 text-left">
                                <a href="{{ $sortLink($sort === 'usage_asc' ? 'usage_desc' : 'usage_asc') }}"
                                    class="inline-flex items-center gap-1 hover:text-white {{ in_array($sort, ['usage_asc','usage_desc']) ? 'text-white' : '' }}">
                                    In use <span>{{ $arrow('usage_asc','usage_desc') }}</span>
                                </a>
                            </th>
                            <th class="px-4 py-3 text-left">
                                <a href="{{ $sortLink($sort === 'created_asc' ? 'created_desc' : 'created_asc') }}"
                                    class="inline-flex items-center gap-1 hover:text-white {{ in_array($sort, ['created_asc','created_desc']) ? 'text-white' : '' }}">
                                    Created <span>{{ $arrow('created_asc','created_desc') }}</span>
                                </a>
                            </th>
                            @endif
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @if($is_tree)
                            @forelse($tree_rows as $row)
                            @php
                                $createdAt = '—';
                                if (!empty($row['created_at'])) {
                                    try {
                                        $createdAt = \Carbon\Carbon::parse($row['created_at'])->format('d/m/Y');
                                    } catch (\Throwable $e) {
                                        $createdAt = (string) $row['created_at'];
                                    }
                                }
                                $indent = $row['depth'] * 20;
                            @endphp
                            <tr class="transition hover:bg-white/5">
                                <td class="px-4 py-3 font-medium" style="padding-left: {{ 16 + $indent }}px">
                                    @if($row['depth'] > 0)
                                    <span class="text-white/40 mr-1">{{ str_repeat('— ', $row['depth']) }}</span>
                                    @endif
                                    {{ $row['name'] }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    @if(($row['usage_total'] ?? 0) > 0)
                                    <span class="inline-flex items-center rounded-full bg-amber-500/20 px-2 py-0.5 text-xs text-amber-200">
                                        {{ $row['usage_total'] }}
                                    </span>
                                    @else
                                    <span class="inline-flex items-center rounded-full bg-emerald-500/20 px-2 py-0.5 text-xs text-emerald-200">
                                        0
                                    </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs text-white/60">{{ $createdAt }}</td>
                                <td class="px-4 py-3 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        @if($type === 'locations')
                                        <a href="{{ route('admin.lookups.locations.contents', $row['id']) }}"
                                            class="rounded-md bg-white/10 px-2.5 py-1 text-xs text-white hover:bg-white/15">
                                            Contents
                                        </a>
                                        @endif
                                        <button type="button"
                                            @click="openEdit({{ $row['id'] }}, {{ json_encode($row['name']) }}, {{ json_encode($row['parent_id']) }})"
                                            class="rounded-md bg-white/10 px-2.5 py-1 text-xs text-white hover:bg-white/15">
                                            Edit
                                        </button>
                                        @if(($row['usage_total'] ?? 0) > 0)
                                        <span class="group relative inline-block">
                                            <button type="button" disabled
                                                class="cursor-not-allowed rounded-md bg-white/10 px-2.5 py-1 text-xs text-white/40">
                                                Delete
                                            </button>
                                            <span class="pointer-events-none absolute bottom-full right-0 mb-2 hidden w-max max-w-[200px] rounded-md bg-black/90 px-2.5 py-1.5 text-xs text-white/90 shadow-lg group-hover:block">
                                                Cannot delete: in use by {{ $row['usage_total'] }} record(s)
                                            </span>
                                        </span>
                                        @else
                                        <form method="POST" action="{{ route('admin.lookups.destroy', ['type' => $type, 'id' => $row['id']]) }}"
                                            onsubmit="return confirm('Delete this option?');"
                                            class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="rounded-md bg-red-500/20 px-2.5 py-1 text-xs text-red-200 hover:bg-red-500/30">
                                                Delete
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-4 py-7 text-center text-white/45">No options found.</td>
                            </tr>
                            @endforelse
                        @else
                            @forelse($rows as $row)
                            @php
                                $createdAt = '—';
                                if (!empty($row->created_at)) {
                                    try {
                                        $createdAt = \Carbon\Carbon::parse($row->created_at)->format('d/m/Y');
                                    } catch (\Throwable $e) {
                                        $createdAt = (string) $row->created_at;
                                    }
                                }
                            @endphp
                            <tr class="transition hover:bg-white/5">
                                <td class="px-4 py-3 font-medium">{{ $row->name }}</td>
                                <td class="px-4 py-3 text-sm">
                                    @if(($row->usage_total ?? 0) > 0)
                                    <span class="inline-flex items-center rounded-full bg-amber-500/20 px-2 py-0.5 text-xs text-amber-200">
                                        {{ $row->usage_total }}
                                    </span>
                                    @else
                                    <span class="inline-flex items-center rounded-full bg-emerald-500/20 px-2 py-0.5 text-xs text-emerald-200">
                                        0
                                    </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs text-white/60">{{ $createdAt }}</td>
                                <td class="px-4 py-3 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <button type="button"
                                            @click="openEdit({{ $row->id }}, {{ json_encode($row->name) }}, null)"
                                            class="rounded-md bg-white/10 px-2.5 py-1 text-xs text-white hover:bg-white/15">
                                            Edit
                                        </button>
                                        @if(($row->usage_total ?? 0) > 0)
                                        <span class="group relative inline-block">
                                            <button type="button" disabled
                                                class="cursor-not-allowed rounded-md bg-white/10 px-2.5 py-1 text-xs text-white/40">
                                                Delete
                                            </button>
                                            <span class="pointer-events-none absolute bottom-full right-0 mb-2 hidden w-max max-w-[200px] rounded-md bg-black/90 px-2.5 py-1.5 text-xs text-white/90 shadow-lg group-hover:block">
                                                Cannot delete: in use by {{ $row->usage_total }} record(s)
                                            </span>
                                        </span>
                                        @else
                                        <form method="POST" action="{{ route('admin.lookups.destroy', ['type' => $type, 'id' => $row->id]) }}"
                                            onsubmit="return confirm('Delete this option?');"
                                            class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="rounded-md bg-red-500/20 px-2.5 py-1 text-xs text-red-200 hover:bg-red-500/30">
                                                Delete
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-4 py-7 text-center text-white/45">No options found.</td>
                            </tr>
                            @endforelse
                        @endif
                    </tbody>
                </table>
            </div>

            @if(!$is_tree)
            <div class="mt-4 text-white">
                {{ $rows->links('pagination::tailwind') }}
            </div>
            @endif
        </div>

        <aside class="rounded-xl border border-black/20 bg-black/15 p-4">
            {{-- Add mode --}}
            <div x-show="!editMode">
                <h2 class="text-base font-semibold text-white">Add option</h2>
                <p class="mt-1 text-sm text-white/65">
                    Add a new value that appears in selection fields.
                </p>

                <form method="POST" action="{{ route('admin.lookups.store', ['type' => $type]) }}" class="mt-4 space-y-3">
                    @csrf
                    @if($is_tree)
                    <div>
                        <label class="mb-1 block text-xs text-white/60">Parent (optional)</label>
                        <select name="parent_id" class="js-select w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-white/20">
                            <option value="">— Root level —</option>
                            @foreach($tree_rows as $tr)
                            <option value="{{ $tr['id'] }}" @selected((string)old('parent_id')===(string)$tr['id'])>
                                {{ str_repeat('— ', $tr['depth']) }}{{ $tr['name'] }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div>
                        <label class="mb-1 block text-xs text-white/60">Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                            class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/45 focus:outline-none focus:ring-2 focus:ring-white/20">
                        @error('name')
                        <p class="mt-1 text-xs text-red-200">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit"
                        class="w-full rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500">
                        Save
                    </button>
                </form>
            </div>

            {{-- Edit mode --}}
            <div x-show="editMode" x-cloak>
                <div class="flex items-center justify-between mb-1">
                    <h2 class="text-base font-semibold text-white">Edit option</h2>
                    <button type="button" @click="closeEdit()" class="text-xs text-white/50 hover:text-white">✕ Cancel</button>
                </div>
                <p class="mt-1 text-sm text-white/65">Rename or move this option.</p>

                <form method="POST" :action="editAction" class="mt-4 space-y-3">
                    @csrf
                    @method('PATCH')
                    @if($is_tree)
                    <div>
                        <label class="mb-1 block text-xs text-white/60">Parent (optional)</label>
                        <select name="parent_id" x-model="editParentId"
                            class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-white/20">
                            <option value="">— Root level —</option>
                            @foreach($tree_rows as $tr)
                            <option value="{{ $tr['id'] }}" :disabled="editId === {{ $tr['id'] }}">
                                {{ str_repeat('— ', $tr['depth']) }}{{ $tr['name'] }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div>
                        <label class="mb-1 block text-xs text-white/60">Name</label>
                        <input type="text" name="name" x-model="editName" required
                            class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/45 focus:outline-none focus:ring-2 focus:ring-white/20">
                    </div>
                    <button type="submit"
                        class="w-full rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500">
                        Save changes
                    </button>
                </form>
            </div>
        </aside>
    </div>
</div>

<script>
function lookupSidebar() {
    return {
        editMode: false,
        editId: null,
        editName: '',
        editParentId: '',
        editAction: '',
        openEdit(id, name, parentId) {
            this.editMode = true;
            this.editId = id;
            this.editName = name;
            this.editParentId = parentId !== null ? String(parentId) : '';
            this.editAction = '{{ route('admin.lookups.update', ['type' => $type, 'id' => '__ID__']) }}'.replace('__ID__', id);
        },
        closeEdit() {
            this.editMode = false;
        },
    };
}
</script>
@endsection
