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
                        {{-- ISBN + Lookup (Google Books) --}}
                        <div class="lg:col-span-2 space-y-2">
                            <div class="flex items-center justify-between gap-3">
                                <label for="isbn" class="text-sm font-medium text-white/80">
                                    ISBN
                                </label>
                                <span class="text-xs text-white/50">Lookup fills fields (Google Books)</span>
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
                                    class="h-10 w-10 mb-6 shrink-0 rounded-md border border-white/10 bg-white/10 text-white hover:bg-white/15"
                                    data-lookup-add
                                    data-type="topic"
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
                                    class="h-10 w-10 mb-6 shrink-0 rounded-md border border-white/10 bg-white/10 text-white hover:bg-white/15"
                                    data-lookup-add
                                    data-type="series"
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
                                    class="h-10 w-10 mb-6 shrink-0 rounded-md border border-white/10 bg-white/10 text-white hover:bg-white/15"
                                    data-lookup-add
                                    data-type="cover"
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
                                    class="h-10 w-10 mb-6 shrink-0 rounded-md border border-white/10 bg-white/10 text-white hover:bg-white/15"
                                    data-lookup-add
                                    data-type="origin"
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
                                    class="h-10 w-10 mb-6 shrink-0 rounded-md border border-white/10 bg-white/10 text-white hover:bg-white/15"
                                    data-lookup-add
                                    data-type="location"
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

                {{-- MEDIA (CREATE) --}}
                <section class="rounded-xl border border-black/20 bg-black/10 p-6 space-y-6">
                    <div class="flex items-center justify-between gap-4">
                        <h2 class="text-base font-semibold text-white">Media</h2>
                        <span class="text-xs text-white/50">Upload now — set "Main" before saving</span>
                    </div>

                    <input type="hidden" name="main_image_index" id="main_image_index" value="0">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="rounded-md bg-sage-900 p-4 border border-white/10">
                            <div class="text-white font-semibold mb-2">Images</div>
                            <input type="file" id="images_input" name="images[]" multiple accept="image/*"
                                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white file:mr-3 file:rounded-md file:border-0 file:bg-white/10 file:px-3 file:py-2 file:text-white hover:file:bg-white/15">
                            <p class="mt-2 text-xs text-white/60">First image becomes <span class="text-white/80 font-semibold">Main</span> by default.</p>
                        </div>
                        <div class="rounded-md bg-sage-900 p-4 border border-white/10">
                            <div class="text-white font-semibold mb-2">PDFs</div>
                            <input type="file" id="pdfs_input" name="pdfs[]" multiple accept="application/pdf"
                                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white file:mr-3 file:rounded-md file:border-0 file:bg-white/10 file:px-3 file:py-2 file:text-white hover:file:bg-white/15">
                            <p class="mt-2 text-xs text-white/60">Max 50MB per file.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="bg-sage rounded-md p-4 border border-black/20">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-lg font-semibold text-white">Selected images</h3>
                                <span id="images_count" class="text-xs text-white/60">0</span>
                            </div>
                            <div id="images_preview" class="flex flex-wrap gap-2 items-start">
                                <p class="text-white/80 text-sm" id="images_empty">No images selected.</p>
                            </div>
                        </div>
                        <div class="bg-sage rounded-md p-4 border border-black/20">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-lg font-semibold text-white">Selected PDFs</h3>
                                <span id="pdfs_count" class="text-xs text-white/60">0</span>
                            </div>
                            <div id="pdfs_preview" class="space-y-3">
                                <p class="text-white/80 text-sm" id="pdfs_empty">No PDFs selected.</p>
                            </div>
                        </div>
                    </div>
                </section>

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

    {{-- Media preview --}}
    <script>
        (function() {
            const imagesInput = document.getElementById('images_input');
            const pdfsInput = document.getElementById('pdfs_input');
            const imagesPreview = document.getElementById('images_preview');
            const pdfsPreview = document.getElementById('pdfs_preview');
            const imagesEmpty = document.getElementById('images_empty');
            const pdfsEmpty = document.getElementById('pdfs_empty');
            const imagesCount = document.getElementById('images_count');
            const pdfsCount = document.getElementById('pdfs_count');
            const mainIndexHidden = document.getElementById('main_image_index');

            let imageFiles = [];
            let pdfFiles = [];

            function humanSize(bytes) {
                if (!bytes && bytes !== 0) return '—';
                const units = ['B', 'KB', 'MB', 'GB'];
                let i = 0,
                    n = bytes;
                while (n >= 1024 && i < units.length - 1) {
                    n /= 1024;
                    i++;
                }
                return `${n.toFixed(i === 0 ? 0 : 1)} ${units[i]}`;
            }

            function syncFileList(inputEl, filesArr) {
                const dt = new DataTransfer();
                filesArr.forEach(f => dt.items.add(f));
                inputEl.files = dt.files;
            }

            function renderImages() {
                imagesPreview.innerHTML = '';
                imagesCount.textContent = `${imageFiles.length}`;
                if (imageFiles.length === 0) {
                    imagesEmpty.style.display = '';
                    imagesPreview.appendChild(imagesEmpty);
                    mainIndexHidden.value = '0';
                    return;
                }
                imagesEmpty.style.display = 'none';

                let mainIndex = parseInt(mainIndexHidden.value || '0', 10);
                if (Number.isNaN(mainIndex) || mainIndex < 0) mainIndex = 0;
                if (mainIndex > imageFiles.length - 1) mainIndex = 0;
                mainIndexHidden.value = String(mainIndex);

                imageFiles.forEach((file, idx) => {
                    const card = document.createElement('div');
                    card.className = 'group w-32 shrink-0 rounded-md bg-sage-900 border border-white/10 overflow-hidden';
                    const url = URL.createObjectURL(file);

                    const preview = document.createElement('div');
                    preview.className = 'w-32 h-44 bg-black/10 flex items-center justify-center overflow-hidden';
                    const img = document.createElement('img');
                    img.src = url;
                    img.alt = file.name;
                    img.loading = 'lazy';
                    img.className = 'w-full h-full object-contain block';
                    img.addEventListener('load', () => URL.revokeObjectURL(url));
                    img.addEventListener('error', () => URL.revokeObjectURL(url));
                    preview.appendChild(img);

                    const footer = document.createElement('div');
                    footer.className = 'p-2 space-y-2';

                    const name = document.createElement('div');
                    name.className = 'text-[11px] text-white/80 truncate';
                    name.textContent = file.name;

                    const meta = document.createElement('div');
                    meta.className = 'text-[10px] text-white/50';
                    meta.textContent = humanSize(file.size);

                    const actions = document.createElement('div');
                    actions.className = 'grid grid-cols-2 gap-2 items-center';

                    const isMain = idx === parseInt(mainIndexHidden.value || '0', 10);
                    const mainBtn = document.createElement('button');
                    mainBtn.type = 'button';
                    mainBtn.className = isMain ?
                        'h-7 rounded bg-white/15 text-white text-[10px] font-semibold' :
                        'h-7 rounded bg-white/10 text-white text-[10px] hover:bg-white/20 transition';
                    mainBtn.textContent = isMain ? 'Main' : 'Set main';
                    mainBtn.addEventListener('click', () => {
                        mainIndexHidden.value = String(idx);
                        renderImages();
                    });

                    const delBtn = document.createElement('button');
                    delBtn.type = 'button';
                    delBtn.className = 'h-7 rounded bg-red-600 text-white text-[10px] hover:bg-red-700 transition';
                    delBtn.textContent = 'Remove';
                    delBtn.addEventListener('click', () => {
                        imageFiles.splice(idx, 1);
                        let mi = parseInt(mainIndexHidden.value || '0', 10);
                        if (idx < mi) mi--;
                        if (mi < 0) mi = 0;
                        if (mi > imageFiles.length - 1) mi = 0;
                        mainIndexHidden.value = String(mi);
                        syncFileList(imagesInput, imageFiles);
                        renderImages();
                    });

                    actions.appendChild(mainBtn);
                    actions.appendChild(delBtn);
                    footer.appendChild(name);
                    footer.appendChild(meta);
                    footer.appendChild(actions);
                    card.appendChild(preview);
                    card.appendChild(footer);
                    imagesPreview.appendChild(card);
                });
            }

            function renderPdfs() {
                pdfsPreview.innerHTML = '';
                pdfsCount.textContent = `${pdfFiles.length}`;
                if (pdfFiles.length === 0) {
                    pdfsEmpty.style.display = '';
                    pdfsPreview.appendChild(pdfsEmpty);
                    return;
                }
                pdfsEmpty.style.display = 'none';

                pdfFiles.forEach((file, idx) => {
                    const row = document.createElement('div');
                    row.className = 'rounded-md bg-sage-900 border border-white/10 p-3 flex items-center justify-between gap-4';

                    const left = document.createElement('div');
                    left.className = 'min-w-0';
                    const name = document.createElement('div');
                    name.className = 'text-white font-semibold text-sm truncate';
                    name.textContent = file.name;
                    const meta = document.createElement('div');
                    meta.className = 'text-[11px] text-white/50 mt-1';
                    meta.textContent = humanSize(file.size);
                    left.appendChild(name);
                    left.appendChild(meta);

                    const right = document.createElement('div');
                    right.className = 'shrink-0 flex items-center gap-2';

                    const openBtn = document.createElement('button');
                    openBtn.type = 'button';
                    openBtn.className = 'h-7 rounded bg-white/10 px-3 text-[10px] text-white hover:bg-white/20 transition';
                    openBtn.textContent = 'Open';
                    openBtn.addEventListener('click', () => window.open(URL.createObjectURL(file), '_blank', 'noopener'));

                    const delBtn = document.createElement('button');
                    delBtn.type = 'button';
                    delBtn.className = 'h-7 rounded bg-red-600 px-3 text-[10px] text-white hover:bg-red-700 transition';
                    delBtn.textContent = 'Remove';
                    delBtn.addEventListener('click', () => {
                        pdfFiles.splice(idx, 1);
                        syncFileList(pdfsInput, pdfFiles);
                        renderPdfs();
                    });

                    right.appendChild(openBtn);
                    right.appendChild(delBtn);
                    row.appendChild(left);
                    row.appendChild(right);
                    pdfsPreview.appendChild(row);
                });
            }

            if (imagesInput) {
                imagesInput.addEventListener('change', () => {
                    imageFiles = Array.from(imagesInput.files || []);
                    mainIndexHidden.value = '0';
                    renderImages();
                });
            }
            if (pdfsInput) {
                pdfsInput.addEventListener('change', () => {
                    pdfFiles = Array.from(pdfsInput.files || []);
                    renderPdfs();
                });
            }

            renderImages();
            renderPdfs();
        })();
    </script>

    @include('admin.partials.lookup-modal')
@endsection