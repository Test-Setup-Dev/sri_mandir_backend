'use client';

import { useState, useEffect } from 'react';
import api from '@/lib/axios';
import toast from 'react-hot-toast';
import { 
  ImageIcon, 
  Search, 
  Filter, 
  Plus, 
  Trash2, 
  Edit, 
  Play, 
  Music, 
  FileText,
  Eye,
  ExternalLink,
  Loader2,
  X
} from 'lucide-react';
import { getImageUrl, getMediaUrl } from '@/lib/utils';
import styles from './Media.module.css';

export default function MediaPage() {
  const [media, setMedia] = useState([]);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  const [filterType, setFilterType] = useState('all');
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [previewItem, setPreviewItem] = useState(null);
  const [editingMedia, setEditingMedia] = useState(null);
  const [categories, setCategories] = useState([]);
  const [formData, setFormData] = useState({
    title: '',
    artist: '',
    type: 'video',
    categorie_id: '',
    media_url: '',
    content: '',
    duration: '',
    isFeatured: false,
    thumbnail: null,
    media_file: null
  });

  useEffect(() => {
    fetchMedia();
    fetchCategories();
  }, [filterType]);

  const fetchMedia = async () => {
    try {
      setLoading(true);
      const typeParam = filterType !== 'all' ? `?type=${filterType}` : '';
      const response = await api.get(`/admin/media${typeParam}`);
      setMedia(response.data.data.data);
    } catch (error) {
      console.error('Error fetching media:', error);
    } finally {
      setLoading(false);
    }
  };

  const fetchCategories = async () => {
    try {
      const response = await api.get('/category');
      setCategories(response.data.data || response.data);
    } catch (error) {
      console.error('Error fetching categories:', error);
    }
  };

  const handleDelete = async (id) => {
    if (window.confirm('Are you sure you want to delete this media item?')) {
      try {
        await api.delete(`/admin/media/${id}`);
        toast.success('Media item deleted successfully');
        fetchMedia();
      } catch (error) {
        console.error('Error deleting media:', error);
        toast.error('Failed to delete media item');
      }
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    if ((formData.type === 'audio' || formData.type === 'video') && !formData.media_file && !formData.media_url) {
      toast.error(`Please upload a ${formData.type} file or provide a media URL.`);
      return;
    }

    if (formData.type === 'text' && !formData.content.trim()) {
      toast.error('Please add text content so users can read this media item.');
      return;
    }

    const data = new FormData();
    Object.keys(formData).forEach(key => {
      if (formData[key] !== null) {
        // Handle boolean isFeatured
        if (key === 'isFeatured') {
          data.append(key, formData[key] ? '1' : '0');
        } else {
          data.append(key, formData[key]);
        }
      }
    });

    try {
      setLoading(true);
      if (editingMedia) {
        // Use POST with _method=PUT or just POST to our special route
        await api.post(`/admin/media/${editingMedia.id}`, data, {
          headers: { 'Content-Type': 'multipart/form-data' }
        });
        toast.success('Media updated successfully!');
      } else {
        await api.post('/admin/media', data, {
          headers: { 'Content-Type': 'multipart/form-data' }
        });
        toast.success('Media added successfully!');
      }
      setIsModalOpen(false);
      resetForm();
      fetchMedia();
    } catch (error) {
      console.error('Error saving media:', error);
      toast.error('Failed to save media. Please check the fields.');
    } finally {
      setLoading(false);
    }
  };

  const openEditModal = (item) => {
    setEditingMedia(item);
    setFormData({
      title: item.title,
      artist: item.artist || '',
      type: item.type,
      categorie_id: item.categorie_id,
      media_url: item.mediaUrl && item.mediaUrl.startsWith('http') ? item.mediaUrl : '',
      content: item.content || '',
      duration: item.duration || '',
      isFeatured: !!item.isFeatured,
      thumbnail: null,
      media_file: null
    });
    setIsModalOpen(true);
  };

  const openCreateModal = () => {
    resetForm();
    setIsModalOpen(true);
  };

  const resetForm = () => {
    setEditingMedia(null);
    setFormData({
      title: '',
      artist: '',
      type: 'video',
      categorie_id: '',
      media_url: '',
      content: '',
      duration: '',
      isFeatured: false,
      thumbnail: null,
      media_file: null
    });
  };

  const handleTypeChange = (type) => {
    setFormData((current) => ({
      ...current,
      type,
      content: type === 'text' ? current.content : '',
      media_file: null,
      media_url: type === 'text' ? '' : current.media_url,
      duration: type === 'text' ? '' : current.duration
    }));
  };

  const getPreviewUrl = (item) => getMediaUrl(item?.mediaUrl || '');

  const getYoutubeEmbedUrl = (url) => {
    if (!url) return null;

    const shortMatch = url.match(/youtu\.be\/([^?&/]+)/i);
    if (shortMatch) return `https://www.youtube.com/embed/${shortMatch[1]}`;

    const fullMatch = url.match(/[?&]v=([^?&/]+)/i);
    if (fullMatch) return `https://www.youtube.com/embed/${fullMatch[1]}`;

    const embedMatch = url.match(/youtube\.com\/embed\/([^?&/]+)/i);
    if (embedMatch) return `https://www.youtube.com/embed/${embedMatch[1]}`;

    return null;
  };

  const renderPreviewContent = (item) => {
    if (!item) return null;

    const mediaUrl = getPreviewUrl(item);
    const youtubeEmbedUrl = getYoutubeEmbedUrl(mediaUrl);

    if (item.type === 'video') {
      if (youtubeEmbedUrl) {
        return (
          <iframe
            className={styles.previewFrame}
            src={youtubeEmbedUrl}
            title={item.title}
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
            allowFullScreen
          />
        );
      }

      if (mediaUrl) {
        return (
          <video className={styles.previewMedia} controls preload="metadata">
            <source src={mediaUrl} />
            Your browser does not support video playback.
          </video>
        );
      }
    }

    if (item.type === 'audio') {
      if (mediaUrl) {
        return (
          <audio className={styles.previewAudio} controls preload="metadata">
            <source src={mediaUrl} />
            Your browser does not support audio playback.
          </audio>
        );
      }
    }

    if (item.type === 'text') {
      return (
        <div className={styles.previewText}>
          {item.content?.trim() ? item.content : 'No text content available for this item.'}
        </div>
      );
    }

    return <p className={styles.previewFallback}>Preview is not available for this media item.</p>;
  };

  const getIcon = (type) => {
    switch (type) {
      case 'video': return <Play size={18} />;
      case 'audio': return <Music size={18} />;
      case 'text': return <FileText size={18} />;
      default: return <ImageIcon size={18} />;
    }
  };

  const filteredMedia = media.filter(item => 
    item.title.toLowerCase().includes(searchTerm.toLowerCase()) ||
    item.artist?.toLowerCase().includes(searchTerm.toLowerCase())
  );

  return (
    <div className={styles.container}>
      <header className={styles.header}>
        <div>
          <h1 className={styles.title}>Media Management</h1>
          <p className={styles.subtitle}>Manage your videos, audios, and religious texts</p>
        </div>
        <button className="btn-primary" onClick={openCreateModal}>
          <Plus size={20} />
          <span>Add New Media</span>
        </button>
      </header>

      <div className={`glass-card ${styles.controls}`}>
        <div className={styles.searchBox}>
          <Search size={20} className={styles.searchIcon} />
          <input 
            type="text" 
            placeholder="Search by title or artist..." 
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
          />
        </div>
        <div className={styles.filters}>
          <div className={styles.filterItem}>
            <Filter size={18} />
            <select value={filterType} onChange={(e) => setFilterType(e.target.value)}>
              <option value="all">All Types</option>
              <option value="video">Videos</option>
              <option value="audio">Audios</option>
              <option value="text">Texts</option>
            </select>
          </div>
        </div>
      </div>

      {loading && !media.length ? (
        <div className={styles.loader}>
          <Loader2 className="animate-spin" size={40} />
          <p>Loading media...</p>
        </div>
      ) : (
        <div className={styles.grid}>
          {filteredMedia.map((item) => (
            <div key={item.id} className={`glass-card ${styles.mediaCard}`}>
              <div className={styles.cardThumb}>
                {item.thumbnailUrl ? (
                  <img src={getImageUrl(item.thumbnailUrl)} alt={item.title} />
                ) : (
                  <div className={styles.placeholderThumb}>
                    {getIcon(item.type)}
                  </div>
                )}
                <div className={styles.typeBadge}>
                  {getIcon(item.type)}
                </div>
              </div>
              <div className={styles.cardContent}>
                <h3 className={styles.itemTitle}>{item.title}</h3>
                <p className={styles.itemArtist}>{item.artist || 'Unknown Artist'}</p>
                <div className={styles.cardFooter}>
                  <span className={styles.categoryName}>{item.category?.name || 'Uncategorized'}</span>
                  <div className={styles.actions}>
                    <button className={styles.actionBtn} title="Edit" onClick={() => openEditModal(item)}>
                      <Edit size={16} />
                    </button>
                    <button className={styles.actionBtn} title="Preview" onClick={() => setPreviewItem(item)}>
                      <Eye size={16} />
                    </button>
                    <button className={`${styles.actionBtn} ${styles.delete}`} title="Delete" onClick={() => handleDelete(item.id)}>
                      <Trash2 size={16} />
                    </button>
                  </div>
                </div>
              </div>
            </div>
          ))}
        </div>
      )}

      {/* Add Media Modal */}
      {isModalOpen && (
        <div className={styles.modalOverlay}>
          <div className={`glass-card ${styles.modal}`}>
            <div className={styles.modalHeader}>
              <h2>{editingMedia ? 'Edit Media' : 'Add New Media'}</h2>
              <button onClick={() => { setIsModalOpen(false); resetForm(); }}><X size={24} /></button>
            </div>
            <form onSubmit={handleSubmit} className={styles.form}>
              <div className={styles.formGrid}>
                <div className={styles.formGroup}>
                  <label>Title</label>
                  <input 
                    type="text" 
                    required 
                    value={formData.title}
                    onChange={(e) => setFormData({...formData, title: e.target.value})}
                  />
                </div>
                <div className={styles.formGroup}>
                  <label>Artist / Author</label>
                  <input 
                    type="text" 
                    value={formData.artist}
                    onChange={(e) => setFormData({...formData, artist: e.target.value})}
                  />
                </div>
                <div className={styles.formGroup}>
                  <label>Type</label>
                  <select 
                    value={formData.type}
                    onChange={(e) => handleTypeChange(e.target.value)}
                  >
                    <option value="video">Video</option>
                    <option value="audio">Audio</option>
                    <option value="text">Text</option>
                  </select>
                </div>
                <div className={styles.formGroup}>
                  <label>Category</label>
                  <select 
                    required
                    value={formData.categorie_id}
                    onChange={(e) => setFormData({...formData, categorie_id: e.target.value})}
                  >
                    <option value="">Select Category</option>
                    {categories.map(cat => (
                      <option key={cat.id} value={cat.id}>{cat.name}</option>
                    ))}
                  </select>
                </div>
                <div className={styles.formGroup}>
                  <label>Thumbnail Image</label>
                  <input 
                    type="file" 
                    accept="image/*"
                    onChange={(e) => setFormData({...formData, thumbnail: e.target.files[0]})}
                  />
                </div>
                <div className={styles.formGroup}>
                  <label>{formData.type === 'text' ? 'Content / Text' : 'Media File'}</label>
                  {formData.type === 'text' ? (
                    <textarea 
                      required
                      value={formData.content}
                      onChange={(e) => setFormData({...formData, content: e.target.value})}
                    />
                  ) : (
                    <input 
                      type="file" 
                      accept={formData.type === 'audio' ? 'audio/*' : 'video/*'}
                      onChange={(e) => setFormData({...formData, media_file: e.target.files[0]})}
                    />
                  )}
                </div>
                <div className={styles.formGroup}>
                  <label>Or Media URL (YouTube/External)</label>
                  <input 
                    type="url" 
                    value={formData.media_url}
                    onChange={(e) => setFormData({...formData, media_url: e.target.value})}
                  />
                </div>
                <div className={styles.formGroup}>
                  <label>Duration (e.g. 05:30)</label>
                  <input 
                    type="text" 
                    value={formData.duration}
                    onChange={(e) => setFormData({...formData, duration: e.target.value})}
                  />
                </div>
              </div>
              <div className={styles.modalFooter}>
                <button type="button" className="btn-secondary" onClick={() => { setIsModalOpen(false); resetForm(); }}>Cancel</button>
                <button type="submit" className="btn-primary" disabled={loading}>
                  {loading ? (editingMedia ? 'Updating...' : 'Adding...') : (editingMedia ? 'Update Media' : 'Add Media')}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {previewItem && (
        <div className={styles.modalOverlay}>
          <div className={`glass-card ${styles.previewModal}`}>
            <div className={styles.modalHeader}>
              <div>
                <h2>{previewItem.title}</h2>
                <p className={styles.previewMeta}>
                  {previewItem.type} {previewItem.artist ? `- ${previewItem.artist}` : ''}
                </p>
              </div>
              <button onClick={() => setPreviewItem(null)}><X size={24} /></button>
            </div>
            <div className={styles.previewBody}>
              {renderPreviewContent(previewItem)}
              {previewItem.mediaUrl && previewItem.type !== 'text' && (
                <a
                  className={styles.previewLink}
                  href={getPreviewUrl(previewItem)}
                  target="_blank"
                  rel="noreferrer"
                >
                  <ExternalLink size={16} />
                  <span>Open media in new tab</span>
                </a>
              )}
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
