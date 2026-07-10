<?php
declare(strict_types=1);

function render_layout(string $title, string $description, string $content, string $currentPath): void
{
    $navItems = [
        '/' => 'Dashboard',
        '/learn' => 'Learn',
        '/highlights' => 'Highlights',
        '/community' => 'Community',
        '/tools' => 'Tools',
    ];

    $fullTitle = str_contains($title, 'Nodestrich') ? $title : $title . ' - Nodestrich';
    $siteUrl = rtrim((string) app_config('site_url'), '/');
    ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($fullTitle) ?></title>
  <meta name="description" content="<?= e($description) ?>">
  <meta property="og:title" content="<?= e($fullTitle) ?>">
  <meta property="og:description" content="<?= e($description) ?>">
  <meta property="og:image" content="<?= e($siteUrl) ?>/social_preview.jpg">
  <meta property="og:url" content="<?= e($siteUrl . $currentPath) ?>">
  <meta property="og:type" content="website">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= e($fullTitle) ?>">
  <meta name="twitter:description" content="<?= e($description) ?>">
  <meta name="twitter:image" content="<?= e($siteUrl) ?>/social_preview.jpg">
  <link rel="icon" href="<?= asset('favicon.ico') ?>">
  <link rel="stylesheet" href="<?= asset('assets/styles.css') ?>">
  <script defer src="<?= asset('assets/app.js') ?>"></script>
</head>
<body>
  <nav class="site-nav">
    <div class="container nav-inner">
      <a href="/" class="brand-link" aria-label="Nodestrich home">
        <img src="<?= asset('nodestrich_logo_white.svg') ?>" alt="Nodestrich" class="brand-logo">
      </a>

      <div class="nav-desktop">
        <div class="nav-links">
          <?php foreach ($navItems as $href => $label): ?>
            <a href="<?= e($href) ?>" class="nav-link<?= is_active_path($currentPath, $href) ? ' is-active' : '' ?>">
              <?= e($label) ?>
            </a>
          <?php endforeach; ?>
        </div>
        <?= render_search_box() ?>
      </div>

      <button class="nav-toggle" type="button" aria-label="Open navigation" aria-expanded="false" data-nav-toggle>
        <span></span>
        <span></span>
        <span></span>
      </button>
    </div>

    <div class="nav-mobile" data-mobile-nav hidden>
      <div class="container">
        <?php foreach ($navItems as $href => $label): ?>
          <a href="<?= e($href) ?>" class="nav-link mobile<?= is_active_path($currentPath, $href) ? ' is-active' : '' ?>">
            <?= e($label) ?>
          </a>
        <?php endforeach; ?>
        <?= render_search_box() ?>
      </div>
    </div>
  </nav>

  <main>
    <?= $content ?>
  </main>

  <footer class="site-footer">
    <div class="container">
      <p>Nodestrich is an open-source project built for the Nostr community.</p>
      <p>
        We encourage members of our community to contribute their knowledge and experience on
        <a href="https://github.com/nodestrich/nodestrich-web" target="_blank" rel="noopener noreferrer">GitHub</a>.
      </p>
    </div>
  </footer>
</body>
</html>
<?php
}

function render_search_box(): string
{
    static $searchId = 0;
    $searchId++;

    ob_start();
    ?>
<div class="search-box" data-search-box>
  <label class="sr-only" for="site-search-<?= $searchId ?>">Search knowledge base</label>
  <input id="site-search-<?= $searchId ?>" type="search" placeholder="Search knowledge base..." autocomplete="off" data-search-input>
  <span class="search-icon" aria-hidden="true"></span>
  <div class="search-results" data-search-results hidden></div>
</div>
<?php
    return (string) ob_get_clean();
}
