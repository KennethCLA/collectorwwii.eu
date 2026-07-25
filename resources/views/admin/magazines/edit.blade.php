{{-- resources/views/admin/magazines/edit.blade.php --}}

@extends('layouts.admin')

@section('admin-content')
        @php
        $val = fn(string $key, $fallback = '') => old($key, data_get($magazine, $key, $fallback));
        $forSaleOld = old('for_sale', $magazine->for_sale ?? false);
        $forSaleJs = filter_var($forSaleOld, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
        @endphp

        <form id="magazine-form" action="{{ route('admin.magazines.update', $magazine) }}" method="POST"
            class="w-full mx-auto max-w-7xl">
            @csrf
            @method('PUT')

            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-white">Edit magazine</h1>
                    <p class="mt-1 text-sm text-white/60">Update the magazine details.</p>
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
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
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
                        <div class="lg:col-span-2 space-y-2">
                            <label for="title" class="text-sm font-medium text-white/80">Title *</label>
                            <input id="title" type="text" name="title" value="{{ $val('title') }}" required
                                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-white/20">
                        </div>

                        <div class="space-y-2">
                            <label for="subtitle" class="text-sm font-medium text-white/80">Subtitle</label>
                            <input id="subtitle" type="text" name="subtitle" value="{{ $val('subtitle') }}"
                                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-white/20">
                        </div>

                        <div class="space-y-2">
                            <label for="publisher" class="text-sm font-medium text-white/80">Publisher</label>
                            <input id="publisher" type="text" name="publisher" value="{{ $val('publisher') }}"
                                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-white/20">
                        </div>

                        <div class="space-y-2">
                            <label for="issue_number" class="text-sm font-medium text-white/80">Issue number</label>
                            <input id="issue_number" type="number" name="issue_number" value="{{ $val('issue_number') }}" min="1"
                                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-white/20">
                        </div>

                        <div class="space-y-2">
                            <label for="issue_year" class="text-sm font-medium text-white/80">Issue year</label>
                            <input id="issue_year" type="number" name="issue_year" value="{{ $val('issue_year') }}"
                                min="1800" max="{{ date('Y') + 1 }}"
                                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-white/20">
                        </div>

                        <div class="space-y-2">
                            <label for="condition" class="text-sm font-medium text-white/80">Condition</label>
                            <select id="condition" name="condition" class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-white/20">
                                <option value="">— Not graded —</option>
                                @foreach(['Mint','Extremely Fine','Very Fine','Fine','Very Good','Good','Poor'] as $grade)
                                <option value="{{ $grade }}" @selected(old('condition', $magazine->condition ?? '') === $grade)>{{ $grade }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                        <div class="space-y-2">
                            <label for="series_id" class="text-sm font-medium text-white/80">Series</label>
                            <div class="flex items-center gap-2">
                                <select id="series_id" name="series_id" class="js-select w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-white/20">
                                    <option value="">— None —</option>
                                    @foreach($series as $s)
                                    <option value="{{ $s->id }}" @selected(old('series_id', $magazine->series_id ?? '') == $s->id)>
                                        {{ $s->name }}
                                    </option>
                                    @endforeach
                                </select>
                                <button type="button"
                                    class="h-10 w-10 shrink-0 rounded-md border border-white/10 bg-white/10 text-white hover:bg-white/15"
                                    data-lookup-add data-type="magazine-series" data-select="#series_id"
                                    title="Add series">+</button>
                            </div>
                        </div>

                    <div class="mt-6 space-y-2">
                        <label for="description" class="text-sm font-medium text-white/80">Description</label>
                        <textarea id="description" name="description" rows="5"
                            class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-white/20">{{ old('description', $magazine->description) }}</textarea>
                    </div>
                </section>

                {{-- ADMIN-ONLY FIELDS --}}
                <section class="rounded-xl border border-white/10 bg-black/20 p-6">
                    <div class="flex items-center justify-between gap-4 mb-5">
                        <div class="flex items-center gap-3">
                            <h2 class="text-base font-semibold text-white">Admin-only</h2>
                            <span class="inline-flex items-center rounded-full bg-white/10 px-2 py-0.5 text-xs text-white/70">Not visible publicly</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label for="purchase_date" class="text-sm font-medium text-white/80">Purchase date</label>
                            <input id="purchase_date" type="date" name="purchase_date"
                                value="{{ old('purchase_date', $magazine->purchase_date?->format('Y-m-d') ?? '') }}"
                                class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-white/20">
                        </div>

                        <div class="space-y-2">
                            <label for="purchase_price" class="text-sm font-medium text-white/80">Purchase price €</label>
                            <input id="purchase_price" type="number" step="0.01" name="purchase_price" value="{{ $val('purchase_price') }}"
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
                                    class="mt-2 w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-white/20">
                            </div>
                        </div>

                        {{-- Sold --}}
                        <div x-data="{ sold: {{ old('sold_at', $magazine->sold_at ?? null) ? 'true' : 'false' }} }" class="space-y-2">
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
                                        value="{{ old('sold_at', $magazine->sold_at?->format('Y-m-d') ?? '') }}"
                                        class="mt-1 w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-white/20">
                                </div>
                                <div>
                                    <label for="sold_price" class="text-sm font-medium text-white/80">Sold price €</label>
                                    <input id="sold_price" type="number" step="0.01" name="sold_price"
                                        value="{{ old('sold_price', $magazine->sold_price ?? '') }}"
                                        class="mt-1 w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-white/20">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 space-y-2">
                        <label for="notes" class="text-sm font-medium text-white/80">Notes</label>
                        <textarea id="notes" name="notes" rows="4"
                            class="w-full rounded-md border border-black/30 bg-white/10 px-3 py-2 text-white placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-white/20">{{ old('notes', $magazine->notes) }}</textarea>
                    </div>
                </section>

        </form>

                {{-- MEDIA --}}
                <section class="rounded-xl border border-black/20 bg-black/10 p-6 space-y-6">
                    <div class="flex items-center justify-between gap-4">
                        <h2 class="text-base font-semibold text-white">Media</h2>
                        <span class="text-xs text-white/50">Upload new files or manage existing ones</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="rounded-md bg-sage-900 p-4 border border-white/10">
                            <div class="text-white font-semibold mb-2">Upload images</div>
                            <form action="{{ route('admin.media.store', ['type' => 'magazines', 'id' => $magazine->id]) }}"
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
                            <form action="{{ route('admin.media.store', ['type' => 'magazines', 'id' => $magazine->id]) }}"
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
                    $mediaImages = $magazine->images;
                    $mediaPdfs = $magazine->files->filter(fn($f) => $f->isPdf())->values();
                    @endphp

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="bg-sage rounded-md p-4 border border-black/20">
                            <h3 class="text-lg font-semibold text-white mb-3">Images ({{ $mediaImages->count() }})</h3>
                            @if($mediaImages->isEmpty())
                            <p class="text-white/80 text-sm">No images uploaded yet.</p>
                            @else
                            <div class="flex flex-wrap gap-2 items-start"
                                data-reorder-container
                                data-reorder-url="{{ route('admin.media.reorder', ['type' => 'magazines', 'id' => $magazine->id]) }}">
                                @foreach($mediaImages as $img)
                                @include('admin.books._image-card', ['img' => $img, 'type' => 'magazines'])
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
                                @include('admin.books._pdf-card', ['pdf' => $pdf, 'type' => 'magazines'])
                                @endforeach
                            </div>
                            @endif
                        </div>
                    </div>
                </section>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('admin.dashboard') }}"
                        class="rounded-md bg-white/10 px-4 py-2 text-sm font-medium text-white hover:bg-white/15">Cancel</a>
                    <button type="submit" form="magazine-form"
                        class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500">Save changes</button>
                </div>
            </div>

    @include('admin.partials.lookup-modal')
@endsection
