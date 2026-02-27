# Nodestrich.com - Lightning Network Community Resource

A modern, community-driven platform for Lightning Network node operators. Built with Next.js and featuring a git-based knowledge base system that enables community contributions.

## Features

- **Community Dashboard**: Real-time statistics from Amboss API
- **Member Directory**: Searchable directory of community node operators
- **Knowledge Base**: Comprehensive guides for beginner to advanced users
- **Git-based Content**: Community can contribute via GitHub pull requests
- **Search Functionality**: Fast, client-side search across all content
- **Responsive Design**: Mobile-first design with dark theme

## Tech Stack

- **Framework**: Next.js 15 with App Router
- **Language**: TypeScript
- **Styling**: Tailwind CSS
- **Content**: MDX with frontmatter
- **Deployment**: Vercel
- **API**: Amboss GraphQL API

## Local Development

### Prerequisites

- Node.js 18+
- npm or yarn
- Amboss API key

### Setup

1. Clone the repository:
```bash
git clone https://github.com/nodestrich/nodestrich-web.git
cd nodestrich-web
```

2. Install dependencies:
```bash
npm install
```

3. Create environment file:
```bash
cp .env.local.example .env.local
```

4. Add your Amboss API key to `.env.local`:
```
AMBOSS_API_KEY=your_api_key_here
```

5. Start development server:
```bash
npm run dev
```

6. Open [http://localhost:3000](http://localhost:3000)

### Available Scripts

- `npm run dev` - Start development server
- `npm run build` - Build for production
- `npm run start` - Start production server
- `npm run lint` - Run ESLint

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
content/
├── learn/
│   ├── beginner/
│   ├── intermediate/
│   └── advanced/
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

The site is automatically deployed to Vercel when changes are pushed to the main branch.

### Environment Variables

Set these in your Vercel dashboard:

- `AMBOSS_API_KEY` - Your Amboss API key

## API Endpoints

- `GET /api/community` - Fetch community stats and member list
- `GET /api/search?q={query}` - Search content

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