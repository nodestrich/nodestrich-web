# Nodestrich.com - Lightning Network Community Resource

A modern, community-driven platform for Lightning Network node operators, featuring a git-based knowledge base system that enables community contributions.

The live site (nodestrich.com) is a PHP/Apache application deployed to DreamHost — see `dreamhost-site/` and the Deployment section below.

## Features

- **Community Dashboard**: Real-time statistics from Amboss API
- **Member Directory**: Searchable directory of community node operators
- **Knowledge Base**: Comprehensive guides for beginner to advanced users
- **Git-based Content**: Community can contribute via GitHub pull requests
- **Search Functionality**: Fast, client-side search across all content
- **Responsive Design**: Mobile-first design with dark theme

## Tech Stack

- **Runtime**: PHP on Apache, routed through a front controller via `.htaccess`
- **Deployment**: rsync over SSH — see `AGENTS.md` and `scripts/dreamhost-rsync.sh`
- **Content**: MDX with frontmatter
- **API**: Amboss GraphQL API

## Local Development

See `dreamhost-site/README.md` for working on the PHP site locally, and `AGENTS.md` for the full DreamHost workflow: pulling the live site, validating PHP/JS changes (`php -l`, `node --check`), and running the rsync dry-run/deploy.

## Content Management

### Adding New Content

Content is stored in MDX files with frontmatter metadata:

```markdown
---
title: "Your Article Title"
description: "Brief description"
category: "beginner" | "intermediate" | "advanced"
tags: ["tag1", "tag2"]
author: "Your Name"
publishedAt: "2025-01-01"
updatedAt: "2025-01-01"
---

# Your Content Here

Write your content in Markdown...
```

### Directory Structure

```
dreamhost-site/content/
├── learn/
│   ├── beginner/
│   ├── intermediate/
│   └── advanced/
├── highlights/
├── tools/
└── guides/
```

### Content Guidelines

- Use clear, descriptive titles
- Include practical examples
- Add relevant tags for discoverability
- Keep beginner content accessible
- Include code examples where appropriate
- Link to external resources when helpful

## Contributing

We welcome contributions from the Lightning Network community!

### Content Contributions

1. Fork the repository
2. Create a new branch for your content
3. Add your MDX file to the appropriate category
4. Submit a pull request with a clear description

### Code Contributions

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Run tests and linting
5. Submit a pull request

### Community Guidelines

- Be respectful and helpful
- Share accurate, up-to-date information
- Help newcomers understand concepts
- Contribute constructively to discussions
- Follow the code of conduct

## Deployment

The current live `nodestrich.com` site is hosted on DreamHost shared Apache/PHP hosting. The deployable production source is the PHP site in `dreamhost-site/`.

Use the rsync workflow in `AGENTS.md` and `scripts/dreamhost-rsync.sh` to update DreamHost. Run `bash scripts/dreamhost-rsync.sh push` first for a dry-run, then `bash scripts/dreamhost-rsync.sh push --apply` to deploy after reviewing the changes. For the first replacement of the old site, back up the old docroot and use a reviewed `--delete` deploy so stale Apache files do not shadow the PHP router.

### Environment Variables

Set these in `dreamhost-site/config.local.php` on DreamHost or as server environment variables:

- `AMBOSS_API_KEY` - Your Amboss API key
- `SIGNAL_INVITE_URL` - Optional Signal invite redirect for `/signal`

## API Endpoints

- `GET /api/community` - Fetch community stats and member list
- `GET /api/search?q={query}` - Search content
- `GET /api/btc-price` - Fetch current BTC/USD price

## Community Links

- **Amboss**: [Community Page](https://amboss.space/community/6d41c0bd-6e39-40a2-a062-a809c2e8c2b5)
- **Nostr**: [Profile](https://primal.net/p/npub1hxfkcs9gvtm49702rmwn2aeuvhkd2w6f0svm4sl84g8glhzx5u9srk5p6t)
- **Signal**: [Group Chat](https://nodestrich.com/signal)

## License

This project is open source. Content is licensed under Creative Commons where applicable.

## Support

- Open an issue for bugs or feature requests
- Join our community channels for questions
- Contribute to the knowledge base to help others

---

Built with ⚡ by the Nodestrich community
