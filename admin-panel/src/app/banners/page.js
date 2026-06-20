'use client';

import { useState, useEffect } from 'react';
import { 
  Plus, 
  Search, 
  MoreVertical, 
  Edit2, 
  Trash2, 
  Image as ImageIcon,
  Loader2
} from 'lucide-react';
import { motion, AnimatePresence } from 'framer-motion';
import api from '@/lib/axios';
import toast from 'react-hot-toast';
import styles from './Banners.module.css';

export default function BannersPage() {
  const [banners, setBanners] = useState([]);
  const [loading, setLoading] = useState(true);
  const [showModal, setShowModal] = useState(false);
  const [searchTerm, setSearchTerm] = useState('');

  useEffect(() => {
    fetchBanners();
  }, []);

  const fetchBanners = async () => {
    try {
      const response = await api.get('/admin/banners');
      setBanners(response.data.banners || []);
    } catch (error) {
      console.error('Error fetching banners:', error);
    } finally {
      setLoading(false);
    }
  };

  const filteredBanners = banners.filter(banner => 
    banner.title?.toLowerCase().includes(searchTerm.toLowerCase())
  );

  return (
    <div className={styles.container}>
      <header className={styles.header}>
        <div>
          <h1 className={styles.title}>Banner Management</h1>
          <p className={styles.subtitle}>Manage your app's home screen banners</p>
        </div>
        <button 
          className="bg-gradient-primary flex-center" 
          style={{ gap: '0.5rem', padding: '0.75rem 1.5rem', borderRadius: 'var(--radius-md)', color: 'white', fontWeight: '500' }}
          onClick={() => setShowModal(true)}
        >
          <Plus size={20} />
          Add New Banner
        </button>
      </header>

      <div className={`${styles.searchBar} glass`}>
        <Search size={20} className={styles.searchIcon} />
        <input 
          type="text" 
          placeholder="Search banners by title..." 
          value={searchTerm}
          onChange={(e) => setSearchTerm(e.target.value)}
        />
      </div>

      {loading ? (
        <div className="flex-center" style={{ minHeight: '400px' }}>
          <Loader2 className={styles.spin} size={40} color="var(--primary)" />
        </div>
      ) : (
        <div className={styles.grid}>
          {filteredBanners.map((banner, index) => (
            <motion.div 
              key={banner.id}
              initial={{ opacity: 0, scale: 0.9 }}
              animate={{ opacity: 1, scale: 1 }}
              transition={{ delay: index * 0.05 }}
              className={`${styles.bannerCard} glass-card`}
            >
              <div className={styles.bannerImage}>
                {banner.image ? (
                  <img src={banner.image} alt={banner.title} />
                ) : (
                  <div className={styles.placeholder}>
                    <ImageIcon size={40} />
                  </div>
                )}
                <div className={styles.actions}>
                  <button className={styles.actionBtn} title="Edit"><Edit2 size={16} /></button>
                  <button className={`${styles.actionBtn} ${styles.delete}`} title="Delete"><Trash2 size={16} /></button>
                </div>
              </div>
              <div className={styles.bannerInfo}>
                <h3>{banner.title || 'Untitled Banner'}</h3>
                <p>{banner.link || 'No link attached'}</p>
                <span className={styles.statusBadge}>Active</span>
              </div>
            </motion.div>
          ))}
        </div>
      )}

      {/* Modal Placeholder */}
      <AnimatePresence>
        {showModal && (
          <motion.div 
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            className={styles.modalOverlay}
            onClick={() => setShowModal(false)}
          >
            <motion.div 
              initial={{ y: 50, opacity: 0 }}
              animate={{ y: 0, opacity: 1 }}
              exit={{ y: 50, opacity: 0 }}
              className={`${styles.modal} glass`}
              onClick={e => e.stopPropagation()}
            >
              <h2>Add New Banner</h2>
              <form className={styles.form}>
                <div className={styles.field}>
                  <label>Banner Title</label>
                  <input type="text" placeholder="Enter banner title" />
                </div>
                <div className={styles.field}>
                  <label>Target URL</label>
                  <input type="text" placeholder="https://..." />
                </div>
                <div className={styles.field}>
                  <label>Banner Image</label>
                  <div className={styles.fileUpload}>
                    <ImageIcon size={30} />
                    <span>Click to upload banner</span>
                  </div>
                </div>
                <div className={styles.modalActions}>
                  <button type="button" onClick={() => setShowModal(false)} className={styles.cancelBtn}>Cancel</button>
                  <button type="submit" className="bg-gradient-primary">Save Banner</button>
                </div>
              </form>
            </motion.div>
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  );
}
