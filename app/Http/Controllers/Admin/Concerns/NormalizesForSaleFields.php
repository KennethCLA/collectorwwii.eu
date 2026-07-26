<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Http\Request;

/**
 * Shared for_sale/sold_at/selling_price normalization used by all 8 collection
 * controllers' store()/update(): a sold item can't also be listed for sale,
 * and an item not for sale has no selling price. Was duplicated 16x before
 * this trait (store + update x 8 types) with a couple of subtly different
 * copies; centralized so the invariant can't drift per type again.
 */
trait NormalizesForSaleFields
{
    private function normalizeForSaleFields(array $validated, Request $request): array
    {
        $validated['for_sale'] = $request->boolean('for_sale');

        if (! $validated['for_sale']) {
            $validated['selling_price'] = null;
        }

        if (! empty($validated['sold_at'])) {
            $validated['for_sale'] = false;
            $validated['selling_price'] = null;
        }

        return $validated;
    }
}
