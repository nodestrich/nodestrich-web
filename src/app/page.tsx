import Image from "next/image";
import CommunityStats from "@/components/CommunityStats";
import MemberDirectory from "@/components/MemberDirectory";
import { getFullCommunityInfo } from "@/lib/amboss";

export default async function Home() {
  // Get initial data at build time
  const initialData = await getFullCommunityInfo();

  return (
    <div className="container mx-auto px-4 py-8">
      {/* Welcome Section */}
      <div className="mb-8">
        <h2 className="text-2xl font-bold text-[#f800c1] mb-4">Welcome!</h2>
        <p className="text-gray-300 mb-6">
          Nodestrich is a community for node runners using Nostr. Users of all levels are welcome to join,
          open channels, share knowledge, and build the Nostr circular economy. We are active on Nostr and Signal.
        </p>
        <p className="text-gray-300 mb-6">To learn more and join, click one of the icons below:</p>

        <div className="flex justify-center">
          <div className="bg-[#21262d] border-2 border-[#30363d] rounded-lg p-6 inline-flex space-x-8">
            <a
              href="https://amboss.space/community/6d41c0bd-6e39-40a2-a062-a809c2e8c2b5"
              target="_blank"
              rel="noopener noreferrer"
              className="hover:opacity-75 transition-opacity"
            >
              <Image src="/icon_amboss.png" alt="Amboss" width={40} height={40} />
            </a>
            <a
              href="https://primal.net/p/npub1hxfkcs9gvtm49702rmwn2aeuvhkd2w6f0svm4sl84g8glhzx5u9srk5p6t"
              target="_blank"
              rel="noopener noreferrer"
              className="hover:opacity-75 transition-opacity"
            >
              <Image src="/icon_nostr.png" alt="Nostr" width={40} height={40} />
            </a>
            <a
              href="https://nodestrich.com/signal"
              target="_blank"
              rel="noopener noreferrer"
              className="hover:opacity-75 transition-opacity"
            >
              <Image src="/icon_Signal.png" alt="Signal" width={40} height={40} />
            </a>
          </div>
        </div>
      </div>

      {/* Community Stats */}
      <div className="mb-8">
        <h2 className="text-2xl font-bold text-[#f800c1] mb-6">Community Stats</h2>
        <CommunityStats initialData={initialData.error ? undefined : initialData} />
      </div>

      {/* Member Directory */}
      {!initialData.error && initialData.members && (
        <MemberDirectory members={initialData.members} />
      )}

      {initialData.error && (
        <div className="text-center py-8">
          <p className="text-red-400">Unable to load community data: {initialData.error}</p>
          <p className="text-gray-400 mt-2">Please try refreshing the page.</p>
        </div>
      )}
    </div>
  );
}
