{{-- resources/views/admin/postcards/create.blade.php --}}

@extends('layouts.admin')

@section('admin-content')
        @php
        $val = fn(string $key, $fallback = '') => old($key, $fallback);
        $forSaleJs = old('for_sale') ? 'true' : 'false';
        @endphp

        <form action="{{ route('admin.postcards.store') }}" method="POST" enctype="multipart/form-data" class="w-full mx-auto max-w-7xl">
            @csrf

            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-white">Create postcard</h1>
                    <p class="mt-1 text-sm text-white/60">Add a new postcard to the collection.</p>
                </div>
                <a href="{{ route('admin.dashboard') }}" class="rounded-md bg-white/10 px-4 py-2 text-sm font-medium text-white hover:bg-white/15">Back</a>
            </div>

            @if ($errors->any())
            <div class="mb-6 rounded-lg border border-red-500/30 bg-red-500/10 p-4 text-sm text-red-100">
                <div class="font-semibold mb-2">Please fix the following:</div>
                <ul class="list-disc pl-5 space-y-1">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
            @endif

            <div class="rounded-xl border border-black/20 bg-black/10 p-6 space-y-8">

                <section class="rounded-xl border border-black/20 bg-black/10 p-6">
                    <h2 class="text-base font-semibold text-white mb-5">Public details</h2>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label for="country_id" class="text-sm font-medium text-white/80">Country</label>
                            <div class="flex items-center gap-2">
                                <select id="country_id" name="country_id" class="js-select w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-white/20">
                                <option value="">—</option>
                                @foreach($countries as $c)
                                <option value="{{ $c->id }}" @selected((string)$val('country_id')===(string)$c->id)>{{ $c->name }}</option>
                                @endforeach
                            </select>
                            <button type="button"
                                class="h-10 w-10 shrink-0 rounded-md border border-white/10 bg-white/10 text-white hover:bg-white/15"
                                data-lookup-add data-type="countries" data-select="#country_id"
                                title="Add country">+</button>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label for="year" class="text-sm font-medium text-white/80">Year</label>
                            <input id="year" type="number" name="year" value="{{ $val('year') }}" min="1800" max="{{ date('Y') + 1 }}"
                                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-white/20">
                        </div>

                        <div class="space-y-2">
                            <label for="postcard_type_id" class="text-sm font-medium text-white/80">Postcard type</label>
                            <div class="flex items-center gap-2">
                                <select id="postcard_type_id" name="postcard_type_id" class="js-select w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-white/20">
                                <option value="">—</option>
                                @foreach($postcardTypes as $t)
                                <option value="{{ $t->id }}" @selected((string)$val('postcard_type_id')===(string)$t->id)>{{ $t->name }}</option>
                                @endforeach
                            </select>
                            <button type="button"
                                class="h-10 w-10 shrink-0 rounded-md border border-white/10 bg-white/10 text-white hover:bg-white/15"
                                data-lookup-add data-type="postcard-types" data-select="#postcard_type_id"
                                title="Add postcard type">+</button>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label for="occasion" class="text-sm font-medium text-white/80">Occasion</label>
                            <input id="occasion" type="text" name="occasion" value="{{ $val('occasion') }}"
                                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-white/20">
                        </div>

                        <div class="space-y-2">
                            <label for="currency_id" class="text-sm font-medium text-white/80">Currency</label>
                            <div class="flex items-center gap-2">
                                <select id="currency_id" name="currency_id" class="js-select w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-white/20">
                                <option value="">—</option>
                                @foreach($currencies as $cur)
                                <option value="{{ $cur->id }}" @selected((string)$val('currency_id')===(string)$cur->id)>{{ $cur->name }}</option>
                                @endforeach
                            </select>
                            <button type="button"
                                class="h-10 w-10 shrink-0 rounded-md border border-white/10 bg-white/10 text-white hover:bg-white/15"
                                data-lookup-add data-type="currencies" data-select="#currency_id"
                                title="Add currency">+</button>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label for="nominal_value_id" class="text-sm font-medium text-white/80">Nominal value</label>
                            <div class="flex items-center gap-2">
                                <select id="nominal_value_id" name="nominal_value_id" class="js-select w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-white/20">
                                <option value="">—</option>
                                @foreach($nominalValues as $nv)
                                <option value="{{ $nv->id }}" @selected((string)$val('nominal_value_id')===(string)$nv->id)>{{ $nv->name }}</option>
                                @endforeach
                            </select>
                            <button type="button"
                                class="h-10 w-10 shrink-0 rounded-md border border-white/10 bg-white/10 text-white hover:bg-white/15"
                                data-lookup-add data-type="nominal-values" data-select="#nominal_value_id"
                                title="Add nominal value">+</button>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label for="michel_number" class="text-sm font-medium text-white/80">Michel number</label>
                            <input id="michel_number" type="text" name="michel_number" value="{{ $val('michel_number') }}"
                                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-white/20">
                        </div>

                        <div class="space-y-2">
                            <label for="date_of_issue" class="text-sm font-medium text-white/80">Date of issue</label>
                            <input id="date_of_issue" type="text" name="date_of_issue" value="{{ $val('date_of_issue') }}"
                                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-white/20">
                        </div>

                        <div class="space-y-2">
                            <label for="valuation_image_id" class="text-sm font-medium text-white/80">Valuation image</label>
                            <div class="flex items-center gap-2">
                                <select id="valuation_image_id" name="valuation_image_id" class="js-select w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-white/20">
                                <option value="">—</option>
                                @foreach($valuationImages as $vi)
                                <option value="{{ $vi->id }}" @selected((string)$val('valuation_image_id')===(string)$vi->id)>{{ $vi->name }}</option>
                                @endforeach
                            </select>
                            <button type="button"
                                class="h-10 w-10 shrink-0 rounded-md border border-white/10 bg-white/10 text-white hover:bg-white/15"
                                data-lookup-add data-type="postcard-valuation-images" data-select="#valuation_image_id"
                                title="Add valuation image">+</button>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label for="colour_id" class="text-sm font-medium text-white/80">Colour</label>
                            <div class="flex items-center gap-2">
                                <select id="colour_id" name="colour_id" class="js-select w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-white/20">
                                <option value="">—</option>
                                @foreach($colours as $col)
                                <option value="{{ $col->id }}" @selected((string)$val('colour_id')===(string)$col->id)>{{ $col->name }}</option>
                                @endforeach
                            </select>
                            <button type="button"
                                class="h-10 w-10 shrink-0 rounded-md border border-white/10 bg-white/10 text-white hover:bg-white/15"
                                data-lookup-add data-type="colours" data-select="#colour_id"
                                title="Add colour">+</button>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label for="print_type_id" class="text-sm font-medium text-white/80">Print type</label>
                            <div class="flex items-center gap-2">
                                <select id="print_type_id" name="print_type_id" class="js-select w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-white/20">
                                <option value="">—</option>
                                @foreach($printTypes as $pt)
                                <option value="{{ $pt->id }}" @selected((string)$val('print_type_id')===(string)$pt->id)>{{ $pt->name }}</option>
                                @endforeach
                            </select>
                            <button type="button"
                                class="h-10 w-10 shrink-0 rounded-md border border-white/10 bg-white/10 text-white hover:bg-white/15"
                                data-lookup-add data-type="print-types" data-select="#print_type_id"
                                title="Add print type">+</button>
                            </div>
                        </div>

                        <div class="space-y-2 lg:col-span-2">
                            <label for="front_image" class="text-sm font-medium text-white/80">Front image description</label>
                            <textarea id="front_image" name="front_image" rows="3"
                                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-white/20">{{ old('front_image') }}</textarea>
                        </div>

                        <div class="space-y-2 lg:col-span-2">
                            <label for="special_features" class="text-sm font-medium text-white/80">Special features</label>
                            <textarea id="special_features" name="special_features" rows="3"
                                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-white/20">{{ old('special_features') }}</textarea>
                        </div>

                        <div class="space-y-2">
                            <label for="stamp_text" class="text-sm font-medium text-white/80">Stamp text</label>
                            <input id="stamp_text" type="text" name="stamp_text" value="{{ $val('stamp_text') }}"
                                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-white/20">
                        </div>

                        <div class="space-y-2">
                            <label for="stamp_date" class="text-sm font-medium text-white/80">Stamp date</label>
                            <input id="stamp_date" type="text" name="stamp_date" value="{{ $val('stamp_date') }}"
                                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-white/20">
                        </div>

                        <div class="space-y-2">
                            <label for="stamp_location" class="text-sm font-medium text-white/80">Stamp location</label>
                            <input id="stamp_location" type="text" name="stamp_location" value="{{ $val('stamp_location') }}"
                                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-white/20">
                        </div>

                        <div class="space-y-2">
                            <label for="width" class="text-sm font-medium text-white/80">Width (mm)</label>
                            <input id="width" type="number" step="0.01" name="width" value="{{ $val('width') }}"
                                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-white/20">
                        </div>

                        <div class="space-y-2">
                            <label for="height" class="text-sm font-medium text-white/80">Height (mm)</label>
                            <input id="height" type="number" step="0.01" name="height" value="{{ $val('height') }}"
                                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-white/20">
                        </div>

                        <div class="space-y-2">
                            <label for="print_run" class="text-sm font-medium text-white/80">Print run</label>
                            <input id="print_run" type="number" name="print_run" value="{{ $val('print_run') }}"
                                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-white/20">
                        </div>

                        <div class="space-y-2 lg:col-span-2">
                            <label for="unstamped" class="text-sm font-medium text-white/80">Stamp status</label>
                            <div class="flex flex-wrap gap-6">
                                <label for="stamped" class="flex items-center gap-2 text-sm text-white/70">
                                    <input id="unstamped" type="checkbox" name="unstamped" value="1" @checked($val('unstamped'))
                                        class="h-4 w-4 rounded border-white/20 bg-white/10">
                                    Unstamped
                                </label>
                                <label for="special_stamp" class="flex items-center gap-2 text-sm text-white/70">
                                    <input id="stamped" type="checkbox" name="stamped" value="1" @checked($val('stamped'))
                                        class="h-4 w-4 rounded border-white/20 bg-white/10">
                                    Stamped
                                </label>
                                <label for="perforation" class="flex items-center gap-2 text-sm text-white/70">
                                    <input id="special_stamp" type="checkbox" name="special_stamp" value="1" @checked($val('special_stamp'))
                                        class="h-4 w-4 rounded border-white/20 bg-white/10">
                                    Special stamp
                                </label>
                                <label class="flex items-center gap-2 text-sm text-white/70">
                                    <input id="perforation" type="checkbox" name="perforation" value="1" @checked($val('perforation'))
                                        class="h-4 w-4 rounded border-white/20 bg-white/10">
                                    Perforation
                                </label>
                            </div>
                        </div>

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
                </section>

                <section class="rounded-xl border border-white/10 bg-black/20 p-6">
                    <div class="flex items-center gap-3 mb-5">
                        <h2 class="text-base font-semibold text-white">Admin-only</h2>
                        <span class="inline-flex items-center rounded-full bg-white/10 px-2 py-0.5 text-xs text-white/70">Not visible publicly</span>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label for="purchase_date" class="text-sm font-medium text-white/80">Purchase date</label>
                            <input id="purchase_date" type="date" name="purchase_date" value="{{ $val('purchase_date') }}"
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
                                <select id="location_id" name="location_id" class="js-select w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-white/20">
                                    <option value="">—</option>
                                    @foreach($locations as $loc)
                                    <option value="{{ $loc->id }}" @selected((string)$val('location_id')===(string)$loc->id)>{{ $loc->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button"
                                    class="h-10 w-10 shrink-0 rounded-md border border-white/10 bg-white/10 text-white hover:bg-white/15"
                                    data-lookup-add data-type="locations" data-select="#location_id"
                                    title="Add location">+</button>
                            </div>
                        </div>
                        <div x-data="{ forSale: {{ $forSaleJs }} }" class="space-y-2">
                            <label class="text-sm font-medium text-white/80">For sale</label>
                            <div class="flex items-center gap-3">
                                <input type="hidden" name="for_sale" value="0">
                                <input id="for_sale" type="checkbox" name="for_sale" value="1" x-model="forSale" class="h-5 w-5 rounded border-white/20 bg-white/10">
                                <span class="text-sm text-white/70">Mark as for sale</span>
                            </div>
                            <div x-show="forSale" x-cloak class="pt-2">
                                <label for="selling_price" class="text-sm font-medium text-white/80">Selling price €</label>
                                <input id="selling_price" type="number" step="0.01" name="selling_price" value="{{ $val('selling_price') }}"
                                    class="mt-2 w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-white/20">
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label for="location_detail" class="text-sm font-medium text-white/80">Location detail</label>
                            <input id="location_detail" type="text" name="location_detail" value="{{ $val('location_detail') }}"
                                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-white/20">
                        </div>
                    </div>
                    <div class="mt-6 space-y-2">
                        <label for="personal_remarks" class="text-sm font-medium text-white/80">Personal remarks</label>
                        <textarea id="personal_remarks" name="personal_remarks" rows="4"
                            class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-white/20">{{ old('personal_remarks') }}</textarea>
                    </div>
                </section>

                @include('admin.partials.create-media-upload')

                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('admin.dashboard') }}" class="rounded-md bg-white/10 px-4 py-2 text-sm font-medium text-white hover:bg-white/15">Cancel</a>
                    <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500">Create postcard</button>
                </div>
            </div>
        </form>

    @include('admin.partials.lookup-modal')
@endsection
