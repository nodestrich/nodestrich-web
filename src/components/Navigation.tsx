'use client';

import Link from 'next/link';
import Image from 'next/image';
import { usePathname } from 'next/navigation';
import { useState } from 'react';
import SearchBox from './SearchBox';

const Navigation = () => {
  const pathname = usePathname();
  const [isMenuOpen, setIsMenuOpen] = useState(false);

  const navItems = [
    { href: '/', label: 'Dashboard' },
    { href: '/learn', label: 'Learn' },
    { href: '/highlights', label: 'Highlights' },
    { href: '/community', label: 'Community' },
    { href: '/tools', label: 'Tools' },
  ];

  return (
    <nav className="bg-[#121212] border-b border-[#282828]">
      <div className="container mx-auto px-4">
        <div className="flex items-center justify-between h-20">
          {/* Logo */}
          <Link href="/" className="flex items-center space-x-3">
            <Image src="/nodestrich_logo_white.svg" alt="Nodestrich" width={120} height={32} className="h-8 w-auto" />
          </Link>

          {/* Desktop Navigation */}
          <div className="hidden lg:flex items-center space-x-6">
            <div className="flex space-x-4">
              {navItems.map((item) => (
                <Link
                  key={item.href}
                  href={item.href}
                  className={`px-4 py-2 rounded-md text-base font-medium transition-colors ${
                    pathname === item.href
                      ? 'bg-[#f800c1] text-white'
                      : 'text-gray-300 hover:text-[#f800c1] hover:bg-[#282828]'
                  }`}
                >
                  {item.label}
                </Link>
              ))}
            </div>
            <div className="w-64">
              <SearchBox />
            </div>
          </div>

          {/* Mobile menu button */}
          <div className="lg:hidden">
            <button
              onClick={() => setIsMenuOpen(!isMenuOpen)}
              className="text-gray-400 hover:text-white focus:outline-none focus:text-white"
            >
              <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                {isMenuOpen ? (
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                ) : (
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />
                )}
              </svg>
            </button>
          </div>
        </div>

        {/* Mobile Navigation */}
        {isMenuOpen && (
          <div className="lg:hidden">
            <div className="px-2 pt-2 pb-3 space-y-1 border-t border-[#282828]">
              {navItems.map((item) => (
                <Link
                  key={item.href}
                  href={item.href}
                  className={`block px-4 py-3 rounded-md text-base font-medium transition-colors ${
                    pathname === item.href
                      ? 'bg-[#f800c1] text-white'
                      : 'text-gray-300 hover:text-[#f800c1] hover:bg-[#282828]'
                  }`}
                  onClick={() => setIsMenuOpen(false)}
                >
                  {item.label}
                </Link>
              ))}
              <div className="px-2 pt-2">
                <SearchBox />
              </div>
            </div>
          </div>
        )}
      </div>
    </nav>
  );
};

export default Navigation;