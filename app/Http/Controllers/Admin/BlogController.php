<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    public function index()
    {
        $posts = collect($this->readPosts())
            ->sortByDesc(fn (array $post) => strtotime((string) ($post['date'] ?? '')))
            ->values()
            ->all();

        return view('admin.blog.index', [
            'posts' => $posts,
        ]);
    }

    public function create()
    {
        return view('admin.blog.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());
        $posts = $this->readPosts();

        $posts[] = $this->buildPost($validated);
        $this->writePosts($posts);

        return redirect()->route('admin.blog.index')
            ->with('success', 'Blog post created.');
    }

    public function edit(string $id)
    {
        $posts = $this->readPosts();
        $index = $this->findPostIndexById($posts, $id);
        abort_if($index === null, 404);

        return view('admin.blog.edit', [
            'post' => $posts[$index],
        ]);
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate($this->rules());
        $posts = $this->readPosts();
        $index = $this->findPostIndexById($posts, $id);
        abort_if($index === null, 404);

        $posts[$index] = $this->buildPost($validated, $id);
        $this->writePosts($posts);

        return redirect()->route('admin.blog.edit', $id)
            ->with('success', 'Blog post updated.');
    }

    public function destroy(string $id)
    {
        $posts = $this->readPosts();
        $index = $this->findPostIndexById($posts, $id);
        abort_if($index === null, 404);

        array_splice($posts, $index, 1);
        $this->writePosts($posts);

        return redirect()->route('admin.blog.index')
            ->with('success', 'Blog post deleted.');
    }

    private function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'content_en' => ['required', 'string'],
            'content_nl' => ['nullable', 'string'],
            'content_de' => ['nullable', 'string'],
            'content_fr' => ['nullable', 'string'],
        ];
    }

    private function blogPath(): string
    {
        return storage_path('app/public/blog.json');
    }

    private function readPosts(): array
    {
        $path = $this->blogPath();
        if (! File::exists($path)) {
            return [];
        }

        $raw = json_decode(File::get($path), true);
        if (! is_array($raw)) {
            return [];
        }

        $posts = [];
        $legacyOccurrences = [];

        foreach ($raw as $row) {
            if (! is_array($row)) {
                continue;
            }

            // Legacy rows have no stored ID. Keep their links stable across reads;
            // distinguish identical rows until the next mutation persists these IDs.
            $fingerprint = hash('sha256', json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            $occurrence = $legacyOccurrences[$fingerprint] ?? 0;
            $legacyOccurrences[$fingerprint] = $occurrence + 1;

            $content = is_array($row['content'] ?? null) ? $row['content'] : [];

            $posts[] = [
                'id' => (string) ($row['id'] ?? 'legacy-'.$fingerprint.'-'.$occurrence),
                'date' => (string) ($row['date'] ?? now()->toDateString()),
                'content' => [
                    'en' => (string) ($content['en'] ?? ''),
                    'nl' => (string) ($content['nl'] ?? ''),
                    'de' => (string) ($content['de'] ?? ''),
                    'fr' => (string) ($content['fr'] ?? ''),
                ],
            ];
        }

        return $posts;
    }

    private function writePosts(array $posts): void
    {
        $normalized = collect($posts)
            ->map(fn (array $post) => $this->buildPostFromRow($post))
            ->sortByDesc(fn (array $post) => strtotime((string) ($post['date'] ?? '')))
            ->values()
            ->all();

        $path = $this->blogPath();
        File::ensureDirectoryExists(dirname($path));
        File::put(
            $path,
            json_encode($normalized, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).PHP_EOL
        );
    }

    private function findPostIndexById(array $posts, string $id): ?int
    {
        foreach ($posts as $index => $post) {
            if (($post['id'] ?? null) === $id) {
                return $index;
            }
        }

        return null;
    }

    private function buildPost(array $validated, ?string $id = null): array
    {
        return [
            'id' => $id ?? (string) Str::ulid(),
            'date' => (string) $validated['date'],
            'content' => [
                'en' => trim((string) $validated['content_en']),
                'nl' => trim((string) ($validated['content_nl'] ?? '')),
                'de' => trim((string) ($validated['content_de'] ?? '')),
                'fr' => trim((string) ($validated['content_fr'] ?? '')),
            ],
        ];
    }

    private function buildPostFromRow(array $post): array
    {
        $content = is_array($post['content'] ?? null) ? $post['content'] : [];

        return [
            'id' => (string) ($post['id'] ?? Str::ulid()),
            'date' => (string) ($post['date'] ?? now()->toDateString()),
            'content' => [
                'en' => trim((string) ($content['en'] ?? '')),
                'nl' => trim((string) ($content['nl'] ?? '')),
                'de' => trim((string) ($content['de'] ?? '')),
                'fr' => trim((string) ($content['fr'] ?? '')),
            ],
        ];
    }
}
