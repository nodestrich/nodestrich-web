'use client';

import { useState } from 'react';
import type { Highlight, HighlightCategory } from '@/lib/highlights';

const CATEGORY_LABELS: Record<string, string> = {
  all: 'All',
  community: 'Community',
  social: 'Social',
  commerce: 'Commerce',
  tools: 'Tools',
  physical: 'Physical',
};

const CATEGORY_ORDER = ['all', 'community', 'social', 'commerce', 'tools', 'physical'];

interface Props {
  highlights: Highlight[];
}

export default function HighlightsGrid({ highlights }: Props) {
  const [activeCategory, setActiveCategory] = useState<'all' | HighlightCategory>('all');

  // Only show tabs for categories that have at least one highlight
  const presentCategories = CATEGORY_ORDER.filter(cat => {
    if (cat === 'all') return true;
    return highlights.some(h => h.category === cat);
  });

  const filtered =
    activeCategory === 'all'
      ? highlights
      : highlights.filter(h => h.category === activeCategory);

  return (
    <div>
      {/* Category filter tabs */}
      <div className="flex flex-wrap gap-2 mb-8">
        {presentCategories.map(cat => (
          <button
            key={cat}
            onClick={() => setActiveCategory(cat as 'all' | HighlightCategory)}
            className={`px-4 py-2 rounded-full text-sm font-medium transition-colors ${
              activeCategory === cat
                ? 'bg-[#f800c1] text-white'
                : 'bg-[#282828] text-gray-300 hover:text-[#f800c1] hover:bg-[#383838] border border-[#30363d]'
            }`}
          >
            {CATEGORY_LABELS[cat] ?? cat}
          </button>
        ))}
      </div>

      {/* Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
        {filtered.map(highlight => (
          <a
            key={highlight.slug}
            href={highlight.url}
            target="_blank"
            rel="noopener noreferrer"
            className="group flex flex-col p-5 bg-[#282828] border border-[#30363d] rounded-lg hover:border-[#f800c1] transition-colors"
          >
            {/* Header */}
            <div className="flex items-start justify-between mb-3">
              <h3 className="text-lg font-semibold text-gray-100 group-hover:text-[#f800c1] transition-colors leading-tight">
                {highlight.title}
              </h3>
              <span className="ml-3 flex-shrink-0 text-xs bg-[#121212] text-gray-400 px-2 py-1 rounded-full capitalize">
                {highlight.category}
              </span>
            </div>

            {/* Description */}
            <p className="text-gray-400 text-sm mb-4 leading-relaxed">{highlight.description}</p>

            {/* Body excerpt */}
            <p className="text-gray-500 text-xs leading-relaxed flex-grow line-clamp-3">
              {highlight.content.trim().split('\n\n')[0]}
            </p>

            {/* Footer */}
            <div className="mt-4 flex items-center justify-between">
              <div className="flex flex-wrap gap-1">
                {highlight.tags.slice(0, 3).map(tag => (
                  <span
                    key={tag}
                    className="text-xs bg-[#121212] text-gray-500 px-2 py-0.5 rounded"
                  >
                    {tag}
                  </span>
                ))}
              </div>
              <span className="text-xs text-[#f800c1] opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0 ml-2">
                Visit →
              </span>
            </div>
          </a>
        ))}
      </div>

      {filtered.length === 0 && (
        <p className="text-gray-500 text-center py-16">No highlights in this category yet.</p>
      )}
    </div>
  );
}
