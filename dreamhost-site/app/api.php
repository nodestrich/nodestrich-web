<?php
declare(strict_types=1);

function handle_api(string $path): void
{
    if ($path === '/api/search') {
        $query = trim((string) ($_GET['q'] ?? ''));
        if ($query === '') {
            send_json([]);
            return;
        }

        $results = array_map(static function (array $item): array {
            return [
                'slug' => $item['slug'],
                'title' => $item['title'],
                'description' => $item['description'],
                'category' => $item['category'],
                'tags' => $item['tags'],
            ];
        }, search_content($query));

        send_json($results);
        return;
    }

    if ($path === '/api/community') {
        $data = get_community_info();
        send_json($data, isset($data['error']) ? 500 : 200);
        return;
    }

    if ($path === '/api/btc-price') {
        try {
            send_json(get_btc_price());
        } catch (Throwable $exception) {
            send_json(['error' => 'Failed to fetch BTC price'], 500);
        }
        return;
    }

    send_json(['error' => 'Not found'], 404);
}
