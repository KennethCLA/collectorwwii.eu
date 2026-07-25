{{-- resources/views/admin/items/create.blade.php --}}

@extends('layouts.admin')

@section('admin-content')
        @php
        $val = function (string $key, $fallback = '') {
            return old($key, $fallback);
        };

        $forSaleOld = old('for_sale', false);
        $forSaleJs = filter_var($forSaleOld, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
        @endphp

        <form id="item-form"
            action="{{ route('admin.items.store') }}"
            method="POST"
            class="w-full mx-auto max-w-7xl">
            @csrf

            {{-- Header --}}
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-white">Create item</h1>
                    <p class="mt-1 text-sm text-white/60">Add a new item to the collection.</p>
                </div>

                <a href="{{ route('admin.dashboard') }}"
                    class="rounded-md bg-white/10 px-4 py-2 text-sm font-medium text-white hover:bg-white/15">
                    Back
                </a>
            </div>

            {{-- Errors --}}
            @if ($errors->any())
            <div class="mb-6 rounded-lg border border-red-500/30 bg-red-500/10 p-4 text-sm text-red-100">
                <div class="font-semibold mb-2">Please fix the following:</div>
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- MAIN --}}
            <div class="rounded-xl border border-black/20 bg-black/10 p-6 space-y-8">

                {{-- PUBLIC FIELDS --}}
                <section class="rounded-xl border border-black/20 bg-black/10 p-6">
                    <div class="flex items-center justify-between gap-4 mb-5">
                        <h2 class="text-base font-semibold text-white">Public details</h2>
                        <span class="text-xs text-white/50">Visible on the public item page</span>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {{-- Title --}}
                        <div class="lg:col-span-2 space-y-2">
                            <label for="title" class="text-sm font-medium text-white/80">Title *</label>
                            <input id="title" type="text"
                                name="title"
                                value="{{ $val('title') }}"
                                required
                                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40
                                          focus:outline-none focus:ring-2 focus:ring-white/20">
                        </div>

                        {{-- Description --}}
                        <div class="lg:col-span-2 space-y-2">
                            <label for="description" class="text-sm font-medium text-white/80">Description</label>
                            <textarea id="description" name="description"
                                rows="4"
                                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40
                                             focus:outline-none focus:ring-2 focus:ring-white/20">{{ $val('description') }}</textarea>
                        </div>

                        {{-- Category --}}
                        <div class="space-y-2">
                            <label for="category_id" class="text-sm font-medium text-white/80">Category</label>
                            <div class="flex items-center gap-2">
                                <select id="category_id" name="category_id"
                                    class="js-select w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-white/20">
                                    <option value="">—</option>
                                    @foreach($categories as $c)
                                    <option value="{{ $c->id }}" @selected((string)$val('category_id')===(string)$c->id)>{{ $c->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button"
                                    class="h-10 w-10 shrink-0 rounded-md border border-white/10 bg-white/10 text-white hover:bg-white/15"
                                    data-lookup-add data-type="item-category" data-select="#category_id"
                                    title="Add category">+</button>
                            </div>
                        </div>

                        {{-- Nationality --}}
                        <div class="space-y-2">
                            <label for="nationality_id" class="text-sm font-medium text-white/80">Nationality</label>
                            <select id="nationality_id" name="nationality_id"
                                class="js-select w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-white/20">
                                <option value="">—</option>
                                @foreach($nationalities as $n)
                                <option value="{{ $n->id }}" @selected((string)$val('nationality_id')===(string)$n->id)>{{ $n->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Organization --}}
                        <div class="space-y-2">
                            <label for="organization_id" class="text-sm font-medium text-white/80">Organization</label>
                            <div class="flex items-center gap-2">
                                <select id="organization_id" name="organization_id"
                                    class="js-select w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-white/20">
                                    <option value="">—</option>
                                    @foreach($organizations as $o)
                                    <option value="{{ $o->id }}" @selected((string)$val('organization_id')===(string)$o->id)>{{ $o->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button"
                                    class="h-10 w-10 shrink-0 rounded-md border border-white/10 bg-white/10 text-white hover:bg-white/15"
                                    data-lookup-add data-type="item-organization" data-select="#organization_id"
                                    title="Add organization">+</button>
                            </div>
                        </div>

                        {{-- For sale --}}
                        <div x-data="{ forSale: {{ $forSaleJs }} }" class="space-y-2">
                            <label class="text-sm font-medium text-white/80">For sale</label>

                            <div class="flex items-center gap-3">
                                <input type="hidden" name="for_sale" value="0">
                                <input id="for_sale" type="checkbox"
                                    name="for_sale"
                                    value="1"
                                    x-model="forSale"
                                    class="h-5 w-5 rounded border-white/20 bg-white/10">
                                <span class="text-sm text-white/70">Mark as for sale</span>
                            </div>

                            <div x-show="forSale" x-cloak class="pt-2">
                                <label for="selling_price" class="text-sm font-medium text-white/80">Selling price €</label>
                                <input id="selling_price" type="number"
                                    step="0.01"
                                    name="selling_price"
                                    value="{{ $val('selling_price') }}"
                                    class="mt-2 w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40
                                              focus:outline-none focus:ring-2 focus:ring-white/20">
                            </div>
                        </div>
                    </div>
                </section>

                {{-- ADMIN-ONLY FIELDS --}}
                <section class="rounded-xl border border-white/10 bg-black/20 p-6">
                    <div class="flex items-center justify-between gap-4 mb-5">
                        <div class="flex items-center gap-3">
                            <h2 class="text-base font-semibold text-white">Admin-only</h2>
                            <span class="inline-flex items-center rounded-full bg-white/10 px-2 py-0.5 text-xs text-white/70">
                                Not visible publicly
                            </span>
                        </div>
                        <span class="text-xs text-white/50">Pricing, storage, internal notes</span>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {{-- Purchase origin --}}
                        <div class="space-y-2">
                            <label for="origin_id" class="text-sm font-medium text-white/80">Purchase origin</label>
                            <div class="flex items-center gap-2">
                                <select id="origin_id" name="origin_id"
                                    class="js-select w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-white/20">
                                    <option value="">—</option>
                                    @foreach($origins as $o)
                                    <option value="{{ $o->id }}" @selected((string)$val('origin_id')===(string)$o->id)>{{ $o->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button"
                                    class="h-10 w-10 shrink-0 rounded-md border border-white/10 bg-white/10 text-white hover:bg-white/15"
                                    data-lookup-add data-type="origin" data-select="#origin_id"
                                    title="Add origin">+</button>
                            </div>
                        </div>

                        {{-- Purchase date --}}
                        <div class="space-y-2">
                            <label for="purchase_date" class="text-sm font-medium text-white/80">Purchase date</label>
                            <input id="purchase_date" type="date"
                                name="purchase_date"
                                value="{{ $val('purchase_date') }}"
                                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white
                                          focus:outline-none focus:ring-2 focus:ring-white/20">
                        </div>

                        {{-- Purchase price --}}
                        <div class="space-y-2">
                            <label for="purchase_price" class="text-sm font-medium text-white/80">Purchase €</label>
                            <input id="purchase_price" type="number"
                                step="0.01"
                                name="purchase_price"
                                value="{{ $val('purchase_price') }}"
                                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40
                                          focus:outline-none focus:ring-2 focus:ring-white/20">
                        </div>

                        {{-- Purchase location --}}
                        <div class="space-y-2">
                            <label for="purchase_location" class="text-sm font-medium text-white/80">Purchase location</label>
                            <input id="purchase_location" type="text"
                                name="purchase_location"
                                value="{{ $val('purchase_location') }}"
                                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40
                                          focus:outline-none focus:ring-2 focus:ring-white/20">
                        </div>

                        {{-- Storage location --}}
                        <div class="space-y-2">
                            <label for="storage_location" class="text-sm font-medium text-white/80">Storage location</label>
                            <input id="storage_location" type="text"
                                name="storage_location"
                                value="{{ $val('storage_location') }}"
                                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40
                                          focus:outline-none focus:ring-2 focus:ring-white/20">
                        </div>

                        {{-- Notes --}}
                        <div class="space-y-2">
                            <label for="notes" class="text-sm font-medium text-white/80">Notes</label>
                            <textarea id="notes" name="notes"
                                rows="4"
                                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40
                                             focus:outline-none focus:ring-2 focus:ring-white/20">{{ $val('notes') }}</textarea>
                        </div>
                    </div>
                </section>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('admin.dashboard') }}"
                        class="rounded-md bg-white/10 px-4 py-2 text-sm font-medium text-white hover:bg-white/15">
                        Cancel
                    </a>
                    <button type="submit"
                        class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500">
                        Save item
                    </button>
                </div>
            </div>
        </form>

    @include('admin.partials.lookup-modal')
@endsection
