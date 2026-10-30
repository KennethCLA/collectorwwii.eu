<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateCatalogueQuery
{
    private const MAX_QUERY_STRING_BYTES = 2048;

    private const CONFIGURATION = [
        'books' => [
            'keys' => ['search', 'topic', 'series', 'cover', 'for_sale', 'sort', 'page'],
            'id_filters' => ['topic', 'series', 'cover'],
            'sorts' => ['title_asc', 'title_desc', 'author_asc', 'author_desc', 'created_at_asc', 'created_at_desc'],
        ],
        'items' => [
            'keys' => ['search', 'category_id', 'nationality_id', 'origin_id', 'organization_id', 'for_sale', 'sort', 'page'],
            'id_filters' => ['category_id', 'nationality_id', 'origin_id', 'organization_id'],
            'sorts' => ['title_asc', 'title_desc', 'created_at_asc', 'created_at_desc'],
        ],
    ];

    public function handle(Request $request, Closure $next, string $catalogue): Response
    {
        $configuration = self::CONFIGURATION[$catalogue] ?? null;

        if ($configuration === null || $request->getMethod() !== 'GET') {
            return $next($request);
        }

        $queryString = (string) $request->server->get('QUERY_STRING', '');

        if (strlen($queryString) > self::MAX_QUERY_STRING_BYTES) {
            return response('', 414);
        }

        $parameters = $this->parseRawQueryString($queryString);
        if ($parameters === null) {
            return $this->invalidQueryResponse();
        }

        $canonical = [];
        $redirectRequired = false;

        foreach ($parameters as [$key, $value]) {
            if (! in_array($key, $configuration['keys'], true)) {
                $redirectRequired = true;

                continue;
            }

            if ($value === '') {
                $redirectRequired = true;

                continue;
            }

            if ($key === 'search') {
                if (! mb_check_encoding($value, 'UTF-8')) {
                    return $this->invalidQueryResponse();
                }

                $trimmed = preg_replace('/^\s+|\s+$/u', '', $value);
                if ($trimmed === null || $trimmed === '') {
                    $redirectRequired = true;

                    continue;
                }

                if (mb_strlen($trimmed) > 120) {
                    return $this->invalidQueryResponse();
                }

                if ($trimmed !== $value) {
                    $redirectRequired = true;
                }

                $canonical[$key] = $trimmed;

                continue;
            }

            if (in_array($key, $configuration['id_filters'], true) && ! $this->isPositiveDecimalInteger($value)) {
                return $this->invalidQueryResponse();
            }

            if ($key === 'for_sale' && $value !== '0' && $value !== '1') {
                return $this->invalidQueryResponse();
            }

            if ($key === 'sort' && ! in_array($value, $configuration['sorts'], true)) {
                return $this->invalidQueryResponse();
            }

            if ($key === 'page' && (! $this->isPositiveDecimalInteger($value) || (int) $value > 1000)) {
                return $this->invalidQueryResponse();
            }

            if ($key === 'page' && $value === '1') {
                $redirectRequired = true;

                continue;
            }

            $canonical[$key] = $value;
        }

        if ($redirectRequired) {
            $query = http_build_query($canonical, '', '&', PHP_QUERY_RFC3986);

            return redirect($request->getPathInfo().($query === '' ? '' : '?'.$query), Response::HTTP_FOUND);
        }

        return $next($request);
    }

    /** @return array<int, array{0: string, 1: string}>|null */
    private function parseRawQueryString(string $queryString): ?array
    {
        if ($queryString === '') {
            return [];
        }

        $parameters = [];
        $seen = [];

        foreach (explode('&', $queryString) as $pair) {
            [$rawKey, $rawValue] = array_pad(explode('=', $pair, 2), 2, '');
            $key = urldecode($rawKey);

            if ($key === '' || str_contains($key, '[') || str_contains($key, ']') || isset($seen[$key])) {
                return null;
            }

            $seen[$key] = true;
            $parameters[] = [$key, urldecode($rawValue)];
        }

        return $parameters;
    }

    private function isPositiveDecimalInteger(string $value): bool
    {
        return preg_match('/^[1-9][0-9]*$/', $value) === 1;
    }

    private function invalidQueryResponse(): Response
    {
        return response('Invalid catalogue query.', Response::HTTP_BAD_REQUEST);
    }
}
