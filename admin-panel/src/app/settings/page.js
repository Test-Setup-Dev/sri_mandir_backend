'use client';

import { useState, useEffect } from 'react';
import api from '@/lib/axios';
import toast from 'react-hot-toast';
import { 
  Settings, 
  Save, 
  Loader2, 
  Info, 
  ShieldCheck, 
  FileText 
} from 'lucide-react';
import styles from './Settings.module.css';

const TABS = [
  { id: 'about', label: 'About Us', icon: Info, endpoint: '/about-us', saveEndpoint: '/about-us/save' },
  { id: 'privacy', label: 'Privacy Policy', icon: ShieldCheck, endpoint: '/privacy-policy', saveEndpoint: '/privacy-policy/save' },
  { id: 'terms', label: 'Terms & Conditions', icon: FileText, endpoint: '/terms-conditions', saveEndpoint: '/terms-conditions/save' },
];

export default function SettingsPage() {
  const [activeTab, setActiveTab] = useState(TABS[0]);
  const [formData, setFormData] = useState({ title: '', description: '' });
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    fetchContent();
  }, [activeTab]);

  const fetchContent = async () => {
    try {
      setLoading(true);
      const response = await api.get(activeTab.endpoint);
      if (response.data.status) {
        setFormData({
          title: response.data.data?.title || '',
          description: response.data.data?.description || '',
        });
      } else {
        setFormData({ title: '', description: '' });
      }
    } catch (error) {
      console.error('Error fetching settings content:', error);
      setFormData({ title: '', description: '' });
    } finally {
      setLoading(false);
    }
  };

  const handleSave = async (e) => {
    e.preventDefault();
    try {
      setSaving(true);
      const response = await api.post(activeTab.saveEndpoint, formData);
      if (response.data.status) {
        toast.success(`${activeTab.label} updated successfully!`);
      }
    } catch (error) {
      console.error('Error saving settings:', error);
      toast.error('Failed to save settings. Please try again.');
    } finally {
      setSaving(false);
    }
  };

  return (
    <div className={styles.container}>
      <header className={styles.header}>
        <h1 className={styles.title}>System Settings</h1>
        <p className={styles.subtitle}>Configure application content and legal documents</p>
      </header>

      <div className={styles.tabs}>
        {TABS.map((tab) => (
          <button
            key={tab.id}
            className={`${styles.tab} ${activeTab.id === tab.id ? styles.activeTab : ''}`}
            onClick={() => setActiveTab(tab)}
          >
            <tab.icon size={18} style={{ display: 'inline', marginRight: '8px', verticalAlign: 'middle' }} />
            {tab.label}
          </button>
        ))}
      </div>

      {loading ? (
        <div className={styles.loader}>
          <Loader2 className="animate-spin" size={40} />
          <p>Loading {activeTab.label} content...</p>
        </div>
      ) : (
        <div className={`glass-card ${styles.formCard}`}>
          <form onSubmit={handleSave}>
            <div className={styles.formGroup}>
              <label>Page Title</label>
              <input 
                type="text" 
                value={formData.title}
                onChange={(e) => setFormData({ ...formData, title: e.target.value })}
                placeholder={`Enter ${activeTab.label} title`}
                required
              />
            </div>
            
            <div className={styles.formGroup}>
              <label>Description / Content</label>
              <textarea 
                value={formData.description}
                onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                placeholder={`Enter ${activeTab.label} content here...`}
                required
              />
            </div>

            <button type="submit" className={styles.saveBtn} disabled={saving}>
              {saving ? (
                <Loader2 className="animate-spin" size={20} />
              ) : (
                <Save size={20} />
              )}
              {saving ? 'Saving...' : 'Save Changes'}
            </button>
          </form>
        </div>
      )}
    </div>
  );
}
