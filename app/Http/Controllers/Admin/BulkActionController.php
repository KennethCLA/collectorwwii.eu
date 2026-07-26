<?php

// app/Http/Controllers/Admin/BulkActionController.php

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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BulkActionController extends Controller
{
    private const TYPES = [
        'books'      => Book::class,
        'items'      => Item::class,
        'banknotes'  => Banknote::class,
        'coins'      => Coin::class,
        'magazines'  => Magazine::class,
        'newspapers' => Newspaper::class,
        'postcards'  => Postcard::class,
        'stamps'     => Stamp::class,
    ];

    public function __invoke(Request $request, string $type): RedirectResponse
    {
        abort_unless(array_key_exists($type, self::TYPES), 404);

        $modelClass = self::TYPES[$type];
        $this->authorize('update', new $modelClass());

        $validated = $request->validate([
            'ids'    => ['required', 'array', 'min:1'],
            'ids.*'  => ['required', 'integer', 'min:1'],
            'action' => ['required', 'in:delete,for_sale_on,for_sale_off,mark_sold'],
        ]);

        $ids   = $validated['ids'];
        $query = $modelClass::whereIn('id', $ids);

        switch ($validated['action']) {
            case 'delete':
                $count = $query->count();
                $query->forceDelete();
                $label = 'deleted';
                break;

            case 'for_sale_on':
                $count = $query->update(['for_sale' => true]);
                $label = 'marked for sale';
                break;

            case 'for_sale_off':
                $count = $query->update(['for_sale' => false]);
                $label = 'removed from sale';
                break;

            case 'mark_sold':
                $count = $query->update([
                    'sold_at'       => now()->toDateString(),
                    'for_sale'      => false,
                    'selling_price' => null,
                ]);
                $label = 'marked as sold';
                break;
        }

        $singular = rtrim($type, 's');

        return back()->with('success', "{$count} {$singular}(s) {$label}.");
    }
}
