'use client';

import { useState, useEffect } from 'react';
import { 
  Users, 
  Image as ImageIcon, 
  HeartHandshake, 
  TrendingUp, 
  ArrowUpRight, 
  ArrowDownRight,
  Loader2 
} from 'lucide-react';
import { motion } from 'framer-motion';
import api from '@/lib/axios';
import { getImageUrl } from '@/lib/utils';
import styles from './Dashboard.module.css';

const stats = [
  { 
    label: 'Total Users', 
    value: '1,284', 
    icon: Users, 
    trend: '+12%', 
    trendUp: true,
    color: '#8b5cf6' 
  },
  { 
    label: 'Media Items', 
    value: '452', 
    icon: ImageIcon, 
    trend: '+5%', 
    trendUp: true,
    color: '#f59e0b' 
  },
  { 
    label: 'Total Donations', 
    value: '₹84,200', 
    icon: HeartHandshake, 
    trend: '-2%', 
    trendUp: false,
    color: '#10b981' 
  },
  { 
    label: 'Growth', 
    value: '24.5%', 
    icon: TrendingUp, 
    trend: '+18%', 
    trendUp: true,
    color: '#ec4899' 
  },
];

export default function Dashboard() {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchStats();
  }, []);

  const fetchStats = async () => {
    try {
      const response = await api.get('/admin/stats');
      if (response.data.status) {
        setData(response.data.data);
      }
    } catch (error) {
      console.error('Error fetching stats:', error);
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return (
      <div className="flex-center" style={{ minHeight: '60vh' }}>
        <Loader2 className="spin" size={40} color="var(--primary)" />
      </div>
    );
  }

  const { stats, recentDonations, popularMedia } = data || { stats: [], recentDonations: [], popularMedia: [] };

  return (
    <div className={styles.dashboard}>
      <header className={styles.header}>
        <div>
          <h1 className={styles.title}>Welcome back, <span className="text-gradient">Admin</span></h1>
          <p className={styles.subtitle}>Here is what's happening with Sanatan Lok today.</p>
        </div>
        <button className="bg-gradient-primary" style={{ padding: '0.75rem 1.5rem', borderRadius: 'var(--radius-md)', color: 'white', fontWeight: '500' }}>
          Download Report
        </button>
      </header>

      <div className={styles.statsGrid}>
        {stats.map((stat, index) => {
          const Icon = index === 0 ? Users : index === 1 ? ImageIcon : index === 2 ? HeartHandshake : TrendingUp;
          return (
            <motion.div 
              key={stat.label}
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: index * 0.1 }}
              className="glass-card"
            >
              <div className={styles.statHeader}>
                <div className={styles.statIcon} style={{ background: `${stat.color}15`, color: stat.color }}>
                  <Icon size={24} />
                </div>
                <div className={`${styles.trend} ${stat.trendUp ? styles.up : styles.down}`}>
                  {stat.trendUp ? <ArrowUpRight size={16} /> : <ArrowDownRight size={16} />}
                  <span>{stat.trend}</span>
                </div>
              </div>
              <div className={styles.statBody}>
                <p className={styles.statLabel}>{stat.label}</p>
                <h2 className={styles.statValue}>{stat.value}</h2>
              </div>
            </motion.div>
          );
        })}
      </div>

      <div className={styles.mainGrid}>
        <section className={`${styles.section} glass-card`}>
          <div className={styles.sectionHeader}>
            <h3>Recent Donations</h3>
            <button className={styles.viewAll}>View All</button>
          </div>
          <div className={styles.tableWrapper}>
            <table className={styles.table}>
              <thead>
                <tr>
                  <th>Donor</th>
                  <th>Amount</th>
                  <th>Status</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
                {recentDonations.map((donation) => (
                  <tr key={donation.id}>
                    <td>
                      <div className={styles.donor}>
                        <div className={styles.miniAvatar}>{donation.donor[0]}</div>
                        <span>{donation.donor}</span>
                      </div>
                    </td>
                    <td>{donation.amount}</td>
                    <td><span className={styles.statusBadge}>{donation.status}</span></td>
                    <td>{donation.date}</td>
                  </tr>
                ))}
              </tbody>
            </table>
            {recentDonations.length === 0 && <p style={{ textAlign: 'center', padding: '2rem', color: '#64748b' }}>No recent donations found.</p>}
          </div>
        </section>

        <section className={`${styles.section} glass-card`}>
          <div className={styles.sectionHeader}>
            <h3>Popular Media</h3>
            <button className={styles.viewAll}>View All</button>
          </div>
          <div className={styles.mediaList}>
            {popularMedia.map((item) => (
              <div key={item.id} className={styles.mediaItem}>
                <div className={styles.mediaThumb}>
                  {item.thumbnail && <img src={getImageUrl(item.thumbnail)} alt={item.title} style={{ width: '100%', height: '100%', objectFit: 'cover', borderRadius: 'var(--radius-sm)' }} />}
                </div>
                <div className={styles.mediaInfo}>
                  <h4>{item.title}</h4>
                  <p>{item.views} views • {item.likes} likes</p>
                </div>
                <button className={styles.moreBtn}>Edit</button>
              </div>
            ))}
            {popularMedia.length === 0 && <p style={{ textAlign: 'center', padding: '2rem', color: '#64748b' }}>No popular media found.</p>}
          </div>
        </section>
      </div>
    </div>
  );
}
