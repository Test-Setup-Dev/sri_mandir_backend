'use client';

import Sidebar from './Sidebar';
import Navbar from './Navbar';
import styles from './AdminLayout.module.css';

export default function AdminLayout({ children }) {
  return (
    <div className={styles.layout}>
      <Sidebar />
      <div className={styles.main}>
        <Navbar />
        <main className={styles.content}>
          {children}
        </main>
      </div>
    </div>
  );
}
