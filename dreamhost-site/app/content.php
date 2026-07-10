<?php
declare(strict_types=1);

const LEARN_CATEGORIES = ['beginner', 'intermediate', 'advanced'];

function parse_front_matter(string $raw): array
{
    if (!str_starts_with($raw, "---\n")) {
        return [[], $raw];
    }

    $end = strpos($raw, "\n---", 4);
    if ($end === false) {
        return [[], $raw];
    }

    $frontMatter = substr($raw, 4, $end - 4);
    $content = ltrim(substr($raw, $end + 4));
    $data = [];

    foreach (preg_split('/\R/', $frontMatter) ?: [] as $line) {
        if (!str_contains($line, ':')) {
            continue;
        }

        [$key, $value] = explode(':', $line, 2);
        $data[trim($key)] = parse_front_matter_value(trim($value));
    }

    return [$data, $content];
}

function parse_front_matter_value(string $value): mixed
{
    if ($value === '') {
        return '';
    }

    if ($value === 'true') {
        return true;
    }

    if ($value === 'false') {
        return false;
    }

    if ($value[0] === '[' && str_ends_with($value, ']')) {
        $inside = trim(substr($value, 1, -1));
        if ($inside === '') {
            return [];
        }

        return array_values(array_map(static function (string $item): string {
            $item = trim($item);
            if (
                (str_starts_with($item, '"') && str_ends_with($item, '"')) ||
                (str_starts_with($item, "'") && str_ends_with($item, "'"))
            ) {
                return substr($item, 1, -1);
            }

            return $item;
        }, explode(',', $inside)));
    }

    if (
        (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
        (str_starts_with($value, "'") && str_ends_with($value, "'"))
    ) {
        return substr($value, 1, -1);
    }

    return $value;
}

function read_mdx_file(string $path): array
{
    $raw = file_get_contents($path);
    if ($raw === false) {
        throw new RuntimeException('Unable to read content file: ' . $path);
    }

    return parse_front_matter($raw);
}

function get_all_content(): array
{
    $items = [];

    foreach (LEARN_CATEGORIES as $category) {
        $categoryPath = CONTENT_PATH . '/learn/' . $category;
        if (!is_dir($categoryPath)) {
            continue;
        }

        $files = glob($categoryPath . '/*.mdx') ?: [];
        foreach ($files as $file) {
            [$data, $body] = read_mdx_file($file);
            $slug = basename($file, '.mdx');

            $items[] = [
                'slug' => $category . '/' . $slug,
                'title' => (string) ($data['title'] ?? $slug),
                'description' => (string) ($data['description'] ?? ''),
                'category' => $category,
                'tags' => $data['tags'] ?? [],
                'author' => (string) ($data['author'] ?? ''),
                'publishedAt' => (string) ($data['publishedAt'] ?? ''),
                'updatedAt' => (string) ($data['updatedAt'] ?? ''),
                'content' => $body,
            ];
        }
    }

    usort($items, static function (array $a, array $b): int {
        $order = ['beginner' => 0, 'intermediate' => 1, 'advanced' => 2];
        $category = ($order[$a['category']] ?? 99) <=> ($order[$b['category']] ?? 99);
        return $category !== 0 ? $category : strcasecmp($a['title'], $b['title']);
    });

    return $items;
}

function get_content_by_slug(string $slug): ?array
{
    $parts = explode('/', trim($slug, '/'));
    if (count($parts) !== 2 || !in_array($parts[0], LEARN_CATEGORIES, true)) {
        return null;
    }

    [$category, $filename] = $parts;
    if (!preg_match('/^[a-z0-9-]+$/', $filename)) {
        return null;
    }

    $path = CONTENT_PATH . '/learn/' . $category . '/' . $filename . '.mdx';
    if (!is_file($path)) {
        return null;
    }

    [$data, $body] = read_mdx_file($path);

    return [
        'slug' => $category . '/' . $filename,
        'title' => (string) ($data['title'] ?? $filename),
        'description' => (string) ($data['description'] ?? ''),
        'category' => $category,
        'tags' => $data['tags'] ?? [],
        'author' => (string) ($data['author'] ?? ''),
        'publishedAt' => (string) ($data['publishedAt'] ?? ''),
        'updatedAt' => (string) ($data['updatedAt'] ?? ''),
        'content' => $body,
    ];
}

function search_content(string $query): array
{
    $query = strtolower(trim($query));
    if ($query === '') {
        return [];
    }

    return array_values(array_filter(get_all_content(), static function (array $item) use ($query): bool {
        $haystack = strtolower(
            $item['title'] . ' ' .
            $item['description'] . ' ' .
            implode(' ', $item['tags']) . ' ' .
            $item['content']
        );

        return str_contains($haystack, $query);
    }));
}

function get_all_highlights(): array
{
    $items = [];
    $path = CONTENT_PATH . '/highlights';
    if (!is_dir($path)) {
        return [];
    }

    foreach (glob($path . '/*.mdx') ?: [] as $file) {
        [$data, $body] = read_mdx_file($file);
        $items[] = [
            'slug' => basename($file, '.mdx'),
            'title' => (string) ($data['title'] ?? basename($file, '.mdx')),
            'description' => (string) ($data['description'] ?? ''),
            'url' => (string) ($data['url'] ?? '#'),
            'category' => (string) ($data['category'] ?? 'tools'),
            'tags' => $data['tags'] ?? [],
            'featured' => (bool) ($data['featured'] ?? false),
            'publishedAt' => (string) ($data['publishedAt'] ?? ''),
            'content' => trim($body),
        ];
    }

    usort($items, static function (array $a, array $b): int {
        if ($a['featured'] && !$b['featured']) {
            return -1;
        }

        if (!$a['featured'] && $b['featured']) {
            return 1;
        }

        return strcasecmp($a['title'], $b['title']);
    });

    return $items;
}

function render_markdown(string $markdown): string
{
    $lines = preg_split('/\R/', trim($markdown)) ?: [];
    $html = '';
    $paragraph = [];
    $count = count($lines);

    $flushParagraph = static function () use (&$html, &$paragraph): void {
        if ($paragraph === []) {
            return;
        }

        $html .= '<p>' . render_inline_markdown(implode(' ', $paragraph)) . '</p>';
        $paragraph = [];
    };

    for ($i = 0; $i < $count; $i++) {
        $line = rtrim($lines[$i]);

        if (trim($line) === '') {
            $flushParagraph();
            continue;
        }

        if (preg_match('/^```([a-zA-Z0-9_-]*)\s*$/', $line, $match)) {
            $flushParagraph();
            $language = $match[1] ?? '';
            $code = [];
            $i++;
            while ($i < $count && !preg_match('/^```\s*$/', rtrim($lines[$i]))) {
                $code[] = $lines[$i];
                $i++;
            }
            $class = $language !== '' ? ' class="language-' . e($language) . '"' : '';
            $html .= '<pre><code' . $class . '>' . e(implode("\n", $code)) . '</code></pre>';
            continue;
        }

        if (preg_match('/^(#{1,6})\s+(.+)$/', $line, $match)) {
            $flushParagraph();
            $level = min(strlen($match[1]), 4);
            $html .= '<h' . $level . '>' . render_inline_markdown($match[2]) . '</h' . $level . '>';
            continue;
        }

        if (preg_match('/^-{3,}\s*$/', trim($line))) {
            $flushParagraph();
            $html .= '<hr>';
            continue;
        }

        if (preg_match('/^\|(.+)\|\s*$/', $line) && $i + 1 < $count && preg_match('/^\|[\s:|-]+\|\s*$/', rtrim($lines[$i + 1]))) {
            $flushParagraph();
            $headers = markdown_table_cells($line);
            $i += 2;
            $rows = [];
            while ($i < $count && preg_match('/^\|(.+)\|\s*$/', rtrim($lines[$i]))) {
                $rows[] = markdown_table_cells(rtrim($lines[$i]));
                $i++;
            }
            $i--;

            $html .= '<div class="table-scroll"><table><thead><tr>';
            foreach ($headers as $cell) {
                $html .= '<th>' . render_inline_markdown($cell) . '</th>';
            }
            $html .= '</tr></thead><tbody>';
            foreach ($rows as $row) {
                $html .= '<tr>';
                foreach ($row as $cell) {
                    $html .= '<td>' . render_inline_markdown($cell) . '</td>';
                }
                $html .= '</tr>';
            }
            $html .= '</tbody></table></div>';
            continue;
        }

        if (preg_match('/^\s*[-*]\s+(.+)$/', $line)) {
            $flushParagraph();
            $items = [];
            while ($i < $count && preg_match('/^\s*[-*]\s+(.+)$/', rtrim($lines[$i]), $match)) {
                $items[] = $match[1];
                $i++;
            }
            $i--;
            $html .= '<ul>';
            foreach ($items as $item) {
                $html .= '<li>' . render_inline_markdown($item) . '</li>';
            }
            $html .= '</ul>';
            continue;
        }

        if (preg_match('/^\s*\d+\.\s+(.+)$/', $line)) {
            $flushParagraph();
            $items = [];
            while ($i < $count && preg_match('/^\s*\d+\.\s+(.+)$/', rtrim($lines[$i]), $match)) {
                $items[] = $match[1];
                $i++;
            }
            $i--;
            $html .= '<ol>';
            foreach ($items as $item) {
                $html .= '<li>' . render_inline_markdown($item) . '</li>';
            }
            $html .= '</ol>';
            continue;
        }

        if (preg_match('/^>\s?(.+)$/', $line)) {
            $flushParagraph();
            $quotes = [];
            while ($i < $count && preg_match('/^>\s?(.+)$/', rtrim($lines[$i]), $match)) {
                $quotes[] = $match[1];
                $i++;
            }
            $i--;
            $html .= '<blockquote>' . render_inline_markdown(implode(' ', $quotes)) . '</blockquote>';
            continue;
        }

        $paragraph[] = trim($line);
    }

    $flushParagraph();
    return $html;
}

function markdown_table_cells(string $line): array
{
    $line = trim($line);
    $line = trim($line, '|');
    return array_map('trim', explode('|', $line));
}

function render_inline_markdown(string $text): string
{
    $codeSpans = [];
    $text = preg_replace_callback('/`([^`]+)`/', static function (array $match) use (&$codeSpans): string {
        $key = '@@CODE' . count($codeSpans) . '@@';
        $codeSpans[$key] = '<code>' . e($match[1]) . '</code>';
        return $key;
    }, $text) ?? $text;

    $html = e($text);
    $html = preg_replace_callback('/\[([^\]]+)\]\(([^)]+)\)/', static function (array $match): string {
        $label = $match[1];
        $href = $match[2];
        return '<a href="' . e($href) . '">' . $label . '</a>';
    }, $html) ?? $html;
    $html = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $html) ?? $html;
    $html = preg_replace('/(?<!\*)\*([^*]+)\*(?!\*)/', '<em>$1</em>', $html) ?? $html;

    return str_replace(array_keys($codeSpans), array_values($codeSpans), $html);
}
