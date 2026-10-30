<?php

namespace App\Services;

class ContactSpamAssessor
{
    private const GENERIC_TERMS = ['seo', 'ranking', 'traffic', 'marketing'];

    private const STRONG_PHRASES = [
        'rank higher',
        'organic traffic',
        'search engine',
        'first page',
        'google ranking',
        'backlink',
        'backlinks',
        'domain authority',
        'guest post',
        'guest article',
        'website design',
        'web design',
        'increase your',
        'improve your website',
    ];

    /**
     * @return array{score: int, reasons: array<int, string>, shouldSuppress: bool}
     */
    public function assess(string $message): array
    {
        $reasons = [];
        $score = 0;

        $genericTermCount = count(array_filter(
            self::GENERIC_TERMS,
            fn (string $term): bool => $this->containsTerm($message, $term),
        ));

        if ($genericTermCount >= 3) {
            $score += 3;
            $reasons[] = 'generic_terms_3_plus';
        } elseif ($genericTermCount === 2) {
            $score += 2;
            $reasons[] = 'generic_terms_2';
        }

        if ($this->containsAnyTerm($message, self::STRONG_PHRASES)) {
            $score += 2;
            $reasons[] = 'strong_commercial_phrase';
        }

        if (preg_match('~\b(?:https?://|www\.)[^\s<>()]+~iu', $message) === 1) {
            $score++;
            $reasons[] = 'url';
        }

        if (preg_match('/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}\b/i', $message) === 1) {
            $score++;
            $reasons[] = 'email_address';
        }

        return [
            'score' => $score,
            'reasons' => $reasons,
            'shouldSuppress' => $score >= 3,
        ];
    }

    private function containsTerm(string $message, string $term): bool
    {
        $pattern = '/(?<![\p{L}\p{N}_])'.preg_quote($term, '/').'(?![\p{L}\p{N}_])/iu';

        return preg_match($pattern, $message) === 1;
    }

    /** @param array<int, string> $terms */
    private function containsAnyTerm(string $message, array $terms): bool
    {
        foreach ($terms as $term) {
            if ($this->containsTerm($message, $term)) {
                return true;
            }
        }

        return false;
    }
}
