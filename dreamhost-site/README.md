# DreamHost Production Site

This directory is the PHP version of the Nodestrich site for DreamHost shared Apache/PHP hosting.

It is self-contained: PHP routes, CSS, JavaScript, content, and public assets all live inside this directory. It does not require Node, Next.js, Vercel, or Composer on DreamHost.

Copy `config.example.php` to `config.local.php` on DreamHost for server-only values:

```php
return [
    'amboss_api_key' => '...',
    'signal_invite_url' => '...',
];
```

Before deploying changes, run a dry-run:

```sh
bash scripts/dreamhost-rsync.sh push
```

For the first replacement of the old DreamHost site, back up the old docroot and dry-run deletion so old files do not shadow `index.php`:

```sh
DREAMHOST_LOCAL_PATH=dreamhost-backup/ bash scripts/dreamhost-rsync.sh pull --apply
bash scripts/dreamhost-rsync.sh push --delete
```

The rsync script protects DreamHost-owned `.htaccess`, `.well-known/`, `.well-known/nostr.json`, `cgi-bin/`, and `config.local.php` even when `--delete` is used.

Deploy only after reviewing the dry-run output:

```sh
bash scripts/dreamhost-rsync.sh push --apply
```

For first replacement, use:

```sh
bash scripts/dreamhost-rsync.sh push --apply --delete
```

Do not commit server secrets, database dumps, logs, or DreamHost-only local config.

Do not pull the old remote site into this directory unless you are intentionally reverting the PHP conversion.
