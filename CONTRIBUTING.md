# Contributing to Nodestrich

Thank you for your interest in contributing to Nodestrich! This document provides guidelines for contributing to our Lightning Network community resource.

## Types of Contributions

### Content Contributions
- Lightning Network guides and tutorials
- Best practices documentation
- Tool reviews and comparisons
- Troubleshooting guides
- Community experiences and case studies

### Code Contributions
- Bug fixes
- Feature enhancements
- Performance improvements
- UI/UX improvements
- Test coverage

### Community Contributions
- Answering questions in discussions
- Reviewing pull requests
- Reporting bugs
- Suggesting improvements

## Getting Started

### Prerequisites
- GitHub account
- Basic knowledge of Markdown (for content)
- Node.js 18+ (for code contributions)
- Understanding of Lightning Network concepts

### Setting Up Development Environment

1. Fork the repository
2. Clone your fork:
   ```bash
   git clone https://github.com/nodestrich/nodestrich-web.git
   cd nodestrich-web
   ```
3. Install dependencies:
   ```bash
   npm install
   ```
4. Create environment file:
   ```bash
   cp .env.local.example .env.local
   ```
5. Start development server:
   ```bash
   npm run dev
   ```

## Content Guidelines

### Writing Style
- Use clear, concise language
- Write for your target audience (beginner/intermediate/advanced)
- Include practical examples
- Provide step-by-step instructions where appropriate
- Use active voice

### Content Structure
```markdown
---
title: "Descriptive Title"
description: "Brief summary in 1-2 sentences"
category: "beginner" | "intermediate" | "advanced"
tags: ["relevant", "tags"]
author: "Your Name"
publishedAt: "YYYY-MM-DD"
updatedAt: "YYYY-MM-DD"
---

# Main Title

Brief introduction paragraph.

## Section Headers

Content organized in logical sections...

### Subsections

Break down complex topics into digestible parts.

## Code Examples

```bash
# Include relevant commands
lncli getinfo
```

## Resources

- Link to relevant external resources
- Reference official documentation
```

### Content Categories

**Beginner**
- Getting started guides
- Basic concepts
- Simple setup instructions
- FAQ content

**Intermediate**
- Channel management
- Routing optimization
- Advanced configurations
- Troubleshooting

**Advanced**
- Protocol deep-dives
- Custom implementations
- Performance tuning
- Development guides

### File Naming
- Use kebab-case: `channel-management-basics.mdx`
- Be descriptive but concise
- Include the category in the path: `content/learn/beginner/`

## Code Guidelines

### TypeScript
- Use TypeScript for all new code
- Define proper types and interfaces
- Avoid `any` types when possible
- Use meaningful variable names

### React Components
- Use functional components with hooks
- Follow the existing component structure
- Add proper TypeScript props interfaces
- Include accessibility attributes

### Styling
- Use Tailwind CSS classes
- Follow the existing color scheme
- Ensure responsive design
- Test dark mode compatibility

### API Routes
- Handle errors gracefully
- Include proper TypeScript types
- Add appropriate caching headers
- Document endpoint behavior

## Pull Request Process

### For Content Changes
1. Create a feature branch: `git checkout -b add-routing-guide`
2. Add your content to the appropriate directory
3. Test locally to ensure it renders correctly
4. Commit with a descriptive message
5. Push to your fork and create a pull request

### For Code Changes
1. Create a feature branch: `git checkout -b fix-search-bug`
2. Make your changes
3. Run tests: `npm test`
4. Run linting: `npm run lint`
5. Commit with clear messages
6. Push to your fork and create a pull request

### Pull Request Template
```markdown
## Description
Brief description of changes made.

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Content addition
- [ ] Documentation update
- [ ] Performance improvement

## Testing
- [ ] Tested locally
- [ ] All tests pass
- [ ] No linting errors

## Screenshots (if applicable)
Add screenshots for UI changes.

## Checklist
- [ ] Code follows project style guidelines
- [ ] Self-review completed
- [ ] Documentation updated if needed
- [ ] No breaking changes introduced
```

## Review Process

### Content Review
- Technical accuracy
- Writing quality and clarity
- Appropriate difficulty level
- Proper formatting and structure
- Relevant tags and metadata

### Code Review
- Code quality and style
- Performance implications
- Security considerations
- Test coverage
- Documentation completeness

### Review Timeline
- Initial review within 1-2 days
- Follow-up responses within 1 day
- Final approval depends on complexity

## Community Standards

### Code of Conduct
- Be respectful and inclusive
- Welcome newcomers and questions
- Provide constructive feedback
- Focus on the technical content
- Help maintain a positive community

### Quality Standards
- Accuracy is paramount
- Test all instructions before submitting
- Update content when information changes
- Credit sources and references appropriately

### Communication
- Use GitHub discussions for questions
- Join community channels for real-time chat
- Be patient with review process
- Respond to feedback promptly

## Recognition

### Contributor Recognition
- Contributors listed in documentation
- GitHub profile linked where appropriate
- Community recognition in channels
- Potential invitation to maintainer team

### Contribution Types
- **Content Creator**: Regular content contributions
- **Code Contributor**: Regular code contributions
- **Community Helper**: Active in discussions and support
- **Maintainer**: Trusted community member with review privileges

## Getting Help

### Resources
- GitHub Discussions for questions
- Community Signal group
- Existing documentation and guides
- Lightning Network specifications

### Contact
- Open a GitHub issue for bugs
- Start a discussion for questions
- Join community channels for real-time help
- Email maintainers for sensitive issues

## License

By contributing to Nodestrich, you agree that your contributions will be licensed under the same license as the project. Content contributions may be subject to Creative Commons licensing.

---

Thank you for helping make Nodestrich a valuable resource for the Lightning Network community! ⚡