import type { Metadata } from 'next';
import { getAllHighlights } from '@/lib/highlights';
import HighlightsGrid from '@/components/HighlightsGrid';

export const metadata: Metadata = {
  title: 'Highlights – Nodestrich',
  description:
    'A curated showcase of Lightning Network apps, tools, and communities worth exploring.',
};

export default async function HighlightsPage() {
  const highlights = await getAllHighlights();

  return (
    <div className="container mx-auto px-4 py-8">
      <h1 className="text-3xl font-bold mb-3 text-[#f800c1]">Lightning Highlights</h1>
      <p className="text-gray-300 mb-8 max-w-2xl">
        A curated collection of apps, tools, platforms, and communities pushing the Lightning
        Network forward. Browse by category or explore them all.
      </p>

      <HighlightsGrid highlights={highlights} />
    </div>
  );
}
