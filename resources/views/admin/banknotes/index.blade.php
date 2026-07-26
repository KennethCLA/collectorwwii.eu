{{-- resources/views/admin/banknotes/index.blade.php --}}

@extends('layouts.admin')

@section('admin-content')
    <div class="w-full">
        <div class="mb-6 flex items-center justify-between gap-4">
            <h1 class="text-2xl font-semibold text-white">Banknotes</h1>
            <a href="{{ route('admin.banknotes.create') }}"
                class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500">
                New banknote
            </a>
        </div>

        @if(session('success'))
        <div class="mb-4 rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-100">
            {{ session('success') }}
        </div>
        @endif

        <form method="GET" action="{{ route('admin.banknotes.index') }}"
            class="mb-4 flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs text-white/60 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Variation, Jaeger no...."
                    class="rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-white/20 w-56" />
            </div>
            <div>
                <label class="block text-xs text-white/60 mb-1">Country</label>
                <select name="country_id"
                    class="rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-white/20">
                    <option value="">All</option>
                    @foreach($countries as $c)
                    <option value="{{ $c->id }}" @selected((string)request('country_id')===(string)$c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-white/60 mb-1">Currency</label>
                <select name="currency_id"
                    class="rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-white/20">
                    <option value="">All</option>
                    @foreach($currencies as $c)
                    <option value="{{ $c->id }}" @selected((string)request('currency_id')===(string)$c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-white/60 mb-1">For Sale</label>
                <select name="for_sale"
                    class="rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-white/20">
                    <option value="">All</option>
                    <option value="1" @selected(request('for_sale') === '1')>For sale</option>
                    <option value="0" @selected(request('for_sale') === '0')>Not for sale</option>
                </select>
            </div>
            <button type="submit"
                class="rounded-md bg-white/10 px-4 py-2 text-sm font-medium text-white hover:bg-white/15">Filter</button>
            @if(request()->hasAny(['search','country_id','currency_id','for_sale']))
            <a href="{{ route('admin.banknotes.index') }}"
                class="rounded-md bg-white/5 px-4 py-2 text-sm text-white/60 hover:text-white">Clear</a>
            @endif
        </form>

        <div x-data="{ count: 0, action: '' }" class="relative">

            <div x-show="count > 0" x-transition
                class="fixed bottom-4 left-1/2 -translate-x-1/2 z-50 flex items-center gap-3 rounded-xl border border-white/20 bg-sage-500 px-5 py-3 shadow-2xl">
                <span class="font-mono text-sm text-white/80" x-text="count + ' selected'"></span>
                <div class="h-4 w-px bg-white/20"></div>
                <select x-model="action" class="rounded-md border border-white/20 bg-black/30 px-3 py-1.5 text-sm text-white focus:outline-none">
                    <option value="">Choose action…</option>
                    <option value="for_sale_on">Mark for sale</option>
                    <option value="for_sale_off">Remove from sale</option>
                    <option value="mark_sold">Mark as sold</option>
                    <option value="delete">Delete</option>
                </select>
                <button type="submit" form="bulk-form-banknotes"
                    @click="if (!action) { $event.preventDefault(); return; } if (action === 'delete' && !confirm('Delete ' + count + ' banknote(s)? This cannot be undone.')) $event.preventDefault();"
                    class="rounded-md bg-khaki/20 border border-khaki/30 px-4 py-1.5 text-sm font-medium text-white hover:bg-khaki/30 transition">Apply</button>
                <button type="button"
                    @click="document.querySelectorAll('[data-row-cb-banknotes]').forEach(cb => cb.checked = false); document.getElementById('select-all-banknotes').checked = false; count = 0;"
                    class="text-white/50 hover:text-white text-xs">✕</button>
            </div>

            <form id="bulk-form-banknotes" method="POST" action="{{ route('admin.bulk', 'banknotes') }}">
                @csrf
                <input type="hidden" name="action" x-bind:value="action">

                <div class="overflow-x-auto rounded-xl border border-black/20 bg-black/10">
                    <table class="w-full text-sm text-white">
                        <thead class="border-b border-white/10 text-white/60 uppercase text-xs">
                            <tr>
                                <th class="px-4 py-3 w-8">
                                    <input type="checkbox" id="select-all-banknotes"
                                        class="rounded border-white/30 bg-white/10 accent-khaki"
                                        @change="document.querySelectorAll('[data-row-cb-banknotes]').forEach(cb => cb.checked = $event.target.checked); count = $event.target.checked ? {{ $banknotes->count() }} : 0;">
                                </th>
                                <th class="px-4 py-3 text-left">Country</th>
                                <th class="px-4 py-3 text-left">Currency</th>
                                <th class="px-4 py-3 text-left">Value</th>
                                <th class="px-4 py-3 text-left">Year</th>
                                <th class="px-4 py-3 text-left">For Sale</th>
                                <th class="px-4 py-3 text-left">Created</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @forelse($banknotes as $banknote)
                            <tr class="hover:bg-white/5 transition">
                                <td class="px-4 py-3">
                                    <input type="checkbox" name="ids[]" value="{{ $banknote->id }}" data-row-cb-banknotes
                                        class="rounded border-white/30 bg-white/10 accent-khaki"
                                        @change="count += $event.target.checked ? 1 : -1">
                                </td>
                                <td class="px-4 py-3 font-medium">{{ $banknote->country?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-white/70">{{ $banknote->currency?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-white/70">{{ $banknote->nominalValue?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-white/70">{{ $banknote->year ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    @if($banknote->for_sale)
                                    <span class="inline-flex items-center rounded-full bg-emerald-500/20 px-2 py-0.5 text-xs text-emerald-200">Yes</span>
                                    @else
                                    <span class="text-white/40">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-white/50 text-xs">{{ $banknote->created_at->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('admin.banknotes.edit', $banknote) }}" class="rounded-md bg-white/10 px-3 py-1 text-xs hover:bg-white/20">Edit</a>
                                        <form method="POST" action="{{ route('admin.banknotes.destroy', $banknote) }}" onsubmit="return confirm('Delete this banknote?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-md bg-red-500/20 px-3 py-1 text-xs text-red-200 hover:bg-red-500/30">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="8" class="px-4 py-8 text-center text-white/40">No banknotes found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>
        </div>

        <div class="mt-4 text-white">
            {{ $banknotes->appends(request()->query())->links('pagination::tailwind') }}
        </div>
    </div>
    @endsection
