# Agent Instructions

## Production Site

The live `nodestrich.com` website is deployed to DreamHost shared hosting in an Apache/PHP webroot.

The deployable production source is `dreamhost-site/`. It is a PHP conversion of the Next.js app and is intended to replace the older DreamHost site.

The Next.js app remains in the repository for reference, but it is not the DreamHost deploy target unless the user explicitly says the hosting plan has changed.

## DreamHost Access

Do not commit credentials, private keys, server secrets, database dumps, or `.env.dreamhost`.

Set up local access like this:

1. Copy `deploy/dreamhost.env.example` to `.env.dreamhost`.
2. Fill in `DREAMHOST_SSH_ALIAS`, `DREAMHOST_REMOTE_PATH`, and optionally `DREAMHOST_LOCAL_PATH`.
3. Put SSH credentials in `~/.ssh/config`, not in the repository.

The expected SSH config shape is:

```sshconfig
Host nodestrich-dreamhost
  HostName example.dreamhost.com
  User your_dreamhost_user
  IdentityFile ~/.ssh/nodestrich_dreamhost
  IdentitiesOnly yes
```

## Workflow For PHP Production Site Changes

1. Edit files under `dreamhost-site/`.

2. Validate locally:

   ```sh
   find dreamhost-site -path 'dreamhost-site/content' -prune -o -name '*.php' -print | sort | xargs -n1 php -l
   node --check dreamhost-site/assets/app.js
   ```

3. Run a dry-run deploy and summarize the rsync itemized changes:

   ```sh
   bash scripts/dreamhost-rsync.sh push
   ```

4. For the first replacement of the old DreamHost site, back up the old docroot separately, then dry-run with deletion so old files that could shadow `index.php` are removed:

   ```sh
   DREAMHOST_LOCAL_PATH=dreamhost-backup/ bash scripts/dreamhost-rsync.sh pull --apply
   bash scripts/dreamhost-rsync.sh push --delete
   ```

5. Only deploy after the user approves the dry-run or explicitly requested deployment:

   ```sh
   bash scripts/dreamhost-rsync.sh push --apply
   ```

Use `--delete` only when the user explicitly wants remote files removed. For first replacement, the apply command should usually be:

```sh
bash scripts/dreamhost-rsync.sh push --apply --delete
```

The rsync script intentionally protects DreamHost-owned `.htaccess`, `.well-known/`, `.well-known/nostr.json`, `cgi-bin/`, and `config.local.php` from overwrite/deletion. Do not remove those protections without explicit user approval.

Do not run `bash scripts/dreamhost-rsync.sh pull --apply` into `dreamhost-site/` unless the user explicitly wants to overwrite the PHP conversion with the remote server state. To preserve the old DreamHost site before first replacement, set `DREAMHOST_LOCAL_PATH=dreamhost-backup/` for a one-time pull.

## Next.js App

Use the normal Next.js scripts for the app that is intended for Vercel:

```sh
npm run lint
npm run build
```

Do not assume `npm run build` updates the live DreamHost site.
