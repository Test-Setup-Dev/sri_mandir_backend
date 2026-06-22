import Link from 'next/link';
import { usePathname, useRouter } from 'next/navigation';
import { 
  LayoutDashboard, 
  Users, 
  Image as ImageIcon, 
  Flag, 
  HeartHandshake, 
  Layers, 
  FileText, 
  Bell,
  Settings, 
  LogOut,
  HelpCircle
} from 'lucide-react';
import styles from './Sidebar.module.css';
import toast from 'react-hot-toast';
import { authService } from '@/lib/auth';

const menuItems = [
  { icon: LayoutDashboard, label: 'Dashboard', href: '/' },
  { icon: Users, label: 'Users', href: '/users' },
  { icon: Bell, label: 'Notifications', href: '/notifications' },
  { icon: ImageIcon, label: 'Media', href: '/media' },
  { icon: Flag, label: 'Banners', href: '/banners' },
  { icon: Layers, label: 'Categories', href: '/categories' },
  { icon: HeartHandshake, label: 'Donations', href: '/donations' },
  { icon: FileText, label: 'Blogs', href: '/blogs' },
  { icon: HelpCircle, label: 'Support', href: '/support' },
  { icon: Settings, label: 'Settings', href: '/settings' },
];

export default function Sidebar() {
  const pathname = usePathname();
  const router = useRouter();

  const handleLogout = () => {
    if (confirm('Are you sure you want to logout?')) {
      authService.logout();
      toast.success('Logged out successfully');
      router.push('/login');
    }
  };

  return (
    <aside className={styles.sidebar}>
      <div className={styles.logo}>
        <div className={styles.logoIcon}>SL</div>
        <span className={styles.logoText}>Sanatan<span className={styles.gold}>Lok</span></span>
      </div>

      <nav className={styles.nav}>
        {menuItems.map((item) => {
          const isActive = pathname === item.href;
          return (
            <Link 
              key={item.href} 
              href={item.href}
              className={`${styles.navItem} ${isActive ? styles.active : ''}`}
            >
              <item.icon size={20} />
              <span>{item.label}</span>
            </Link>
          );
        })}
      </nav>

      <div className={styles.footer}>
        <button className={styles.logoutBtn} onClick={handleLogout}>
          <LogOut size={20} />
          <span>Logout</span>
        </button>
      </div>
    </aside>
  );
}
