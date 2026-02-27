export default function CommunityPage() {
  return (
    <div className="container mx-auto px-4 py-8">
      <h1 className="text-3xl font-bold mb-6 text-[#f800c1]">Community</h1>
      <p className="text-gray-300 mb-8">
        Connect with Lightning Network node operators and share knowledge.
      </p>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div className="bg-[#282828] border border-[#121212] rounded-lg p-6">
          <h2 className="text-xl font-semibold mb-3 text-[#f800c1]">Join Our Platforms</h2>
          <div className="space-y-4">
            <a href="https://amboss.space/community/6d41c0bd-6e39-40a2-a062-a809c2e8c2b5"
               target="_blank"
               className="flex items-center space-x-3 p-3 bg-[#121212] rounded-lg hover:bg-[#383838] transition-colors">
              <img src="/icon_amboss.png" alt="Amboss" className="w-8 h-8" />
              <span className="text-gray-300">Amboss Community</span>
            </a>
            <a href="https://primal.net/p/npub1hxfkcs9gvtm49702rmwn2aeuvhkd2w6f0svm4sl84g8glhzx5u9srk5p6t"
               target="_blank"
               className="flex items-center space-x-3 p-3 bg-[#121212] rounded-lg hover:bg-[#383838] transition-colors">
              <img src="/icon_nostr.png" alt="Nostr" className="w-8 h-8" />
              <span className="text-gray-300">Nostr</span>
            </a>
            <a href="https://nodestrich.com/signal"
               target="_blank"
               className="flex items-center space-x-3 p-3 bg-[#121212] rounded-lg hover:bg-[#383838] transition-colors">
              <img src="/icon_Signal.png" alt="Signal" className="w-8 h-8" />
              <span className="text-gray-300">Signal Group</span>
            </a>
          </div>
        </div>

        <div className="bg-[#282828] border border-[#121212] rounded-lg p-6">
          <h2 className="text-xl font-semibold mb-3 text-[#f800c1]">Community Guidelines</h2>
          <ul className="space-y-2 text-gray-300">
            <li>• Be respectful and helpful</li>
            <li>• Help newcomers get started</li>
            <li>• Share knowledge and experience</li>
            <li>• Ask questions - all levels welcome!</li>
            <li>• Contribute to documentation if you can</li>
            <li>• No promotion of non-bitcoin projects</li>       
          </ul>
        </div>
      </div>
    </div>
  );
}