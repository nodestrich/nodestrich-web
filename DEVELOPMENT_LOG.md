# Nodestrich Development Log

## Project Overview
Complete rebuild of Nodestrich.com from PHP/Dreamhost to modern Next.js stack with git-based content management system.

## Development Session: September 26, 2025

### Initial Planning & Architecture Decision
- **Goal**: Rebuild Nodestrich.com with better design and Lightning Network knowledge base
- **Architecture Choice**: Database-free approach using static generation + API calls
- **Key Requirements**:
  - Community contribution system via git
  - Preserve existing member directory functionality
  - Add comprehensive Lightning Network knowledge base
  - Enable community-driven content contributions

### Technology Stack Selected
- **Frontend**: Next.js 15 with App Router + TypeScript
- **Styling**: Tailwind CSS (matching original dark theme)
- **Content**: MDX with frontmatter for git-based content management
- **Data**: Hybrid approach - Amboss API + static generation
- **Deployment**: Vercel with automatic deployments
- **No Database**: Pure static + API approach for simplicity

### Implementation Progress

#### Phase 1: Foundation Setup ✅
- Created Next.js 15 project with TypeScript and Tailwind CSS
- Set up project structure with proper routing (`/learn`, `/community`, `/tools`)
- Migrated original assets (logo, icons, social preview image)
- Configured environment variables for Amboss API

#### Phase 2: API Integration ✅
- **Amboss API Migration**: Converted PHP GraphQL calls to Next.js API routes
- **Type Safety**: Created TypeScript interfaces for all API responses
- **Error Handling**: Proper error handling and fallbacks
- **Caching Strategy**: Build-time static generation + runtime updates

#### Phase 3: Core Components ✅
- **Navigation**: Responsive navigation with mobile menu
- **Community Stats**: Animated counters matching original UX
- **Member Directory**: Searchable grid with Amboss profile links
- **Layout System**: Consistent dark theme and responsive design

#### Phase 4: Knowledge Base System ✅
- **MDX Integration**: Full MDX support with syntax highlighting
- **Content Structure**: Organized by difficulty (beginner/intermediate/advanced)
- **Dynamic Routing**: `[...slug]` routing for flexible article URLs
- **Frontmatter Support**: Rich metadata for categorization and search

#### Phase 5: Search & Discovery ✅
- **Search API**: `/api/search` endpoint for content queries
- **Real-time Search**: Client-side search with debouncing
- **Search UI**: Dropdown results with category and tag filtering
- **Content Organization**: Clear categorization and tagging system

#### Phase 6: Content Creation ✅
- **Initial Articles**: Created sample content for each difficulty level
  - Beginner: "Getting Started with Lightning Network"
  - Intermediate: "Channel Management Best Practices"
  - Advanced: "Advanced Routing Optimization"
- **Content Guidelines**: Established structure and writing standards

#### Phase 7: Community Contribution System ✅
- **Git Workflow**: Pull request-based content contributions
- **Documentation**: Comprehensive README and CONTRIBUTING.md
- **Templates**: Content templates and guidelines for contributors
- **Recognition System**: Contributor attribution and community building

#### Phase 8: Deployment Configuration ✅
- **Vercel Setup**: Configured for automatic deployments
- **Environment Variables**: Documented required environment setup
- **Build Optimization**: Static generation with API route optimization
- **Performance**: Optimized for fast loading and SEO

### Key Technical Decisions

#### Database-Free Architecture
**Decision**: No database, pure static + API approach
**Reasoning**:
- Simplifies maintenance and reduces costs
- Git becomes the content management system
- Scales automatically with static hosting
- No database migration or maintenance needed
- Community can contribute via familiar git workflow

#### Hybrid Data Loading
**Decision**: Build-time static generation + runtime API updates
**Reasoning**:
- Fast initial page loads with static data
- Real-time stats updates for current information
- Fallback to cached data if API fails
- Best of both worlds for performance and freshness

#### MDX Content System
**Decision**: MDX files with frontmatter instead of CMS
**Reasoning**:
- Version control for all content changes
- Community can contribute via pull requests
- No vendor lock-in or additional services
- Familiar markdown syntax with React component support

#### Component-Based Architecture
**Decision**: Modular, typed React components
**Benefits**:
- Reusable UI components
- Type safety throughout application
- Easy to maintain and extend
- Clear separation of concerns

### Current Status: COMPLETE ✅

#### Working Features
- ✅ Community dashboard with real-time Amboss stats
- ✅ Animated counters matching original site UX
- ✅ Searchable member directory with Amboss profile links
- ✅ Complete knowledge base with 3 difficulty levels
- ✅ Full-text search across all content
- ✅ Responsive design with consistent dark theme
- ✅ Git-based content contribution workflow
- ✅ Dynamic article routing and rendering
- ✅ API endpoints for community data and search

#### Development Server
- **Status**: Running on http://localhost:3002
- **Build Status**: Successful compilation
- **Warnings**: Minor ESLint warnings (img tags, quotes) - non-blocking

#### Ready for Production
- ✅ Vercel deployment configuration complete
- ✅ Environment variables documented
- ✅ Content contribution guidelines established
- ✅ API key security properly configured
- ✅ All core functionality implemented and tested

### Next Steps for Production Deployment

1. **Repository Setup**
   - Push code to GitHub repository
   - Set up branch protection rules
   - Configure issue/PR templates

2. **Vercel Deployment**
   - Connect GitHub repository to Vercel
   - Set environment variables in Vercel dashboard
   - Configure custom domain (nodestrich.com)

3. **Content Migration**
   - Migrate any existing content from old site
   - Add more comprehensive Lightning Network guides
   - Set up content contribution workflow

4. **Community Launch**
   - Announce new site to community channels
   - Encourage community contributions
   - Gather feedback and iterate

### File Structure Summary
```
nodestrich-web/
├── src/
│   ├── app/                    # Next.js app directory
│   │   ├── api/               # API routes (community, search)
│   │   ├── learn/             # Knowledge base pages
│   │   ├── community/         # Community page
│   │   ├── tools/             # Tools page
│   │   └── layout.tsx         # Root layout
│   ├── components/            # React components
│   │   ├── Navigation.tsx     # Main navigation
│   │   ├── CommunityStats.tsx # Animated stats display
│   │   ├── MemberDirectory.tsx# Member grid
│   │   ├── SearchBox.tsx      # Search functionality
│   │   └── AnimatedCounter.tsx# Counter animation
│   ├── lib/                   # Utilities
│   │   ├── amboss.ts         # Amboss API integration
│   │   └── content.ts        # Content management
│   └── types/                 # TypeScript definitions
├── content/                   # MDX content files
│   └── learn/                # Knowledge base articles
│       ├── beginner/
│       ├── intermediate/
│       └── advanced/
├── public/                    # Static assets
├── README.md                  # Project documentation
├── CONTRIBUTING.md            # Contribution guidelines
└── vercel.json               # Deployment configuration
```

### Performance Characteristics
- **Initial Load**: Sub-second with static generation
- **Search**: Real-time with client-side caching
- **Member Directory**: Instant filtering and search
- **Content Delivery**: Global CDN via Vercel
- **API Response**: Cached with appropriate revalidation

### Community Impact
- **Contribution Barrier**: Lowered via git workflow
- **Content Quality**: Improved with review process
- **Knowledge Sharing**: Structured learning paths
- **Maintenance**: Distributed across community
- **Growth**: Scalable architecture for community expansion

This rebuild successfully transforms Nodestrich from a simple community directory into a comprehensive Lightning Network resource platform while maintaining the community-driven ethos and enabling sustainable growth through collaborative content creation.

## Development Notes

### Lessons Learned
1. **Database-free approach** significantly simplifies deployment and maintenance
2. **Git-based content management** enables true community collaboration
3. **Hybrid data loading** provides optimal balance of performance and freshness
4. **Type safety** throughout the application prevents many runtime errors
5. **Component architecture** makes the codebase maintainable and extensible

### Future Enhancements Considerations
- Advanced search with full-text indexing
- User accounts for content contributors
- Interactive tutorials and tools
- Integration with additional Lightning Network APIs
- Mobile app companion
- Multilingual content support

### Community Building Strategy
The new platform positions Nodestrich as the definitive Lightning Network resource by:
- Lowering barriers to content contribution
- Providing structured learning paths
- Enabling community-driven knowledge curation
- Maintaining technical accuracy through review processes
- Fostering collaboration between node operators of all skill levels

**Status**: Ready for production deployment and community launch! 🚀⚡

## Development Session: July 9, 2026

### Pivot: Back to PHP/DreamHost

The Next.js rebuild above was completed but never launched — `nodestrich.com` continued running the whole time on the original DreamHost PHP/Apache hosting. Rather than migrate to Vercel, the decision was made to keep DreamHost as the permanent home and bring the Next.js work's features (knowledge base, community stats, search, highlights) to a PHP rewrite instead.

#### What Changed
- Built `dreamhost-site/`, a PHP/Apache conversion carrying forward the Next.js app's content, features, and design (community stats, member directory, search, highlights, BTC price widget).
- Added an rsync-based deploy workflow (`scripts/dreamhost-rsync.sh`, `AGENTS.md`) to pull/push the live DreamHost site, dry-run by default.
- Deployed `dreamhost-site/` to production: merged the server's existing `.htaccess` rules (Lightning Address/lnurlp redirects) with the new PHP router's rewrite rules, and configured the Amboss API key via `config.local.php` (kept off git, placed on the server directly).
- Disconnected the Vercel project's GitHub auto-deploy integration, since Vercel is no longer a deployment target.
- Removed the Next.js app (`src/`, root `content/`, `public/`, and associated config/tooling) from this repository — it was never launched, and DreamHost/PHP is now the sole production target.

**Status**: DreamHost/PHP is live and is the only production target for nodestrich.com.