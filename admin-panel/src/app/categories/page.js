'use client';

import { useState, useEffect } from 'react';
import api from '@/lib/axios';
import { 
  Layers, 
  Plus, 
  Trash2, 
  Edit, 
  Loader2,
  X,
  Search,
  CheckCircle2
} from 'lucide-react';
import styles from './Categories.module.css';

export default function CategoriesPage() {
  const [categories, setCategories] = useState([]);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingCategory, setEditingCategory] = useState(null);
  const [formData, setFormData] = useState({ name: '' });

  useEffect(() => {
    fetchCategories();
  }, []);

  const fetchCategories = async () => {
    try {
      setLoading(true);
      const response = await api.get('/category');
      setCategories(response.data.data || response.data || []);
    } catch (error) {
      console.error('Error fetching categories:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      setLoading(true);
      if (editingCategory) {
        await api.put(`/category/${editingCategory.id}`, formData);
      } else {
        await api.post('/category', formData);
      }
      setIsModalOpen(false);
      setFormData({ name: '' });
      setEditingCategory(null);
      fetchCategories();
    } catch (error) {
      console.error('Error saving category:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleEdit = (category) => {
    setEditingCategory(category);
    setFormData({ name: category.name });
    setIsModalOpen(true);
  };

  const handleDelete = async (id) => {
    if (confirm('Are you sure? This might affect media items in this category.')) {
      try {
        await api.delete(`/category/${id}`);
        fetchCategories();
      } catch (error) {
        console.error('Error deleting category:', error);
      }
    }
  };

  const filteredCategories = categories.filter(cat => 
    cat.name.toLowerCase().includes(searchTerm.toLowerCase())
  );

  return (
    <div className={styles.container}>
      <header className={styles.header}>
        <div>
          <h1 className={styles.title}>Categories</h1>
          <p className={styles.subtitle}>Organize your media and content into logical sections</p>
        </div>
        <button className="btn-primary" onClick={() => {
          setEditingCategory(null);
          setFormData({ name: '' });
          setIsModalOpen(true);
        }}>
          <Plus size={20} />
          <span>New Category</span>
        </button>
      </header>

      <div className={`glass-card ${styles.controls}`}>
        <div className={styles.searchBox}>
          <Search size={20} className={styles.searchIcon} />
          <input 
            type="text" 
            placeholder="Search categories..." 
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
          />
        </div>
      </div>

      {loading && !categories.length ? (
        <div className={styles.loader}>
          <Loader2 className="animate-spin" size={40} />
          <p>Loading categories...</p>
        </div>
      ) : (
        <div className={styles.categoryGrid}>
          {filteredCategories.map((cat) => (
            <div key={cat.id} className={`glass-card ${styles.categoryCard}`}>
              <div className={styles.cardHeader}>
                <div className={styles.iconBox}><Layers size={24} /></div>
                <div className={styles.actions}>
                  <button className={styles.actionBtn} onClick={() => handleEdit(cat)}><Edit size={16} /></button>
                  <button className={`${styles.actionBtn} ${styles.delete}`} onClick={() => handleDelete(cat.id)}><Trash2 size={16} /></button>
                </div>
              </div>
              <h3 className={styles.catName}>{cat.name}</h3>
              <div className={styles.catMeta}>
                <CheckCircle2 size={14} />
                <span>Active</span>
              </div>
            </div>
          ))}
        </div>
      )}

      {isModalOpen && (
        <div className={styles.modalOverlay}>
          <div className={`glass-card ${styles.modal}`}>
            <div className={styles.modalHeader}>
              <h2>{editingCategory ? 'Edit Category' : 'Create Category'}</h2>
              <button onClick={() => setIsModalOpen(false)}><X size={24} /></button>
            </div>
            <form onSubmit={handleSubmit} className={styles.form}>
              <div className={styles.formGroup}>
                <label>Category Name</label>
                <input 
                  type="text" 
                  required 
                  autoFocus
                  placeholder="e.g. Bhajan, Gita Saar"
                  value={formData.name}
                  onChange={(e) => setFormData({ name: e.target.value })}
                />
              </div>
              <div className={styles.modalFooter}>
                <button type="button" className="btn-secondary" onClick={() => setIsModalOpen(false)}>Cancel</button>
                <button type="submit" className="btn-primary" disabled={loading}>
                  {loading ? 'Saving...' : editingCategory ? 'Update' : 'Create'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
