import fs from 'fs';
import path from 'path';
import matter from 'gray-matter';

const highlightsDirectory = path.join(process.cwd(), 'content', 'highlights');

export type HighlightCategory = 'physical' | 'social' | 'commerce' | 'tools' | 'community';

export interface HighlightMeta {
  title: string;
  description: string;
  url: string;
  category: HighlightCategory;
  tags: string[];
  featured: boolean;
  publishedAt: string;
}

export interface Highlight extends HighlightMeta {
  slug: string;
  content: string;
}

export async function getAllHighlights(): Promise<Highlight[]> {
  if (!fs.existsSync(highlightsDirectory)) return [];

  const files = fs.readdirSync(highlightsDirectory).filter(f => f.endsWith('.mdx'));
  const highlights: Highlight[] = [];

  for (const file of files) {
    const filePath = path.join(highlightsDirectory, file);
    const raw = fs.readFileSync(filePath, 'utf8');
    const { data, content } = matter(raw);

    highlights.push({
      slug: file.replace(/\.mdx$/, ''),
      title: data.title,
      description: data.description,
      url: data.url,
      category: data.category as HighlightCategory,
      tags: data.tags || [],
      featured: data.featured ?? false,
      publishedAt: data.publishedAt,
      content,
    });
  }

  // Featured first, then alphabetical by title
  return highlights.sort((a, b) => {
    if (a.featured && !b.featured) return -1;
    if (!a.featured && b.featured) return 1;
    return a.title.localeCompare(b.title);
  });
}
