<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banknote;
use App\Models\Book;
use App\Models\Coin;
use App\Models\Item;
use App\Models\Magazine;
use App\Models\Newspaper;
use App\Models\Postcard;
use App\Models\Stamp;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class PdfController extends Controller
{
    public const TYPES = [
        'books'      => Book::class,
        'items'      => Item::class,
        'banknotes'  => Banknote::class,
        'coins'      => Coin::class,
        'magazines'  => Magazine::class,
        'newspapers' => Newspaper::class,
        'postcards'  => Postcard::class,
        'stamps'     => Stamp::class,
    ];

    public function download(string $type, int $id)
    {
        abort_unless(array_key_exists($type, self::TYPES), 404);

        $model = self::TYPES[$type]::findOrFail($id);
        $this->authorize('update', $model);

        $data = match ($type) {
            'books'      => $this->bookData($model),
            'items'      => $this->itemData($model),
            'banknotes'  => $this->banknoteData($model),
            'coins'      => $this->coinData($model),
            'magazines'  => $this->magazineData($model),
            'newspapers' => $this->newspaperData($model),
            'postcards'  => $this->postcardData($model),
            'stamps'     => $this->stampData($model),
        };

        $pdf = Pdf::loadView('pdf.show', $data)->setPaper('a4', 'portrait');

        return $pdf->download(Str::slug($data['title']) . '.pdf');
    }

    private function money(float $amount): string
    {
        return '€ ' . number_format($amount, 2, ',', '.');
    }

    private function bookData(Book $book): array
    {
        $book->load(['authors', 'cover', 'topic', 'series', 'origin', 'location']);
        $mainUrl = $book->mainImageFile()?->url();

        return [
            'title'   => $book->title,
            'section' => 'Books',
            'itemId'  => $book->id,
            'mainUrl' => $mainUrl,
            'publicFields' => array_filter([
                'Condition'     => $book->condition,
                'Author(s)'     => $book->authors->pluck('name')->join(', ') ?: null,
                'Cover'         => $book->cover?->name,
                'Topic'         => $book->topic?->name,
                'Series'        => $book->series?->name,
                'ISBN'          => $book->isbn,
                'Year'          => $book->year_published,
                'Language'      => $book->language,
                'Pages'         => $book->pages,
                'For sale'      => $book->for_sale ? 'Ja' : null,
                'Selling price' => $book->for_sale && $book->selling_price !== null ? $this->money($book->selling_price) : null,
                'Description'   => $book->description,
            ]),
            'privateFields' => array_filter([
                'Purchase date'  => $book->purchase_date?->format('d/m/Y'),
                'Purchase price' => $book->purchase_price !== null ? $this->money($book->purchase_price) : null,
                'Origin'         => $book->origin?->name,
                'Weight'         => $book->weight ? $book->weight . ' g' : null,
                'Dimensions'     => ($book->width || $book->height || $book->thickness)
                    ? ($book->width ?: '—') . ' × ' . ($book->height ?: '—') . ' × ' . ($book->thickness ?: '—') . ' mm'
                    : null,
                'Location'       => $book->location?->name,
                'Notes'          => $book->notes,
                'Sold on'        => $book->sold_at?->format('d/m/Y'),
                'Sold price'     => $book->sold_price !== null ? $this->money($book->sold_price) : null,
            ]),
        ];
    }

    private function itemData(Item $item): array
    {
        $item->load(['category', 'nationality', 'organization', 'origin']);
        $mainUrl = $item->mainImageFile()?->url();

        return [
            'title'   => $item->title,
            'section' => 'Items',
            'itemId'  => $item->id,
            'mainUrl' => $mainUrl,
            'publicFields' => array_filter([
                'Condition'     => $item->condition,
                'Category'      => $item->category?->name,
                'Nationality'   => $item->nationality?->name,
                'Organization'  => $item->organization?->name,
                'For sale'      => $item->for_sale ? 'Ja' : null,
                'Selling price' => $item->for_sale && $item->selling_price !== null ? $this->money($item->selling_price) : null,
                'Description'   => $item->description,
            ]),
            'privateFields' => array_filter([
                'Purchase date'  => $item->purchase_date?->format('d/m/Y'),
                'Purchase price' => $item->purchase_price !== null ? $this->money($item->purchase_price) : null,
                'Origin'         => $item->origin?->name,
                'Location'       => $item->storage_location,
                'Notes'          => $item->notes,
                'Sold on'        => $item->sold_at?->format('d/m/Y'),
                'Sold price'     => $item->sold_price !== null ? $this->money($item->sold_price) : null,
            ]),
        ];
    }

    private function banknoteData(Banknote $banknote): array
    {
        $banknote->load(['country', 'currency', 'nominalValue', 'series', 'timePeriod', 'location']);
        $mainUrl = $banknote->mainImageFile()?->url();

        return [
            'title'   => $banknote->card_title,
            'section' => 'Banknotes',
            'itemId'  => $banknote->id,
            'mainUrl' => $mainUrl,
            'publicFields' => array_filter([
                'Condition'     => $banknote->condition,
                'Country'       => $banknote->country?->name,
                'Currency'      => $banknote->currency?->name,
                'Nominal value' => $banknote->nominalValue?->name,
                'Series'        => $banknote->series?->name,
                'Time period'   => $banknote->timePeriod?->name,
                'Year'          => $banknote->year,
                'Variation'     => $banknote->variation,
                'For sale'      => $banknote->for_sale ? 'Ja' : null,
                'Selling price' => $banknote->for_sale && $banknote->selling_price !== null ? $this->money($banknote->selling_price) : null,
            ]),
            'privateFields' => array_filter([
                'Purchase date'    => $banknote->purchase_date?->format('d/m/Y'),
                'Purchasing price' => $banknote->purchasing_price !== null ? $this->money($banknote->purchasing_price) : null,
                'Current value'    => $banknote->current_value !== null ? $this->money($banknote->current_value) : null,
                'Location'         => $banknote->location?->name,
                'Personal remarks' => $banknote->personal_remarks,
                'Sold on'          => $banknote->sold_at?->format('d/m/Y'),
                'Sold price'       => $banknote->sold_price !== null ? $this->money($banknote->sold_price) : null,
            ]),
        ];
    }

    private function coinData(Coin $coin): array
    {
        $coin->load(['country', 'currency', 'nominalValue', 'shape', 'material', 'occasion', 'location']);
        $mainUrl = $coin->mainImageFile()?->url();

        return [
            'title'   => $coin->card_title,
            'section' => 'Coins',
            'itemId'  => $coin->id,
            'mainUrl' => $mainUrl,
            'publicFields' => array_filter([
                'Condition'     => $coin->condition,
                'Country'       => $coin->country?->name,
                'Currency'      => $coin->currency?->name,
                'Nominal value' => $coin->nominalValue?->name,
                'Shape'         => $coin->shape?->name,
                'Material'      => $coin->material?->name,
                'Occasion'      => $coin->occasion?->name,
                'Year'          => $coin->year,
                'For sale'      => $coin->for_sale ? 'Ja' : null,
                'Selling price' => $coin->for_sale && $coin->selling_price !== null ? $this->money($coin->selling_price) : null,
            ]),
            'privateFields' => array_filter([
                'Purchase date'    => $coin->purchase_date?->format('d/m/Y'),
                'Purchasing price' => $coin->purchasing_price !== null ? $this->money($coin->purchasing_price) : null,
                'Location'         => $coin->location?->name,
                'Personal remarks' => $coin->personal_remarks,
                'Sold on'          => $coin->sold_at?->format('d/m/Y'),
                'Sold price'       => $coin->sold_price !== null ? $this->money($coin->sold_price) : null,
            ]),
        ];
    }

    private function magazineData(Magazine $magazine): array
    {
        $magazine->load(['series']);
        $mainUrl = $magazine->mainImageFile()?->url();

        return [
            'title'   => $magazine->title,
            'section' => 'Magazines',
            'itemId'  => $magazine->id,
            'mainUrl' => $mainUrl,
            'publicFields' => array_filter([
                'Condition'     => $magazine->condition,
                'Publisher'     => $magazine->publisher,
                'Series'        => $magazine->series?->name,
                'Issue number'  => $magazine->issue_number !== null ? (string) $magazine->issue_number : null,
                'Year'          => $magazine->issue_year !== null ? (string) $magazine->issue_year : null,
                'Description'   => $magazine->description,
                'For sale'      => $magazine->for_sale ? 'Ja' : null,
                'Selling price' => $magazine->for_sale && $magazine->selling_price !== null ? $this->money($magazine->selling_price) : null,
            ]),
            'privateFields' => array_filter([
                'Purchase date'  => $magazine->purchase_date?->format('d/m/Y'),
                'Purchase price' => $magazine->purchase_price !== null ? $this->money($magazine->purchase_price) : null,
                'Notes'          => $magazine->notes,
                'Sold on'        => $magazine->sold_at?->format('d/m/Y'),
                'Sold price'     => $magazine->sold_price !== null ? $this->money($magazine->sold_price) : null,
            ]),
        ];
    }

    private function newspaperData(Newspaper $newspaper): array
    {
        $mainUrl = $newspaper->mainImageFile()?->url();

        return [
            'title'   => $newspaper->title,
            'section' => 'Newspapers',
            'itemId'  => $newspaper->id,
            'mainUrl' => $mainUrl,
            'publicFields' => array_filter([
                'Condition'        => $newspaper->condition,
                'Publisher'        => $newspaper->publisher,
                'Publication date' => $newspaper->publication_date?->format('d/m/Y'),
                'Description'      => $newspaper->description,
                'For sale'         => $newspaper->for_sale ? 'Ja' : null,
                'Selling price'    => $newspaper->for_sale && $newspaper->selling_price !== null ? $this->money($newspaper->selling_price) : null,
            ]),
            'privateFields' => array_filter([
                'Purchase date'  => $newspaper->purchase_date?->format('d/m/Y'),
                'Purchase price' => $newspaper->purchase_price !== null ? $this->money($newspaper->purchase_price) : null,
                'Notes'          => $newspaper->notes,
                'Sold on'        => $newspaper->sold_at?->format('d/m/Y'),
                'Sold price'     => $newspaper->sold_price !== null ? $this->money($newspaper->sold_price) : null,
            ]),
        ];
    }

    private function postcardData(Postcard $postcard): array
    {
        $postcard->load(['country', 'postcardType', 'location']);
        $mainUrl = $postcard->mainImageFile()?->url();

        $stampStatuses = array_filter([
            $postcard->unstamped    ? 'Unstamped'     : null,
            $postcard->stamped      ? 'Stamped'        : null,
            $postcard->special_stamp? 'Special stamp'  : null,
        ]);

        return [
            'title'   => $postcard->card_title,
            'section' => 'Postcards',
            'itemId'  => $postcard->id,
            'mainUrl' => $mainUrl,
            'publicFields' => array_filter([
                'Condition'     => $postcard->condition,
                'Country'       => $postcard->country?->name,
                'Year'          => $postcard->year,
                'Type'          => $postcard->postcardType?->name,
                'Occasion'      => $postcard->occasion,
                'Stamp status'  => $stampStatuses ? implode(', ', $stampStatuses) : null,
                'For sale'      => $postcard->for_sale ? 'Ja' : null,
                'Selling price' => $postcard->for_sale && $postcard->selling_price !== null ? $this->money($postcard->selling_price) : null,
            ]),
            'privateFields' => array_filter([
                'Purchase date'    => $postcard->purchase_date?->format('d/m/Y'),
                'Purchasing price' => $postcard->purchasing_price !== null ? $this->money($postcard->purchasing_price) : null,
                'Location'         => $postcard->location?->name,
                'Personal remarks' => $postcard->personal_remarks,
                'Sold on'          => $postcard->sold_at?->format('d/m/Y'),
                'Sold price'       => $postcard->sold_price !== null ? $this->money($postcard->sold_price) : null,
            ]),
        ];
    }

    private function stampData(Stamp $stamp): array
    {
        $stamp->load(['country', 'currency', 'nominalValue', 'stampType', 'location']);
        $mainUrl = $stamp->mainImageFile()?->url();

        return [
            'title'   => $stamp->card_title,
            'section' => 'Stamps',
            'itemId'  => $stamp->id,
            'mainUrl' => $mainUrl,
            'publicFields' => array_filter([
                'Condition'     => $stamp->condition,
                'Country'       => $stamp->country?->name,
                'Currency'      => $stamp->currency?->name,
                'Nominal value' => $stamp->nominalValue?->name,
                'Type'          => $stamp->stampType?->name,
                'Year'          => $stamp->year,
                'Michel number' => $stamp->michel_number,
                'For sale'      => $stamp->for_sale ? 'Ja' : null,
                'Selling price' => $stamp->for_sale && $stamp->selling_price !== null ? $this->money($stamp->selling_price) : null,
            ]),
            'privateFields' => array_filter([
                'Purchase date'    => $stamp->purchase_date?->format('d/m/Y'),
                'Purchasing price' => $stamp->purchasing_price !== null ? $this->money($stamp->purchasing_price) : null,
                'Location'         => $stamp->location?->name,
                'Personal remarks' => $stamp->personal_remarks,
                'Sold on'          => $stamp->sold_at?->format('d/m/Y'),
                'Sold price'       => $stamp->sold_price !== null ? $this->money($stamp->sold_price) : null,
            ]),
        ];
    }
}
