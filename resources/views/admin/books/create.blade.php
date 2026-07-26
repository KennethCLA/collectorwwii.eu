{{-- resources/views/admin/books/create.blade.php --}}

@extends('layouts.admin')

@section('admin-content')
        @php
        $isEdit = false;

        $bookData = $bookData ?? [];

        // old() > bookData > fallback
        $val = function (string $key, $fallback = '') use ($bookData) {
        return old($key, data_get($bookData, $key, $fallback));
        };

        // booleans (old() returns "0"/"1" sometimes)
        $forSaleOld = old('for_sale', data_get($bookData, 'for_sale', false));
        $forSaleJs = filter_var($forSaleOld, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
        @endphp

        <form id="book-form"
            action="{{ route('admin.books.store') }}"
            method="POST"
            enctype="multipart/form-data"
            class="w-full mx-auto max-w-7xl">
            @csrf

            {{-- Header --}}
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-white">Create book</h1>
                    <p class="mt-1 text-sm text-white/60">Add a new book to the collection.</p>
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
                        <span class="text-xs text-white/50">Visible on the public book page</span>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {{-- ISBN + Lookup (Open Library) --}}
                        <div class="lg:col-span-2 space-y-2">
                            <div class="flex items-center justify-between gap-3">
                                <label for="isbn" class="text-sm font-medium text-white/80">
                                    ISBN
                                </label>
                                <span class="text-xs text-white/50">Lookup fills fields (Open Library / Google Books)</span>
                            </div>

                            <div class="flex items-end gap-4">
                                <input
                                    type="text"
                                    id="isbn"
                                    name="isbn"
                                    value="{{ $val('isbn', $isbn ?? '') }}"
                                    placeholder="978..."
                                    class="flex-1 rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40
                                           focus:outline-none focus:ring-2 focus:ring-white/20" />

                                <button
                                    type="button"
                                    id="isbn-lookup-btn"
                                    class="shrink-0 rounded-md bg-white/10 px-4 py-2 text-sm font-medium text-white hover:bg-white/15">
                                    Search ISBN
                                </button>
                            </div>

                            @if(!empty($isbnLookupFailed))
                            <div class="text-sm text-red-200/90">
                                ISBN lookup failed. You can still fill everything manually.
                            </div>
                            @endif
                        </div>

                        {{-- Authors --}}
                        <div class="space-y-2">
                            <label for="authors" class="text-sm font-medium text-white/80">
                                Author(s) * <span class="text-white/50">(comma separated)</span>
                            </label>
                            <input id="authors" type="text"
                                name="authors"
                                value="{{ $val('authors') }}"
                                required
                                placeholder="e.g. John Doe, Jane Doe"
                                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40
                                          focus:outline-none focus:ring-2 focus:ring-white/20">
                        </div>

                        {{-- Title --}}
                        <div class="space-y-2">
                            <label for="title" class="text-sm font-medium text-white/80">Title *</label>
                            <input id="title" type="text"
                                name="title"
                                value="{{ $val('title') }}"
                                required
                                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40
                                          focus:outline-none focus:ring-2 focus:ring-white/20">
                        </div>

                        {{-- Subtitle --}}
                        <div class="space-y-2">
                            <label for="subtitle" class="text-sm font-medium text-white/80">Subtitle</label>
                            <input id="subtitle" type="text"
                                name="subtitle"
                                value="{{ $val('subtitle') }}"
                                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40
                                          focus:outline-none focus:ring-2 focus:ring-white/20">
                        </div>

                        {{-- Publisher --}}
                        <div class="space-y-2">
                            <label for="publisher_name" class="text-sm font-medium text-white/80">Publisher</label>
                            <input id="publisher_name" type="text"
                                name="publisher_name"
                                value="{{ $val('publisher_name') }}"
                                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40
                                          focus:outline-none focus:ring-2 focus:ring-white/20">
                        </div>

                        {{-- Year --}}
                        <div class="space-y-2">
                            <label for="copyright_year" class="text-sm font-medium text-white/80">Copyright year</label>
                            <input id="copyright_year" type="number"
                                name="copyright_year"
                                value="{{ $val('copyright_year') }}"
                                min="1000"
                                max="{{ date('Y') }}"
                                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40
                                          focus:outline-none focus:ring-2 focus:ring-white/20">
                        </div>

                        {{-- Pages --}}
                        <div class="space-y-2">
                            <label for="pages" class="text-sm font-medium text-white/80">Pages</label>
                            <input id="pages" type="number"
                                name="pages"
                                value="{{ $val('pages') }}"
                                min="1"
                                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40
                                          focus:outline-none focus:ring-2 focus:ring-white/20">
                        </div>

                        {{-- Topic --}}
                        <div class="space-y-2">
                            <label for="topic_id" class="text-sm font-medium text-white/80">Topic</label>

                            <div class="flex items-center gap-2">
                                <select id="topic_id" name="topic_id"
                                    class="js-select w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-white/20">
                                    <option value="">—</option>
                                    @foreach($topics as $t)
                                    <option value="{{ $t->id }}" @selected((string)$val('topic_id')===(string)$t->id)>{{ $t->name }}</option>
                                    @endforeach
                                </select>

                                <button type="button"
                                    class="h-10 w-10 shrink-0 rounded-md border border-white/10 bg-white/10 text-white hover:bg-white/15"
                                    data-lookup-add
                                    data-type="book-topics"
                                    data-select="#topic_id"
                                    title="Add topic">+</button>
                            </div>
                        </div>

                        {{-- Series --}}
                        <div class="space-y-2">
                            <label for="series_id" class="text-sm font-medium text-white/80">Series</label>

                            <div class="flex items-center gap-2">
                                <select id="series_id" name="series_id"
                                    class="js-select w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-white/20">
                                    <option value="">—</option>
                                    @foreach($series as $s)
                                    <option value="{{ $s->id }}" @selected((string)$val('series_id')===(string)$s->id)>{{ $s->name }}</option>
                                    @endforeach
                                </select>

                                <button type="button"
                                    class="h-10 w-10 shrink-0 rounded-md border border-white/10 bg-white/10 text-white hover:bg-white/15"
                                    data-lookup-add
                                    data-type="book-series"
                                    data-select="#series_id"
                                    title="Add series">+</button>
                            </div>
                        </div>

                        {{-- Series number --}}
                        <div class="space-y-2">
                            <label for="series_number" class="text-sm font-medium text-white/80">Series #</label>
                            <input id="series_number" type="text"
                                name="series_number"
                                value="{{ $val('series_number') }}"
                                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40
                                          focus:outline-none focus:ring-2 focus:ring-white/20">
                        </div>

                        {{-- Cover type --}}
                        <div class="space-y-2">
                            <label for="cover_id" class="text-sm font-medium text-white/80">Cover</label>

                            <div class="flex items-center gap-2">
                                <select id="cover_id" name="cover_id"
                                    class="js-select w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-white/20">
                                    <option value="">—</option>
                                    @foreach($covers as $c)
                                    <option value="{{ $c->id }}" @selected((string)$val('cover_id')===(string)$c->id)>{{ $c->name }}</option>
                                    @endforeach
                                </select>

                                <button type="button"
                                    class="h-10 w-10 shrink-0 rounded-md border border-white/10 bg-white/10 text-white hover:bg-white/15"
                                    data-lookup-add
                                    data-type="book-covers"
                                    data-select="#cover_id"
                                    title="Add cover">+</button>
                            </div>
                        </div>

                        {{-- Translator --}}
                        <div class="space-y-2">
                            <label for="translator" class="text-sm font-medium text-white/80">Translator</label>
                            <input id="translator" type="text"
                                name="translator"
                                value="{{ $val('translator') }}"
                                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40
                                          focus:outline-none focus:ring-2 focus:ring-white/20">
                        </div>

                        {{-- Issue number --}}
                        <div class="space-y-2">
                            <label for="issue_number" class="text-sm font-medium text-white/80">Issue #</label>
                            <input id="issue_number" type="text"
                                name="issue_number"
                                value="{{ $val('issue_number') }}"
                                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40
                                          focus:outline-none focus:ring-2 focus:ring-white/20">
                        </div>

                        {{-- Issue year --}}
                        <div class="space-y-2">
                            <label for="issue_year" class="text-sm font-medium text-white/80">Issue year</label>
                            <input id="issue_year" type="number"
                                name="issue_year"
                                value="{{ $val('issue_year') }}"
                                min="1000"
                                max="{{ date('Y') }}"
                                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40
                                          focus:outline-none focus:ring-2 focus:ring-white/20">
                        </div>

                        {{-- First edition title --}}
                        <div class="space-y-2">
                            <label for="title_first_edition" class="text-sm font-medium text-white/80">Title (1st ed.)</label>
                            <input id="title_first_edition" type="text"
                                name="title_first_edition"
                                value="{{ $val('title_first_edition') }}"
                                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40
                                          focus:outline-none focus:ring-2 focus:ring-white/20">
                        </div>

                        {{-- First edition subtitle --}}
                        <div class="space-y-2">
                            <label for="subtitle_first_edition" class="text-sm font-medium text-white/80">Subtitle (1st ed.)</label>
                            <input id="subtitle_first_edition" type="text"
                                name="subtitle_first_edition"
                                value="{{ $val('subtitle_first_edition') }}"
                                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40
                                          focus:outline-none focus:ring-2 focus:ring-white/20">
                        </div>

                        {{-- Publisher first issue --}}
                        <div class="space-y-2">
                            <label for="publisher_first_issue" class="text-sm font-medium text-white/80">Publisher (1st)</label>
                            <input id="publisher_first_issue" type="text"
                                name="publisher_first_issue"
                                value="{{ $val('publisher_first_issue') }}"
                                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40
                                          focus:outline-none focus:ring-2 focus:ring-white/20">
                        </div>

                        {{-- Copyright year first issue --}}
                        <div class="space-y-2">
                            <label for="copyright_year_first_issue" class="text-sm font-medium text-white/80">Copyright (1st)</label>
                            <input id="copyright_year_first_issue" type="number"
                                name="copyright_year_first_issue"
                                value="{{ $val('copyright_year_first_issue') }}"
                                min="1000"
                                max="{{ date('Y') }}"
                                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40
                                          focus:outline-none focus:ring-2 focus:ring-white/20">
                        </div>

                        {{-- Condition --}}
                        <div class="space-y-2">
                            <label for="condition" class="text-sm font-medium text-white/80">Condition</label>
                            <select id="condition" name="condition" class="js-select w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-white/20">
                                <option value="">— Not graded —</option>
                                @foreach(['Mint','Extremely Fine','Very Fine','Fine','Very Good','Good','Poor'] as $grade)
                                <option value="{{ $grade }}" @selected(old('condition') === $grade)>{{ $grade }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Description --}}
                    <div class="mt-6 space-y-2">
                        <label for="description" class="text-sm font-medium text-white/80">Description</label>
                        <textarea id="description" name="description"
                            rows="6"
                            class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40
                                         focus:outline-none focus:ring-2 focus:ring-white/20">{{ $val('description') }}</textarea>
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
                        {{-- Purchase date --}}
                        <div class="space-y-2">
                            <label for="purchase_date" class="text-sm font-medium text-white/80">Purchase date</label>
                            <input id="purchase_date" type="date"
                                name="purchase_date"
                                value="{{ old('purchase_date', data_get($bookData,'purchase_date') ? \Illuminate\Support\Carbon::parse(data_get($bookData,'purchase_date'))->format('Y-m-d') : '') }}"
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
                                    data-lookup-add
                                    data-type="origins"
                                    data-select="#origin_id"
                                    title="Add origin">+</button>
                            </div>
                        </div>

                        {{-- Storage location --}}
                        <div class="space-y-2">
                            <label for="location_id" class="text-sm font-medium text-white/80">Storage location</label>

                            <div class="flex items-center gap-2">
                                <select id="location_id" name="location_id"
                                    class="js-select w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-white/20">
                                    <option value="">—</option>
                                    @foreach($locations as $loc)
                                    <option value="{{ $loc->id }}" @selected((string)$val('location_id')===(string)$loc->id)>{{ $loc->name }}</option>
                                    @endforeach
                                </select>

                                <button type="button"
                                    class="h-10 w-10 shrink-0 rounded-md border border-white/10 bg-white/10 text-white hover:bg-white/15"
                                    data-lookup-add
                                    data-type="locations"
                                    data-select="#location_id"
                                    title="Add location">+</button>
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

                    {{-- Notes --}}
                    <div class="mt-6 space-y-2">
                        <label for="notes" class="text-sm font-medium text-white/80">Notes</label>
                        <textarea id="notes" name="notes"
                            rows="4"
                            class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40
                                         focus:outline-none focus:ring-2 focus:ring-white/20">{{ $val('notes') }}</textarea>
                    </div>

                    {{-- Weight + dimensions --}}
                    <div class="mt-6">
                        <div class="text-sm font-medium text-white/80 mb-2">Physical (optional)</div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div class="space-y-2">
                                <label for="weight" class="text-sm text-white/70">Weight (grams)</label>
                                <input id="weight" type="number" name="weight" value="{{ $val('weight') }}"
                                    class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-white/20">
                            </div>
                            <div class="space-y-2">
                                <label for="width" class="text-sm text-white/70">Width</label>
                                <input id="width" type="number" name="width" value="{{ $val('width') }}"
                                    class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-white/20">
                            </div>
                            <div class="space-y-2">
                                <label for="height" class="text-sm text-white/70">Height</label>
                                <input id="height" type="number" name="height" value="{{ $val('height') }}"
                                    class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-white/20">
                            </div>
                            <div class="space-y-2">
                                <label for="thickness" class="text-sm text-white/70">Thickness</label>
                                <input id="thickness" type="number" name="thickness" value="{{ $val('thickness') }}"
                                    class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-white/20">
                            </div>
                        </div>
                    </div>
                </section>

                @include('admin.partials.create-media-upload')

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('admin.dashboard') }}"
                        class="rounded-md bg-white/10 px-4 py-2 text-sm font-medium text-white hover:bg-white/15">
                        Cancel
                    </a>
                    <button type="submit" name="after_save" value="create"
                        class="rounded-md bg-white/10 px-4 py-2 text-sm font-semibold text-white hover:bg-white/15">
                        Save & add another
                    </button>
                    <button type="submit" name="after_save" value="show"
                        class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500">
                        Save book
                    </button>
                </div>
            </div>
        </form>

    {{-- ISBN lookup --}}
    <script>
        (function() {
            const btn = document.getElementById('isbn-lookup-btn');
            const input = document.getElementById('isbn');
            if (!btn || !input) return;
            btn.addEventListener('click', () => {
                const isbn = (input.value || '').trim();
                if (!isbn) {
                    input.focus();
                    return;
                }
                const url = new URL("{{ route('admin.books.create') }}", window.location.origin);
                url.searchParams.set('isbn', isbn);
                window.location.href = url.toString();
            });
        })();
    </script>

    @include('admin.partials.lookup-modal')
@endsection