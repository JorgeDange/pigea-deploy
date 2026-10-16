<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class ContentSeeder extends Seeder
{
    private const MODELS = [
        'news' => \App\Models\News::class,
        'projects' => \App\Models\Project::class,
        'jobs' => \App\Models\Job::class,
        'suppliers' => \App\Models\Supplier::class,
        'events' => \App\Models\Event::class,
        'legislation' => \App\Models\Legislation::class,
        'research' => \App\Models\Research::class,
        'interviews' => \App\Models\Interview::class,
        'technologies' => \App\Models\Technology::class,
        'directory' => \App\Models\Directory::class,
        'training' => \App\Models\Training::class,
    ];

    public function run(): void
    {
        $path = database_path('data');
        $authors = $this->seedAuthors($this->read($path.'/authors.json'));

        foreach (self::MODELS as $module => $class) {
            $doc = $this->read($path.'/'.$module.'.json');
            if ($doc === null) {
                $this->command?->warn("Sem dados: {$module}.json");
                continue;
            }

            foreach ($doc['items'] as $item) {
                $category = null;
                if (!empty($item['category'])) {
                    $category = Category::firstOrCreate(
                        ['type' => $module, 'slug' => Str::slug($item['category'])],
                        ['name' => $item['category']]
                    );
                }

                $class::firstOrCreate(
                    ['slug' => $item['slug']],
                    [
                        'title' => $item['title'],
                        'category_id' => $category?->id,
                        'author_id' => $authors[$item['author_name'] ?? ''] ?? null,
                        'excerpt' => $item['excerpt'] ?: null,
                        'content' => $doc['body'] ?? null,
                        'image' => $item['image'] ?: null,
                        'image_caption' => $item['image_caption'] ?: null,
                        'status' => 'published',
                        'published_at' => $item['published_at'],
                        'date_label' => $item['date_label'] ?? null,
                        'comments_count' => (int) ($item['comments_count'] ?? 0),
                        'views_count' => (int) ($item['views_count'] ?? 0),
                    ]
                );
            }

            $this->command?->info("{$module}: ".($class::count())." registos");
        }
    }

    private function seedAuthors(array $authors): array
    {
        $map = [];
        $used = [];

        foreach ($authors as $author) {
            $name = trim((string) ($author['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $base = Str::slug($name, '.');
            $email = $base.'@pigea.local';
            $suffix = 1;
            while (isset($used[$email])) {
                $suffix++;
                $email = $base.'.'.$suffix.'@pigea.local';
            }
            $used[$email] = true;

            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Str::random(40),
                    'avatar' => $author['avatar'] ?? null,
                ]
            );

            // Os autores do conteúdo extraído são editores: sem role a coluna
            // "Perfil" de `admin/utilizadores` ficava vazia para 9 em 10 users.
            $user->assignRole(Role::findOrCreate('editor', 'web'));

            $map[$name] = $user->id;
        }

        return $map;
    }

    private function read(string $file): ?array
    {
        if (! is_file($file)) {
            return null;
        }

        return json_decode(file_get_contents($file), true);
    }
}
