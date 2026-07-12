<?php
declare(strict_types=1);

function route_page(string $path): array
{
    return match (true) {
        $path === '/' => [
            'Nodestrich - A community for node runners using Nostr',
            'A community for node runners using Nostr. Users of all levels are welcome to join, open channels, share knowledge, and build the Nostr circular economy.',
            render_home_page(),
        ],
        $path === '/highlights' => [
            'Highlights',
            'A curated showcase of Lightning Network apps, tools, and communities worth exploring.',
            render_highlights_page(),
        ],
        $path === '/learn' => [
            'Lightning Network Knowledge Base',
            'Comprehensive guides and documentation for Lightning Network node operators of all levels.',
            render_learn_page(),
        ],
        preg_match('#^/learn/(beginner|intermediate|advanced)/([a-z0-9-]+)$#', $path, $matches) === 1 => render_article_route($matches[1] . '/' . $matches[2]),
        $path === '/tools' => [
            'Tools',
            'Interactive Lightning Network tools and a curated directory of resources for node operators.',
            render_tools_page(),
        ],
        $path === '/community' => [
            'Community',
            'Connect with Lightning Network node operators and share knowledge.',
            render_community_page(),
        ],
        default => render_not_found_route(),
    };
}

function handle_signal_redirect(): void
{
    $inviteUrl = (string) app_config('signal_invite_url');
    if ($inviteUrl !== '') {
        redirect_to($inviteUrl);
        return;
    }

    redirect_to('/community');
}

function render_home_page(): string
{
    $data = get_community_info();
    $community = $data['community'] ?? null;
    $members = $data['members'] ?? [];
    $error = $data['error'] ?? null;

    ob_start();
    ?>
<section class="container page-section">
  <div class="intro-block">
    <h1>Welcome!</h1>
    <p>
      Nodestrich is a community for node runners using Nostr. Users of all levels are welcome to join,
      open channels, share knowledge, and build the Nostr circular economy. We are active on Nostr and Signal.
    </p>
    <p>To learn more and join, click one of the icons below:</p>

    <div class="social-panel" aria-label="Nodestrich community links">
      <a href="https://amboss.space/community/6d41c0bd-6e39-40a2-a062-a809c2e8c2b5" target="_blank" rel="noopener noreferrer">
        <img src="<?= asset('icon_amboss.png') ?>" alt="Amboss">
      </a>
      <a href="https://primal.net/p/npub1hxfkcs9gvtm49702rmwn2aeuvhkd2w6f0svm4sl84g8glhzx5u9srk5p6t" target="_blank" rel="noopener noreferrer">
        <img src="<?= asset('icon_nostr.png') ?>" alt="Nostr">
      </a>
      <a href="/signal" target="_blank" rel="noopener noreferrer">
        <img src="<?= asset('icon_Signal.png') ?>" alt="Signal">
      </a>
    </div>
  </div>

  <div class="section-heading">
    <h2>Community Stats</h2>
  </div>

  <?php if (is_array($community)): ?>
    <div class="stat-grid" data-community-stats>
      <article class="stat-card">
        <strong class="js-counter" data-target="<?= e((string) $community['member_count']) ?>"><?= e((string) $community['member_count']) ?></strong>
        <span>Members</span>
      </article>
      <article class="stat-card">
        <?php $activeCount = count(filter_active_members($members)); ?>
        <strong class="js-counter" data-target="<?= e((string) $activeCount) ?>"><?= e((string) $activeCount) ?></strong>
        <span>Active Nodes</span>
      </article>
      <article class="stat-card">
        <strong class="js-counter" data-target="<?= e((string) $community['community_stats']['total_channels']) ?>"><?= e((string) $community['community_stats']['total_channels']) ?></strong>
        <span>Channels</span>
      </article>
      <article class="stat-card">
        <?php $capacity = (int) floor(((int) $community['community_stats']['total_capacity']) / 100000000); ?>
        <strong class="js-counter" data-target="<?= e((string) $capacity) ?>"><?= e((string) $capacity) ?></strong>
        <span>BTC Capacity</span>
      </article>
    </div>
  <?php else: ?>
    <div class="notice error">
      <p>Unable to load community data<?= $error ? ': ' . e($error) : '.' ?></p>
      <p>Configure the Amboss API key in <code>config.local.php</code> to enable live stats.</p>
    </div>
  <?php endif; ?>

  <?php if (is_array($members) && count($members) > 0): ?>
    <?= render_member_directory($members) ?>
  <?php endif; ?>
</section>
<?php
    return (string) ob_get_clean();
}

function filter_active_members(array $members): array
{
    return array_values(array_filter($members, static function (array $member): bool {
        $capacity = (int) ($member['capacity_sats'] ?? 0);
        $channels = (int) ($member['channels'] ?? 0);

        return $capacity > 0 && $channels > 0;
    }));
}

function render_member_directory(array $members): string
{
    $members = filter_active_members($members);

    ob_start();
    ?>
<section class="member-directory">
  <div class="directory-toolbar">
    <h2>Nodes</h2>
    <div class="toolbar-actions">
      <input type="search" placeholder="Search nodes..." data-member-search>
      <select data-member-sort aria-label="Sort nodes">
        <option value="default">Sort: Default</option>
        <option value="capacity">Sort: Capacity</option>
        <option value="channels">Sort: Channels</option>
      </select>
      <span class="muted small" data-member-count><?= count($members) ?> of <?= count($members) ?></span>
    </div>
  </div>

  <div class="member-grid" data-member-grid>
    <?php foreach ($members as $member): ?>
      <?php $alias = normalize_member_alias((string) ($member['alias'] ?? ''), (string) ($member['pub_key'] ?? '')); ?>
      <?php $pubKey = (string) ($member['pub_key'] ?? ''); ?>
      <?php $capacity = (int) ($member['capacity_sats'] ?? 0); ?>
      <?php $channels = (int) ($member['channels'] ?? 0); ?>
      <a
        href="https://amboss.space/node/<?= e($pubKey) ?>"
        target="_blank"
        rel="noopener noreferrer"
        class="member-card"
        data-member="<?= e(strtolower($alias)) ?>"
        data-capacity="<?= e((string) $capacity) ?>"
        data-channels="<?= e((string) $channels) ?>"
      >
        <span class="member-card__name"><?= e($alias) ?></span>
        <span class="member-card__row">
          <span>Capacity</span>
          <strong><?= e(format_node_capacity($capacity)) ?></strong>
        </span>
        <span class="member-card__row">
          <span>Channels</span>
          <strong><?= e(number_format($channels)) ?></strong>
        </span>
      </a>
    <?php endforeach; ?>
  </div>
  <p class="empty-state" data-member-empty hidden>No nodes found.</p>
</section>
<?php
    return (string) ob_get_clean();
}

function format_node_capacity(int $sats): string
{
    if ($sats <= 0) {
        return '- BTC';
    }

    $btc = $sats / 100000000;
    $decimals = $btc >= 1 ? 2 : 3;
    $formatted = number_format($btc, $decimals, '.', '');
    $formatted = rtrim(rtrim($formatted, '0'), '.');

    return $formatted . ' BTC';
}

function render_highlights_page(): string
{
    $highlights = get_all_highlights();
    $labels = [
        'all' => 'All',
        'community' => 'Community',
        'social' => 'Social',
        'commerce' => 'Commerce',
        'tools' => 'Tools',
        'physical' => 'Physical',
    ];
    $order = ['all', 'community', 'social', 'commerce', 'tools', 'physical'];
    $present = array_values(array_filter($order, static function (string $category) use ($highlights): bool {
        if ($category === 'all') {
            return true;
        }

        foreach ($highlights as $highlight) {
            if ($highlight['category'] === $category) {
                return true;
            }
        }

        return false;
    }));

    ob_start();
    ?>
<section class="container page-section">
  <h1>Lightning Highlights</h1>
  <p class="lede">
    A curated collection of apps, tools, platforms, and communities pushing the Lightning
    Network forward. Browse by category or explore them all.
  </p>

  <div data-filter-scope>
    <div class="filter-tabs" role="tablist" aria-label="Highlight categories">
      <?php foreach ($present as $category): ?>
        <button type="button" class="filter-tab<?= $category === 'all' ? ' is-active' : '' ?>" data-filter="<?= e($category) ?>">
          <?= e($labels[$category] ?? $category) ?>
        </button>
      <?php endforeach; ?>
    </div>

    <div class="card-grid three">
      <?php foreach ($highlights as $highlight): ?>
        <a href="<?= e($highlight['url']) ?>" target="_blank" rel="noopener noreferrer" class="directory-card" data-category="<?= e($highlight['category']) ?>">
          <div class="card-heading">
            <h2><?= e($highlight['title']) ?></h2>
            <span class="pill"><?= e($highlight['category']) ?></span>
          </div>
          <p><?= e($highlight['description']) ?></p>
          <p class="excerpt"><?= e(strtok($highlight['content'], "\n") ?: '') ?></p>
          <div class="card-footer">
            <div class="tag-row">
              <?php foreach (array_slice($highlight['tags'], 0, 3) as $tag): ?>
                <span><?= e($tag) ?></span>
              <?php endforeach; ?>
            </div>
            <span class="visit-label">Visit</span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
    <p class="empty-state" data-filter-empty hidden>No highlights in this category yet.</p>
  </div>
</section>
<?php
    return (string) ob_get_clean();
}

function render_learn_page(): string
{
    $content = get_all_content();
    $categoryCopy = [
        'beginner' => ['Beginner', 'Start your Lightning Network journey'],
        'intermediate' => ['Intermediate', 'Expand your node operations'],
        'advanced' => ['Advanced', 'Master Lightning Network'],
    ];

    ob_start();
    ?>
<section class="container page-section">
  <h1>Lightning Network Knowledge Base</h1>
  <p class="lede">Comprehensive guides and documentation for Lightning Network node operators of all levels.</p>

  <div class="category-grid">
    <?php foreach (LEARN_CATEGORIES as $category): ?>
      <?php $items = array_values(array_filter($content, static fn(array $item): bool => $item['category'] === $category)); ?>
      <article class="panel">
        <h2><?= e($categoryCopy[$category][0]) ?></h2>
        <p class="muted"><?= e($categoryCopy[$category][1]) ?></p>
        <div class="link-stack">
          <?php foreach ($items as $article): ?>
            <a href="/learn/<?= e($article['slug']) ?>">
              <strong><?= e($article['title']) ?></strong>
              <span><?= e($article['description']) ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      </article>
    <?php endforeach; ?>
  </div>

  <section class="subsection">
    <h2>All Articles</h2>
    <div class="card-grid two">
      <?php foreach ($content as $article): ?>
        <a href="/learn/<?= e($article['slug']) ?>" class="article-card">
          <div class="card-heading">
            <h3><?= e($article['title']) ?></h3>
            <span class="pill"><?= e($article['category']) ?></span>
          </div>
          <p><?= e($article['description']) ?></p>
          <div class="tag-row">
            <?php foreach ($article['tags'] as $tag): ?>
              <span><?= e($tag) ?></span>
            <?php endforeach; ?>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </section>
</section>
<?php
    return (string) ob_get_clean();
}

function render_article_route(string $slug): array
{
    $article = get_content_by_slug($slug);
    if (!$article) {
        return render_not_found_route();
    }

    ob_start();
    ?>
<section class="container page-section">
  <nav class="breadcrumb" aria-label="Breadcrumb">
    <a href="/learn">Knowledge Base</a>
    <span>/</span>
    <span><?= e(ucfirst($article['category'])) ?></span>
    <span>/</span>
    <span><?= e($article['title']) ?></span>
  </nav>

  <header class="article-header">
    <div class="article-title-row">
      <h1><?= e($article['title']) ?></h1>
      <span class="pill strong"><?= e($article['category']) ?></span>
    </div>
    <p class="lede"><?= e($article['description']) ?></p>
    <div class="tag-row">
      <?php foreach ($article['tags'] as $tag): ?>
        <span><?= e($tag) ?></span>
      <?php endforeach; ?>
    </div>
  </header>

  <article class="article-body">
    <?= render_markdown($article['content']) ?>
  </article>

  <footer class="article-footer">
    <a href="/learn">Back to Knowledge Base</a>
    <?php if ($article['updatedAt'] !== ''): ?>
      <span>Last updated: <?= e(format_date($article['updatedAt'])) ?></span>
    <?php endif; ?>
  </footer>
</section>
<?php
    return [
        $article['title'],
        $article['description'],
        (string) ob_get_clean(),
    ];
}

function render_tools_page(): string
{
    $labels = tool_category_labels();
    $order = tool_category_order();
    $tools = tool_directory();

    ob_start();
    ?>
<section class="container page-section">
  <h1>Node Runner Tools</h1>
  <p class="lede">
    Interactive tools for Lightning Network operations, plus a curated directory of essential
    resources for node operators.
  </p>

  <section class="subsection">
    <h2>Interactive Tools</h2>
    <div class="tool-stack">
      <?= render_bolt11_tool() ?>
      <?= render_converter_tool() ?>
      <?= render_channel_calculator_tool() ?>
    </div>
  </section>

  <section class="subsection">
    <h2>Tool Directory</h2>
    <div data-filter-scope>
      <div class="filter-tabs" role="tablist" aria-label="Tool categories">
        <button type="button" class="filter-tab is-active" data-filter="all">All</button>
        <?php foreach ($order as $category): ?>
          <button type="button" class="filter-tab" data-filter="<?= e($category) ?>"><?= e($labels[$category]) ?></button>
        <?php endforeach; ?>
      </div>

      <div class="card-grid three">
        <?php foreach ($tools as $tool): ?>
          <a href="<?= e($tool['url']) ?>" target="_blank" rel="noopener noreferrer" class="directory-card" data-category="<?= e($tool['category']) ?>">
            <div class="card-heading">
              <h3><?= e($tool['name']) ?></h3>
              <span class="pill"><?= e($labels[$tool['category']]) ?></span>
            </div>
            <p><?= e($tool['description']) ?></p>
            <div class="card-footer right">
              <span class="visit-label">Visit</span>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
      <p class="empty-state" data-filter-empty hidden>No tools in this category.</p>
    </div>
  </section>
</section>
<?php
    return (string) ob_get_clean();
}

function render_bolt11_tool(): string
{
    return <<<'HTML'
<article class="tool-panel" data-bolt11-tool>
  <h3>BOLT11 Invoice Decoder</h3>
  <p class="muted">Paste a Lightning invoice to decode its contents. This tool runs entirely in your browser.</p>
  <textarea rows="3" placeholder="lnbc..." data-bolt11-input></textarea>
  <div class="button-row">
    <button type="button" class="button primary" data-bolt11-decode>Decode</button>
    <button type="button" class="button secondary" data-bolt11-clear>Clear</button>
  </div>
  <div class="notice error" data-bolt11-error hidden></div>
  <div class="result-panel" data-bolt11-result hidden></div>
</article>
HTML;
}

function render_converter_tool(): string
{
    return <<<'HTML'
<article class="tool-panel" data-converter-tool>
  <h3>Sats / BTC / USD Converter</h3>
  <div class="price-status" data-price-status>Loading price...</div>
  <div class="form-stack">
    <label>Satoshis <input type="text" inputmode="numeric" placeholder="100,000" data-sats-input></label>
    <label>Bitcoin (BTC) <input type="text" inputmode="decimal" placeholder="0.00100000" data-btc-input></label>
    <label>USD <input type="text" inputmode="decimal" placeholder="0.00" data-usd-input disabled></label>
  </div>
</article>
HTML;
}

function render_channel_calculator_tool(): string
{
    return <<<'HTML'
<article class="tool-panel" data-channel-tool>
  <h3>Channel Size Calculator</h3>
  <p class="muted">Estimate optimal channel sizes based on your expected transaction volume.</p>
  <div class="form-grid">
    <label>Avg. Transaction Size (sats) <input type="text" inputmode="numeric" placeholder="50,000" data-avg-tx></label>
    <label>Monthly Transactions <input type="text" inputmode="numeric" placeholder="20" data-monthly-tx></label>
    <label>On-chain Fee Rate (sat/vB) <input type="text" inputmode="numeric" value="10" data-fee-rate></label>
    <label class="checkbox-label"><input type="checkbox" data-routing-node> I plan to route payments</label>
  </div>
  <div class="result-panel" data-channel-result hidden></div>
</article>
HTML;
}

function render_community_page(): string
{
    ob_start();
    ?>
<section class="container page-section">
  <h1>Community</h1>
  <p class="lede">Connect with Lightning Network node operators and share knowledge.</p>

  <section class="nostr-panel" data-nostr-latest>
    <div class="skeleton-line short"></div>
    <div class="skeleton-line"></div>
    <div class="skeleton-line medium"></div>
  </section>

  <div class="category-grid two-cols">
    <article class="panel">
      <h2>Where to Find Us</h2>
      <div class="link-stack community-links">
        <a href="https://amboss.space/community/6d41c0bd-6e39-40a2-a062-a809c2e8c2b5" target="_blank" rel="noopener noreferrer">
          <img src="<?= asset('icon_amboss.png') ?>" alt=""> <span>Amboss Community</span>
        </a>
        <a href="https://primal.net/p/npub1hxfkcs9gvtm49702rmwn2aeuvhkd2w6f0svm4sl84g8glhzx5u9srk5p6t" target="_blank" rel="noopener noreferrer">
          <img src="<?= asset('icon_nostr.png') ?>" alt=""> <span>Nostr</span>
        </a>
        <a href="/signal" target="_blank" rel="noopener noreferrer">
          <img src="<?= asset('icon_Signal.png') ?>" alt=""> <span>Signal Group</span>
        </a>
      </div>
    </article>

    <article class="panel">
      <h2>Community Guidelines</h2>
      <ul class="plain-list">
        <li>Be respectful and helpful</li>
        <li>Help newcomers get started</li>
        <li>Share knowledge and experience</li>
        <li>Ask questions - all levels welcome!</li>
        <li>Contribute to documentation if you can</li>
        <li>No promotion of non-bitcoin projects</li>
      </ul>
    </article>
  </div>
</section>
<?php
    return (string) ob_get_clean();
}

function render_not_found_route(): array
{
    http_response_code(404);

    return [
        'Page Not Found',
        'The requested Nodestrich page could not be found.',
        '<section class="container page-section"><h1>Page Not Found</h1><p class="lede">The page you requested does not exist.</p><a class="button primary inline" href="/">Go to dashboard</a></section>',
    ];
}
