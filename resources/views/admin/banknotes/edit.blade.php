{{-- resources/views/admin/banknotes/edit.blade.php --}}

@extends('layouts.admin')

@section('admin-content')
        @php
        $val = fn(string $key, $fallback = '') => old($key, data_get($banknote, $key, $fallback));
        $forSaleOld = old('for_sale', $banknote->for_sale ?? false);
        $forSaleJs = filter_var($forSaleOld, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
        @endphp

        <form id="banknote-form" action="{{ route('admin.banknotes.update', $banknote) }}" method="POST"
            class="w-full mx-auto max-w-7xl">
            @csrf
            @method('PUT')

            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-white">Edit banknote</h1>
                    <p class="mt-1 text-sm text-white/60">Update the banknote details.</p>
                </div>
                <a href="{{ route('admin.dashboard') }}"
                    class="rounded-md bg-white/10 px-4 py-2 text-sm font-medium text-white hover:bg-white/15">Back</a>
            </div>

            @if(session('success'))
            <div class="mb-4 rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-100">
                {{ session('success') }}
            </div>
            @endif

            @if ($errors->any())
            <div class="mb-6 rounded-lg border border-red-500/30 bg-red-500/10 p-4 text-sm text-red-100">
                <div class="font-semibold mb-2">Please fix the following:</div>
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
            @endif

            <div class="rounded-xl border border-black/20 bg-black/10 p-6 space-y-8">

                {{-- PUBLIC FIELDS --}}
                <section class="rounded-xl border border-black/20 bg-black/10 p-6">
                    <div class="flex items-center justify-between gap-4 mb-5">
                        <h2 class="text-base font-semibold text-white">Public details</h2>
                        <span class="text-xs text-white/50">Visible on the public page</span>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label for="country_id" class="text-sm font-medium text-white/80">Country</label>
                            <select id="country_id" name="country_id"
                                class="js-select w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-white/20">
                                <option value="">—</option>
                                @foreach($countries as $c)
                                <option value="{{ $c->id }}" @selected((string)$val('country_id') === (string)$c->id)>{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label for="currency_id" class="text-sm font-medium text-white/80">Currency</label>
                            <select id="currency_id" name="currency_id"
                                class="js-select w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-white/20">
                                <option value="">—</option>
                                @foreach($currencies as $c)
                                <option value="{{ $c->id }}" @selected((string)$val('currency_id') === (string)$c->id)>{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label for="nominal_value_id" class="text-sm font-medium text-white/80">Nominal value</label>
                            <select id="nominal_value_id" name="nominal_value_id"
                                class="js-select w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-white/20">
                                <option value="">—</option>
                                @foreach($nominalValues as $nv)
                                <option value="{{ $nv->id }}" @selected((string)$val('nominal_value_id') === (string)$nv->id)>{{ $nv->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label for="series_id" class="text-sm font-medium text-white/80">Series</label>
                            <div class="flex items-center gap-2">
                                <select id="series_id" name="series_id"
                                    class="js-select w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-white/20">
                                    <option value="">—</option>
                                    @foreach($seriesList as $s)
                                    <option value="{{ $s->id }}" @selected((string)$val('series_id') === (string)$s->id)>{{ $s->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button"
                                    class="h-10 w-10 shrink-0 rounded-md border border-white/10 bg-white/10 text-white hover:bg-white/15"
                                    data-lookup-add data-type="banknote-series" data-select="#series_id"
                                    title="Add series">+</button>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label for="time_period_id" class="text-sm font-medium text-white/80">Time period</label>
                            <select id="time_period_id" name="time_period_id"
                                class="js-select w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-white/20">
                                <option value="">—</option>
                                @foreach($timePeriods as $tp)
                                <option value="{{ $tp->id }}" @selected((string)$val('time_period_id') === (string)$tp->id)>{{ $tp->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label for="year" class="text-sm font-medium text-white/80">Year</label>
                            <input id="year" type="number" name="year" value="{{ $val('year') }}"
                                min="1800" max="{{ date('Y') + 1 }}"
                                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-white/20">
                        </div>

                        <div class="space-y-2">
                            <label for="variation" class="text-sm font-medium text-white/80">Variation</label>
                            <input id="variation" type="text" name="variation" value="{{ $val('variation') }}"
                                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-white/20">
                        </div>

                        <div class="space-y-2">
                            <label for="head_of_state_id" class="text-sm font-medium text-white/80">Head of state</label>
                            <select id="head_of_state_id" name="head_of_state_id" class="js-select w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-white/20">
                                <option value="">—</option>
                                @foreach($headsOfState as $h)
                                <option value="{{ $h->id }}" @selected((string)$val('head_of_state_id') === (string)$h->id)>{{ $h->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label for="colour_id" class="text-sm font-medium text-white/80">Colour</label>
                            <select id="colour_id" name="colour_id" class="js-select w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-white/20">
                                <option value="">—</option>
                                @foreach($colours as $col)
                                <option value="{{ $col->id }}" @selected((string)$val('colour_id') === (string)$col->id)>{{ $col->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label for="designer_id" class="text-sm font-medium text-white/80">Designer</label>
                            <select id="designer_id" name="designer_id" class="js-select w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-white/20">
                                <option value="">—</option>
                                @foreach($designers as $d)
                                <option value="{{ $d->id }}" @selected((string)$val('designer_id') === (string)$d->id)>{{ $d->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label for="watermark_id" class="text-sm font-medium text-white/80">Watermark</label>
                            <select id="watermark_id" name="watermark_id" class="js-select w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-white/20">
                                <option value="">—</option>
                                @foreach($watermarks as $w)
                                <option value="{{ $w->id }}" @selected((string)$val('watermark_id') === (string)$w->id)>{{ $w->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label for="date_of_issue" class="text-sm font-medium text-white/80">Date of issue</label>
                            <input id="date_of_issue" type="date" name="date_of_issue" value="{{ old('date_of_issue', $banknote->date_of_issue?->format('Y-m-d') ?? $banknote->date_of_issue ?? '') }}"
                                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-white/20">
                        </div>

                        <div class="space-y-2">
                            <label for="number_on_note" class="text-sm font-medium text-white/80">Number on note</label>
                            <input id="number_on_note" type="text" name="number_on_note" value="{{ $val('number_on_note') }}"
                                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-white/20">
                        </div>

                        <div class="space-y-2">
                            <label for="number_jaeger" class="text-sm font-medium text-white/80">Number Jaeger</label>
                            <input id="number_jaeger" type="text" name="number_jaeger" value="{{ $val('number_jaeger') }}"
                                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-white/20">
                        </div>

                        <div class="space-y-2 lg:col-span-2">
                            <label for="special_features" class="text-sm font-medium text-white/80">Special features</label>
                            <textarea id="special_features" name="special_features" rows="3"
                                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-white/20">{{ old('special_features', $banknote->special_features) }}</textarea>
                        </div>

                        <div class="space-y-2 lg:col-span-2">
                            <label for="front_image" class="text-sm font-medium text-white/80">Front image description</label>
                            <textarea id="front_image" name="front_image" rows="3"
                                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-white/20">{{ old('front_image', $banknote->front_image) }}</textarea>
                        </div>

                        <div class="space-y-2 lg:col-span-2">
                            <label for="front_text" class="text-sm font-medium text-white/80">Front text</label>
                            <textarea id="front_text" name="front_text" rows="3"
                                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-white/20">{{ old('front_text', $banknote->front_text) }}</textarea>
                        </div>

                        <div class="space-y-2 lg:col-span-2">
                            <label for="reverse_image" class="text-sm font-medium text-white/80">Reverse image description</label>
                            <textarea id="reverse_image" name="reverse_image" rows="3"
                                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-white/20">{{ old('reverse_image', $banknote->reverse_image) }}</textarea>
                        </div>

                        <div class="space-y-2 lg:col-span-2">
                            <label for="reverse_text" class="text-sm font-medium text-white/80">Reverse text</label>
                            <textarea id="reverse_text" name="reverse_text" rows="3"
                                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-white/20">{{ old('reverse_text', $banknote->reverse_text) }}</textarea>
                        </div>

                        <div class="space-y-2">
                            <label for="width" class="text-sm font-medium text-white/80">Width mm</label>
                            <input id="width" type="number" name="width" value="{{ $val('width') }}" min="0"
                                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-white/20">
                        </div>

                        <div class="space-y-2">
                            <label for="height" class="text-sm font-medium text-white/80">Height mm</label>
                            <input id="height" type="number" name="height" value="{{ $val('height') }}" min="0"
                                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-white/20">
                        </div>

                        <div class="space-y-2">
                            <label for="print_run" class="text-sm font-medium text-white/80">Print run</label>
                            <input id="print_run" type="number" name="print_run" value="{{ $val('print_run') }}" min="0"
                                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-white/20">
                        </div>

                        {{-- Condition --}}
                        <div class="space-y-2">
                            <label for="condition" class="text-sm font-medium text-white/80">Condition</label>
                            <select id="condition" name="condition" class="js-select w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-white/20">
                                <option value="">— Not graded —</option>
                                @foreach(['Mint','Extremely Fine','Very Fine','Fine','Very Good','Good','Poor'] as $grade)
                                <option value="{{ $grade }}" @selected(old('condition', $banknote->condition ?? '') === $grade)>{{ $grade }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </section>

                {{-- ADMIN-ONLY FIELDS --}}
                <section class="rounded-xl border border-white/10 bg-black/20 p-6">
                    <div class="flex items-center gap-3 mb-5">
                        <h2 class="text-base font-semibold text-white">Admin-only</h2>
                        <span class="inline-flex items-center rounded-full bg-white/10 px-2 py-0.5 text-xs text-white/70">Not visible publicly</span>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label for="purchase_date" class="text-sm font-medium text-white/80">Purchase date</label>
                            <input id="purchase_date" type="date" name="purchase_date"
                                value="{{ old('purchase_date', $banknote->purchase_date?->format('Y-m-d') ?? '') }}"
                                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-white/20">
                        </div>

                        <div class="space-y-2">
                            <label for="purchasing_price" class="text-sm font-medium text-white/80">Purchasing price €</label>
                            <input id="purchasing_price" type="number" step="0.01" name="purchasing_price" value="{{ $val('purchasing_price') }}"
                                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-white/20">
                        </div>

                        <div class="space-y-2">
                            <label for="current_value" class="text-sm font-medium text-white/80">Current value €</label>
                            <input id="current_value" type="number" step="0.01" name="current_value" value="{{ $val('current_value') }}"
                                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-white/20">
                        </div>

                        <div class="space-y-2">
                            <label for="location_id" class="text-sm font-medium text-white/80">Location</label>
                            <div class="flex items-center gap-2">
                                <select id="location_id" name="location_id"
                                    class="js-select w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-white/20">
                                    <option value="">—</option>
                                    @foreach($locations as $loc)
                                    <option value="{{ $loc->id }}" @selected((string)$val('location_id') === (string)$loc->id)>{{ $loc->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button"
                                    class="h-10 w-10 shrink-0 rounded-md border border-white/10 bg-white/10 text-white hover:bg-white/15"
                                    data-lookup-add data-type="location" data-select="#location_id"
                                    title="Add location">+</button>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label for="location_detail" class="text-sm font-medium text-white/80">Location detail</label>
                            <input id="location_detail" type="text" name="location_detail" value="{{ $val('location_detail') }}"
                                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-white/20">
                        </div>

                        <div x-data="{ forSale: {{ $forSaleJs }} }" class="space-y-2">
                            <label class="text-sm font-medium text-white/80">For sale</label>
                            <div class="flex items-center gap-3">
                                <input type="hidden" name="for_sale" value="0">
                                <input id="for_sale" type="checkbox" name="for_sale" value="1" x-model="forSale"
                                    class="h-5 w-5 rounded border-white/20 bg-white/10">
                                <span class="text-sm text-white/70">Mark as for sale</span>
                            </div>
                            <div x-show="forSale" x-cloak class="pt-2">
                                <label for="selling_price" class="text-sm font-medium text-white/80">Selling price €</label>
                                <input id="selling_price" type="number" step="0.01" name="selling_price" value="{{ $val('selling_price') }}"
                                    class="mt-2 w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-white/20">
                            </div>
                        </div>

                        {{-- Sold --}}
                        <div x-data="{ sold: {{ old('sold_at', $banknote->sold_at ?? null) ? 'true' : 'false' }} }" class="space-y-2">
                            <label class="text-sm font-medium text-white/80">Sold</label>
                            <div class="flex items-center gap-3">
                                <input type="hidden" name="sold" value="0">
                                <input type="checkbox" value="1" x-model="sold"
                                    class="h-5 w-5 rounded border-white/20 bg-white/10">
                                <span class="text-sm text-white/70">Mark as sold</span>
                            </div>
                            <div x-show="sold" x-cloak class="grid grid-cols-2 gap-3 pt-2">
                                <div>
                                    <label for="sold_at" class="text-sm font-medium text-white/80">Sold on</label>
                                    <input id="sold_at" type="date" name="sold_at"
                                        value="{{ old('sold_at', $banknote->sold_at?->format('Y-m-d') ?? '') }}"
                                        class="mt-1 w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-white/20">
                                </div>
                                <div>
                                    <label for="sold_price" class="text-sm font-medium text-white/80">Sold price €</label>
                                    <input id="sold_price" type="number" step="0.01" name="sold_price"
                                        value="{{ old('sold_price', $banknote->sold_price ?? '') }}"
                                        class="mt-1 w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-white/20">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 space-y-2">
                        <label for="personal_remarks" class="text-sm font-medium text-white/80">Personal remarks</label>
                        <textarea id="personal_remarks" name="personal_remarks" rows="4"
                            class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-white/20">{{ old('personal_remarks', $banknote->personal_remarks) }}</textarea>
                    </div>
                </section>

        </form>

                {{-- MEDIA --}}
                <section class="rounded-xl border border-black/20 bg-black/10 p-6 space-y-6">
                    <div class="flex items-center justify-between gap-4">
                        <h2 class="text-base font-semibold text-white">Media</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="rounded-md bg-sage-900 p-4 border border-white/10">
                            <div class="text-white font-semibold mb-2">Upload images</div>
                            <form action="{{ route('admin.media.store', ['type' => 'banknotes', 'id' => $banknote->id]) }}"
                                method="POST" enctype="multipart/form-data" class="flex flex-col gap-3">
                                @csrf
                                <input type="hidden" name="collection" value="images">
                                <input type="file" name="files[]" multiple accept="image/*"
                                    class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white file:mr-3 file:rounded-md file:border-0 file:bg-white/10 file:px-3 file:py-2 file:text-white hover:file:bg-white/15">
                                <button type="submit"
                                    class="rounded-md bg-white/10 px-4 py-2 text-sm font-medium text-white hover:bg-white/15">Upload images</button>
                            </form>
                        </div>

                        <div class="rounded-md bg-sage-900 p-4 border border-white/10">
                            <div class="text-white font-semibold mb-2">Upload PDFs</div>
                            <form action="{{ route('admin.media.store', ['type' => 'banknotes', 'id' => $banknote->id]) }}"
                                method="POST" enctype="multipart/form-data" class="flex flex-col gap-3">
                                @csrf
                                <input type="hidden" name="collection" value="files">
                                <input type="file" name="files[]" multiple accept="application/pdf"
                                    class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white file:mr-3 file:rounded-md file:border-0 file:bg-white/10 file:px-3 file:py-2 file:text-white hover:file:bg-white/15">
                                <button type="submit"
                                    class="rounded-md bg-white/10 px-4 py-2 text-sm font-medium text-white hover:bg-white/15">Upload PDFs</button>
                            </form>
                        </div>
                    </div>

                    <p class="text-xs text-white/50">Max 50MB per file.</p>

                    @php
                    $mediaImages = $banknote->images;
                    $mediaPdfs = $banknote->files->filter(fn($f) => $f->isPdf())->values();
                    @endphp

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="bg-sage rounded-md p-4 border border-black/20">
                            <h3 class="text-lg font-semibold text-white mb-3">Images ({{ $mediaImages->count() }})</h3>
                            @if($mediaImages->isEmpty())
                            <p class="text-white/80 text-sm">No images uploaded yet.</p>
                            @else
                            <div class="flex flex-wrap gap-2 items-start"
                                data-reorder-container
                                data-reorder-url="{{ route('admin.media.reorder', ['type' => 'banknotes', 'id' => $banknote->id]) }}">
                                @foreach($mediaImages as $img)
                                @include('admin.books._image-card', ['img' => $img, 'type' => 'banknotes'])
                                @endforeach
                            </div>
                            @endif
                        </div>

                        <div class="bg-sage rounded-md p-4 border border-black/20">
                            <h3 class="text-lg font-semibold text-white mb-3">PDFs ({{ $mediaPdfs->count() }})</h3>
                            @if($mediaPdfs->isEmpty())
                            <p class="text-white/80 text-sm">No PDFs uploaded yet.</p>
                            @else
                            <div class="space-y-4">
                                @foreach($mediaPdfs as $pdf)
                                @include('admin.books._pdf-card', ['pdf' => $pdf, 'type' => 'banknotes'])
                                @endforeach
                            </div>
                            @endif
                        </div>
                    </div>
                </section>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('admin.dashboard') }}"
                        class="rounded-md bg-white/10 px-4 py-2 text-sm font-medium text-white hover:bg-white/15">Cancel</a>
                    <button type="submit" form="banknote-form"
                        class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500">Save changes</button>
                </div>
            </div>

    @include('admin.partials.lookup-modal')
@endsection
