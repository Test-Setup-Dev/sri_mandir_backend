'use client';

import { useState, useEffect } from 'react';
import api from '@/lib/axios';
import toast from 'react-hot-toast';
import { 
  FileText, 
  Search, 
  Plus, 
  Edit, 
  Trash2, 
  Calendar,
  Image as ImageIcon,
  User,
  Loader2,
  X,
  Eye,
  CheckCircle,
  Clock
} from 'lucide-react';
import { getImageUrl } from '@/lib/utils';
import styles from './Blogs.module.css';

const normalizeImages = (images) => {
  if (Array.isArray(images)) return images.filter(Boolean);
  if (!images) return [];

  if (typeof images === 'string') {
    try {
      const parsed = JSON.parse(images);
      if (Array.isArray(parsed)) return parsed.filter(Boolean);
    } catch (error) {
      return [images].filter(Boolean);
    }
  }

  return [];
};

const normalizeBlog = (blog) => ({
  ...blog,
  id: blog?.id ?? blog?._id,
  images: normalizeImages(blog?.images),
});

const extractBlogsFromResponse = (payload) => {
  const rawBlogs =
    payload?.data?.data ??
    payload?.data ??
    payload?.blogs ??
    [];

  return Array.isArray(rawBlogs) ? rawBlogs.map(normalizeBlog) : [];
};

export default function BlogsPage() {
  const [blogs, setBlogs] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [searchTerm, setSearchTerm] = useState('');
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingBlog, setEditingBlog] = useState(null);
  
  const [formData, setFormData] = useState({
    title: '',
    subtitle: '',
    content: '',
    category: '',
    author_name: '',
    status: 'published',
    blog_images: null,
    author_image: null
  });

  const getStatusColor = (status) => {
    switch(status?.toLowerCase()) {
      case 'published':
      case 'active':
        return '#10b981';
      case 'draft':
      case 'inactive':
        return '#f59e0b';
      default:
        return '#6b7280';
    }
  };

  useEffect(() => {
    fetchBlogs();
  }, []);

  const fetchBlogs = async () => {
    try {
      setLoading(true);
      setError('');
      const publicResponse = await api.get('/blogs');
      const blogsList = extractBlogsFromResponse(publicResponse.data);
      setBlogs(blogsList);
    } catch (error) {
      console.error('Error fetching blogs:', error);
      const message = error.response?.data?.message || 'Failed to load blogs';
      setError(message);
      toast.error(message);
      setBlogs([]);
    } finally {
      setLoading(false);
    }
  };

  const handleDelete = async (id) => {
    if (window.confirm('Are you sure you want to delete this blog?')) {
      try {
        await api.delete(`/admin/blogs/${id}`);
        toast.success('Blog deleted successfully');
        fetchBlogs();
      } catch (error) {
        console.error('Error deleting blog:', error);
        const message = error.response?.data?.message || 'Failed to delete blog';
        toast.error(message);
      }
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    const data = new FormData();
    
    Object.keys(formData).forEach(key => {
      if (formData[key] !== null) {
        if (key === 'blog_images' && formData[key] instanceof FileList) {
          Array.from(formData[key]).forEach(file => data.append('blog_images[]', file));
        } else {
          data.append(key, formData[key]);
        }
      }
    });

    try {
      setLoading(true);
      if (editingBlog) {
        // Use POST with _method=PUT to support file uploads in Laravel via PUT
        data.append('_method', 'PUT');
        await api.post(`/admin/blogs/${editingBlog.id}`, data, {
          headers: { 'Content-Type': 'multipart/form-data' }
        });
        toast.success('Blog updated successfully');
      } else {
        await api.post('/admin/blogs', data, {
          headers: { 'Content-Type': 'multipart/form-data' }
        });
        toast.success('Blog created successfully');
      }
      setIsModalOpen(false);
      resetForm();
      fetchBlogs();
    } catch (error) {
      console.error('Error saving blog:', error);
      const message = error.response?.data?.message || 'Failed to save blog';
      toast.error(message);
    } finally {
      setLoading(false);
    }
  };

  const openEditModal = (blog) => {
    setEditingBlog(blog);
    setFormData({
      title: blog.title || '',
      subtitle: blog.subtitle || '',
      content: blog.content || '',
      category: blog.category || '',
      author_name: blog.author_name || '',
      status: blog.status === 'active' ? 'published' : 'draft',
      blog_images: null, // Don't pre-fill files
      author_image: null
    });
    setIsModalOpen(true);
  };

  const resetForm = () => {
    setEditingBlog(null);
    setFormData({
      title: '',
      subtitle: '',
      content: '',
      category: '',
      author_name: '',
      status: 'published',
      blog_images: null,
      author_image: null
    });
  };

  const filteredBlogs = blogs.filter(blog => 
    blog.title?.toLowerCase().includes(searchTerm.toLowerCase()) ||
    blog.author_name?.toLowerCase().includes(searchTerm.toLowerCase())
  );

  return (
    <div className={styles.container}>
      <header className={styles.header}>
        <div>
          <h1 className={styles.title}>Blog Management</h1>
          <p className={styles.subtitle}>Create and manage your spiritual articles</p>
        </div>
        <button className="btn-primary" onClick={() => { resetForm(); setIsModalOpen(true); }}>
          <Plus size={20} />
          <span>New Article</span>
        </button>
      </header>

      <div className={`glass-card ${styles.controls}`}>
        <div className={styles.searchBox}>
          <Search size={20} className={styles.searchIcon} />
          <input 
            type="text" 
            placeholder="Search by title or author..." 
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
          />
        </div>
      </div>

      {loading && !blogs.length ? (
        <div className={styles.loader}>
          <Loader2 className="animate-spin" size={40} />
          <p>Loading blogs...</p>
        </div>
      ) : error ? (
        <div className={styles.loader}>
          <p>{error}</p>
        </div>
      ) : !filteredBlogs.length ? (
        <div className={styles.loader}>
          <p>No blogs found.</p>
        </div>
      ) : (
        <div className={styles.blogList}>
          {filteredBlogs.map((blog) => (
            <div key={blog.id} className={`glass-card ${styles.blogCard}`}>
              <div className={styles.blogImage}>
                {blog.images && blog.images[0] ? (
                  <img src={getImageUrl(blog.images[0])} alt={blog.title} />
                ) : (
                  <div className={styles.placeholderImage}>
                    <FileText size={40} />
                  </div>
                )}
                <div className={styles.statusBadge} style={{ backgroundColor: getStatusColor(blog.status) + '20', color: getStatusColor(blog.status) }}>
                  {blog.status}
                </div>
              </div>
              <div className={styles.blogContent}>
                <div className={styles.blogMeta}>
                  <span className={styles.blogCategory}>{blog.category}</span>
                  <span className={styles.blogDate}>
                    <Calendar size={14} />
                    {new Date(blog.publish_date || blog.created_at).toLocaleDateString()}
                  </span>
                </div>
                <h3 className={styles.blogTitle}>{blog.title}</h3>
                <p className={styles.blogExcerpt}>{blog.subtitle}</p>
                <div className={styles.authorInfo}>
                  <div className={styles.authorBox}>
                    {blog.author_image ? (
                      <img src={getImageUrl(blog.author_image)} alt={blog.author_name} className={styles.authorImg} />
                    ) : (
                      <div className={styles.authorPlaceholder}><User size={14} /></div>
                    )}
                    <span>{blog.author_name}</span>
                  </div>
                  <div className={styles.blogActions}>
                    <button className={styles.actionBtn} onClick={() => openEditModal(blog)}><Edit size={16} /></button>
                    <button className={`${styles.actionBtn} ${styles.delete}`} onClick={() => handleDelete(blog.id)}><Trash2 size={16} /></button>
                  </div>
                </div>
              </div>
            </div>
          ))}
        </div>
      )}

      {/* Write Blog Modal */}
      {isModalOpen && (
        <div className={styles.modalOverlay}>
          <div className={`glass-card ${styles.modal}`}>
            <div className={styles.modalHeader}>
              <h2>{editingBlog ? 'Edit Blog' : 'Write New Blog'}</h2>
              <button onClick={() => setIsModalOpen(false)}><X size={24} /></button>
            </div>
            <form onSubmit={handleSubmit} className={styles.form}>
              <div className={styles.formGroup}>
                <label>Blog Title</label>
                <input 
                  type="text" 
                  required 
                  placeholder="Enter a compelling title"
                  value={formData.title}
                  onChange={(e) => setFormData({...formData, title: e.target.value})}
                />
              </div>
              <div className={styles.formGroup}>
                <label>Subtitle / Excerpt</label>
                <input 
                  type="text" 
                  placeholder="Short description for preview"
                  value={formData.subtitle}
                  onChange={(e) => setFormData({...formData, subtitle: e.target.value})}
                />
              </div>
              <div className={styles.formRow}>
                <div className={styles.formGroup}>
                  <label>Category</label>
                  <input 
                    type="text" 
                    required 
                    placeholder="e.g. Spirituality, News"
                    value={formData.category}
                    onChange={(e) => setFormData({...formData, category: e.target.value})}
                  />
                </div>
                <div className={styles.formGroup}>
                  <label>Status</label>
                  <select 
                    value={formData.status}
                    onChange={(e) => setFormData({...formData, status: e.target.value})}
                  >
                    <option value="draft">Draft</option>
                    <option value="published">Published</option>
                  </select>
                </div>
              </div>
              <div className={styles.formRow}>
                <div className={styles.formGroup}>
                  <label>Author Name</label>
                  <input 
                    type="text" 
                    required 
                    value={formData.author_name}
                    onChange={(e) => setFormData({...formData, author_name: e.target.value})}
                  />
                </div>
                <div className={styles.formGroup}>
                  <label>Author Image</label>
                  <input 
                    type="file" 
                    accept="image/*"
                    onChange={(e) => setFormData({...formData, author_image: e.target.files[0]})}
                  />
                </div>
              </div>
              <div className={styles.formGroup}>
                <label>Blog Images</label>
                <input 
                  type="file" 
                  multiple 
                  accept="image/*"
                  onChange={(e) => setFormData({...formData, blog_images: e.target.files})}
                />
              </div>
              <div className={styles.formGroup}>
                <label>Content</label>
                <textarea 
                  required
                  placeholder="Write your blog content here..."
                  className={styles.contentArea}
                  value={formData.content}
                  onChange={(e) => setFormData({...formData, content: e.target.value})}
                />
              </div>
              <div className={styles.modalFooter}>
                <button type="button" className="btn-secondary" onClick={() => setIsModalOpen(false)}>Cancel</button>
                <button type="submit" className="btn-primary" disabled={loading}>
                  {loading ? 'Saving...' : (editingBlog ? 'Update Blog' : 'Publish Blog')}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
