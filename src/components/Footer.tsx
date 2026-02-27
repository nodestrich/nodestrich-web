import Link from 'next/link';

const Footer = () => {
  return (
    <footer className="bg-[#121212] border-t border-[#282828] mt-16">
      <div className="container mx-auto px-4 py-8">
        <div className="text-center text-gray-400">
          <p className="mb-2">
            Nodestrich is an open-source project built for the Nostr community.
          </p>
          <p>
            We encourage members of our community to contribute their knowledge and experience on{' '}
            <a
              href="https://github.com/nodestrich/nodestrich-web"
              target="_blank"
              rel="noopener noreferrer"
              className="text-[#f800c1] hover:underline"
            >
              GitHub
            </a>
            .
          </p>
        </div>
      </div>
    </footer>
  );
};

export default Footer;
