<?php
declare(strict_types=1);

function tool_category_labels(): array
{
    return [
        'node-implementations' => 'Node Implementations',
        'node-management' => 'Node Management',
        'node-in-a-box' => 'Node-in-a-Box',
        'network-explorers' => 'Network Explorers',
        'liquidity' => 'Liquidity',
        'monitoring' => 'Monitoring',
    ];
}

function tool_category_order(): array
{
    return [
        'node-implementations',
        'node-management',
        'node-in-a-box',
        'network-explorers',
        'liquidity',
        'monitoring',
    ];
}

function tool_directory(): array
{
    return [
        [
            'name' => 'LND',
            'description' => 'Lightning Network Daemon by Lightning Labs. The most widely deployed Lightning implementation, written in Go.',
            'url' => 'https://github.com/lightningnetwork/lnd',
            'category' => 'node-implementations',
        ],
        [
            'name' => 'Core Lightning',
            'description' => "Blockstream's modular Lightning implementation written in C. Highly extensible with a plugin system.",
            'url' => 'https://github.com/ElementsProject/lightning',
            'category' => 'node-implementations',
        ],
        [
            'name' => 'Eclair',
            'description' => "ACINQ's Lightning implementation written in Scala. Powers the Phoenix wallet and ACINQ's routing node.",
            'url' => 'https://github.com/ACINQ/eclair',
            'category' => 'node-implementations',
        ],
        [
            'name' => 'LDK',
            'description' => 'Lightning Development Kit by Spiral. A flexible library for building custom Lightning-enabled applications.',
            'url' => 'https://lightningdevkit.org/',
            'category' => 'node-implementations',
        ],
        [
            'name' => 'ThunderHub',
            'description' => 'Full-featured Lightning node manager with a clean web interface. Supports LND with channel, payment, and forwarding management.',
            'url' => 'https://thunderhub.io/',
            'category' => 'node-management',
        ],
        [
            'name' => 'Ride The Lightning',
            'description' => 'Feature-rich web interface for managing LND and Core Lightning nodes. Supports channel management, payments, and routing.',
            'url' => 'https://github.com/Ride-The-Lightning/RTL',
            'category' => 'node-management',
        ],
        [
            'name' => 'Lightning Terminal',
            'description' => 'Lightning Labs browser-based tool for node management, liquidity operations, and channel optimization.',
            'url' => 'https://terminal.lightning.engineering/',
            'category' => 'node-management',
        ],
        [
            'name' => 'Umbrel',
            'description' => 'Personal server OS for self-hosting Bitcoin, Lightning, and dozens of other apps with a beautiful web interface.',
            'url' => 'https://umbrel.com/',
            'category' => 'node-in-a-box',
        ],
        [
            'name' => 'Start9',
            'description' => 'Sovereign computing platform focused on privacy and self-hosting. Runs Bitcoin, Lightning, and a growing app marketplace.',
            'url' => 'https://start9.com/',
            'category' => 'node-in-a-box',
        ],
        [
            'name' => 'RaspiBlitz',
            'description' => 'DIY Bitcoin and Lightning node on a Raspberry Pi. Highly customizable with a terminal-based setup and web dashboard.',
            'url' => 'https://raspiblitz.org/',
            'category' => 'node-in-a-box',
        ],
        [
            'name' => 'myNode',
            'description' => 'Easy-to-use Bitcoin and Lightning node with a web interface. Available as software or pre-built hardware.',
            'url' => 'https://mynodebtc.com/',
            'category' => 'node-in-a-box',
        ],
        [
            'name' => 'Amboss',
            'description' => 'Lightning Network explorer and analytics platform. Features node rankings, channel maps, and the Magma liquidity marketplace.',
            'url' => 'https://amboss.space/',
            'category' => 'network-explorers',
        ],
        [
            'name' => 'Mempool.space',
            'description' => 'Open-source Bitcoin mempool and blockchain explorer. Includes Lightning network visualization, fee estimation, and transaction tracking.',
            'url' => 'https://mempool.space/',
            'category' => 'network-explorers',
        ],
        [
            'name' => '1ML',
            'description' => 'Lightning Network search and analysis engine. Browse node and channel statistics, rankings, and network metrics.',
            'url' => 'https://1ml.com/',
            'category' => 'network-explorers',
        ],
        [
            'name' => 'LN+',
            'description' => 'Peer-to-peer Lightning channel swap marketplace. Organize triangular swaps and liquidity rings to build balanced channels.',
            'url' => 'https://lightningnetwork.plus/',
            'category' => 'liquidity',
        ],
        [
            'name' => 'Magma',
            'description' => 'Amboss liquidity marketplace for buying and selling Lightning channel capacity with automated management.',
            'url' => 'https://amboss.space/magma',
            'category' => 'liquidity',
        ],
        [
            'name' => 'Loop',
            'description' => 'Lightning Labs submarine swap service. Move funds between on-chain and Lightning to manage channel liquidity.',
            'url' => 'https://lightning.engineering/loop/',
            'category' => 'liquidity',
        ],
        [
            'name' => 'Pool',
            'description' => 'Lightning Labs peer-to-peer marketplace for buying and selling Lightning channel leases.',
            'url' => 'https://lightning.engineering/pool/',
            'category' => 'liquidity',
        ],
        [
            'name' => 'lndg',
            'description' => 'Auto-management and monitoring tool for LND nodes. Features automated rebalancing, fee management, and detailed analytics.',
            'url' => 'https://github.com/cryptosharks131/lndg',
            'category' => 'monitoring',
        ],
        [
            'name' => 'Balance of Satoshis',
            'description' => 'Powerful CLI tool for LND node management. Features rebalancing, accounting, Telegram bot integration, and chain operations.',
            'url' => 'https://github.com/alexbosworth/balanceofsatoshis',
            'category' => 'monitoring',
        ],
    ];
}

function get_community_info(): array
{
    $cached = read_json_cache('community.json', 3600);
    if (is_array($cached) && ($cached['_version'] ?? null) === 2) {
        return $cached;
    }

    $apiKey = (string) app_config('amboss_api_key');
    if ($apiKey === '') {
        return [
            'community' => null,
            'members' => [],
            'error' => 'AMBOSS_API_KEY is not configured.',
        ];
    }

    $communityId = '6d41c0bd-6e39-40a2-a062-a809c2e8c2b5';
    $communityQuery = <<<'GRAPHQL'
query GetCommunity($getCommunityId: String!) {
  getCommunity(id: $getCommunityId) {
    details {
      description
      pubId
    }
    member_count
    member_list
    community_stats {
      total_channels
      total_capacity
    }
  }
}
GRAPHQL;

    $aliasesQuery = <<<'GRAPHQL'
query getNodeAliasBatch($pubkeys: [String!]!) {
  getNodeAliasBatch(pubkeys: $pubkeys) {
    alias
    pub_key
  }
}
GRAPHQL;

    try {
        $communityResponse = amboss_graphql($communityQuery, ['getCommunityId' => $communityId], $apiKey);
        $community = $communityResponse['data']['getCommunity'] ?? null;

        if (!is_array($community)) {
            throw new RuntimeException('Amboss community response was empty.');
        }

        $memberList = $community['member_list'] ?? [];
        $aliasesResponse = amboss_graphql($aliasesQuery, ['pubkeys' => $memberList], $apiKey);
        $members = $aliasesResponse['data']['getNodeAliasBatch'] ?? [];
        if (!is_array($members)) {
            $members = [];
        }

        $statsByPubkey = fetch_member_node_stats(array_values(array_filter(array_map(
            static fn(array $member): string => (string) ($member['pub_key'] ?? ''),
            $members
        ))));

        usort($members, static function (array $a, array $b): int {
            $aAlias = normalize_member_alias((string) ($a['alias'] ?? ''), (string) ($a['pub_key'] ?? ''));
            $bAlias = normalize_member_alias((string) ($b['alias'] ?? ''), (string) ($b['pub_key'] ?? ''));
            return compare_member_aliases($aAlias, $bAlias);
        });

        $result = [
            'community' => [
                'description' => (string) ($community['details']['description'] ?? ''),
                'pubId' => (string) ($community['details']['pubId'] ?? ''),
                'member_list' => $memberList,
                'member_count' => (int) ($community['member_count'] ?? count($memberList)),
                'community_stats' => [
                    'total_channels' => (int) ($community['community_stats']['total_channels'] ?? 0),
                    'total_capacity' => (int) ($community['community_stats']['total_capacity'] ?? 0),
                ],
            ],
            'members' => array_values(array_map(static function (array $member) use ($statsByPubkey): array {
                $pubKey = (string) ($member['pub_key'] ?? '');
                $stats = $statsByPubkey[$pubKey] ?? [];

                return [
                    'alias' => normalize_member_alias((string) ($member['alias'] ?? ''), $pubKey),
                    'pub_key' => $pubKey,
                    'capacity_sats' => (int) ($stats['capacity_sats'] ?? 0),
                    'channels' => (int) ($stats['channels'] ?? 0),
                ];
            }, $members)),
            '_version' => 2,
        ];

        write_json_cache('community.json', $result);
        return $result;
    } catch (Throwable $exception) {
        return [
            'community' => null,
            'members' => [],
            'error' => $exception->getMessage(),
        ];
    }
}

function normalize_member_alias(string $alias, string $pubKey): string
{
    $alias = trim($alias);
    if ($alias !== '') {
        return $alias;
    }

    return $pubKey !== '' ? substr($pubKey, 0, 20) : 'Unknown node';
}

function compare_member_aliases(string $a, string $b): int
{
    static $collator = null;
    static $hasCollator = null;

    if ($hasCollator === null) {
        $hasCollator = class_exists('Collator');
        if ($hasCollator) {
            $collator = new Collator('en_US');
            $collator->setAttribute(Collator::ALTERNATE_HANDLING, Collator::NON_IGNORABLE);
            $collator->setStrength(Collator::SECONDARY);
        }
    }

    if ($hasCollator && $collator instanceof Collator) {
        return $collator->compare($a, $b);
    }

    return member_alias_sort_bucket($a) <=> member_alias_sort_bucket($b) ?: strcasecmp($a, $b);
}

function member_alias_sort_bucket(string $alias): int
{
    preg_match('/^./u', $alias, $match);
    $first = $match[0] ?? '';

    if ($first === '') {
        return 4;
    }

    if (preg_match('/^[[:punct:]]$/u', $first) === 1 || preg_match('/^[^\p{L}\p{N}]$/u', $first) === 1) {
        return 0;
    }

    if (ctype_digit($first)) {
        return 1;
    }

    return 2;
}

function amboss_graphql(string $query, array $variables, string $apiKey): array
{
    $response = http_json('https://amboss.space/graphql', [
        'method' => 'POST',
        'headers' => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ],
        'body' => json_encode([
            'query' => $query,
            'variables' => $variables,
        ], JSON_UNESCAPED_SLASHES),
        'timeout' => 30,
    ]);

    if (!empty($response['errors'][0]['message'])) {
        throw new RuntimeException((string) $response['errors'][0]['message']);
    }

    return $response;
}

function fetch_member_node_stats(array $pubkeys): array
{
    $pubkeys = array_values(array_unique(array_filter($pubkeys)));
    if ($pubkeys === []) {
        return [];
    }

    $apiKey = (string) app_config('amboss_api_key');
    $stats = [];

    foreach (array_chunk($pubkeys, 25) as $chunk) {
        $definitions = [];
        $fields = [];
        $variables = [];

        foreach ($chunk as $index => $pubkey) {
            $definitions[] = '$p' . $index . ': String!';
            $fields[] = 'n' . $index . ': getNode(pubkey: $p' . $index . ') { graph_info { node { pub_key } channels { num_channels total_capacity } } }';
            $variables['p' . $index] = $pubkey;
        }

        $query = 'query NodeStats(' . implode(', ', $definitions) . ') { ' . implode(' ', $fields) . ' }';

        try {
            $response = amboss_graphql($query, $variables, $apiKey);
        } catch (Throwable $exception) {
            continue;
        }

        foreach (($response['data'] ?? []) as $node) {
            $graphInfo = $node['graph_info'] ?? [];
            $pubKey = (string) ($graphInfo['node']['pub_key'] ?? '');
            if ($pubKey === '') {
                continue;
            }

            $stats[$pubKey] = [
                'channels' => (int) ($graphInfo['channels']['num_channels'] ?? 0),
                'capacity_sats' => (int) ($graphInfo['channels']['total_capacity'] ?? 0),
            ];
        }
    }

    return $stats;
}

function get_btc_price(): array
{
    $cached = read_json_cache('btc-price.json', 60);
    if (is_array($cached)) {
        return $cached;
    }

    $response = http_json('https://api.coingecko.com/api/v3/simple/price?ids=bitcoin&vs_currencies=usd', [
        'timeout' => 10,
    ]);

    $usd = $response['bitcoin']['usd'] ?? null;
    if (!is_numeric($usd)) {
        throw new RuntimeException('BTC price response was empty.');
    }

    $result = ['usd' => (float) $usd];
    write_json_cache('btc-price.json', $result);
    return $result;
}
