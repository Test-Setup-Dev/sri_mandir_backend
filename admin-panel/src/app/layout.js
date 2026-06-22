'use client';

import { Toaster } from 'react-hot-toast';
import { usePathname } from 'next/navigation';
import './globals.css';
import AdminLayout from '@/components/AdminLayout';

export default function RootLayout({ children }) {
  const pathname = usePathname();
  const normalizedPathname = pathname?.replace(/\/+$/, '') || '/';
  const isLoginPage = normalizedPathname === '/login';

  return (
    <html lang="en">
      <body>
        <Toaster 
          position="top-right"
          toastOptions={{
            style: {
              background: '#1e293b',
              color: '#fff',
              border: '1px solid rgba(255, 255, 255, 0.1)',
            },
          }}
        />
        {isLoginPage ? (
          children
        ) : (
          <AdminLayout>{children}</AdminLayout>
        )}
      </body>
    </html>
  );
}
