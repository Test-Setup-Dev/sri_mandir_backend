'use client';

import { useState, useEffect } from 'react';
import api from '@/lib/axios';
import toast from 'react-hot-toast';
import { 
  Users, 
  Search, 
  Mail, 
  MapPin, 
  Calendar,
  Loader2,
  UserCheck,
  UserX,
  Plus,
  Edit,
  Trash2,
  X,
  Lock,
  Camera,
  Phone,
  Bell,
  Send
} from 'lucide-react';
import { getImageUrl } from '@/lib/utils';
import styles from './Users.module.css';

export default function UsersPage() {
  const [users, setUsers] = useState([]);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [isNotificationModalOpen, setIsNotificationModalOpen] = useState(false);
  const [editingUser, setEditingUser] = useState(null);
  const [notificationTarget, setNotificationTarget] = useState(null);
  const [imagePreview, setImagePreview] = useState(null);
  const [sendingNotification, setSendingNotification] = useState(false);
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    password: '',
    phone: '',
    city: '',
    address: '',
    pincode: '',
    state: '',
    country: 'India',
    gender: 'male',
    about: '',
    activated: true,
    image: null
  });
  const [notificationForm, setNotificationForm] = useState({
    title: '',
    body: ''
  });

  useEffect(() => {
    fetchUsers();
  }, []);

  const hasStoredNotifications = (responseData) =>
    Array.isArray(responseData?.results) &&
    responseData.results.length > 0 &&
    responseData.results.every((item) => item.notification_id);

  const storeNotificationRecords = async ({ title, body, userIds = [], broadcast = false }) => {
    try {
      await api.post('/admin/notifications/store', {
        title,
        body,
        user_ids: userIds,
        broadcast,
      });
      return true;
    } catch (error) {
      console.error('Error storing notification records:', error);
      return false;
    }
  };

  const fetchUsers = async () => {
    try {
      setLoading(true);
      const response = await api.get('/admin/users');
      setUsers(response.data.data.data || []);
    } catch (error) {
      console.error('Error fetching users:', error);
      toast.error('Failed to load users');
    } finally {
      setLoading(false);
    }
  };

  const handleDelete = async (id) => {
    if (window.confirm('Are you sure you want to delete this user?')) {
      try {
        await api.delete(`/admin/users/${id}`);
        toast.success('User deleted successfully');
        fetchUsers();
      } catch (error) {
        console.error('Error deleting user:', error);
        toast.error('Failed to delete user');
      }
    }
  };

  const handleImageChange = (e) => {
    const file = e.target.files[0];
    if (file) {
      setFormData({ ...formData, image: file });
      const reader = new FileReader();
      reader.onloadend = () => {
        setImagePreview(reader.result);
      };
      reader.readAsDataURL(file);
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    const data = new FormData();
    Object.keys(formData).forEach(key => {
      if (key === 'image' && formData[key] === null) return;
      if (key === 'activated') {
        data.append(key, formData[key] ? '1' : '0');
      } else {
        data.append(key, formData[key]);
      }
    });

    try {
      setLoading(true);
      if (editingUser) {
        // Since we are uploading files, we must use POST
        // Laravel handles this if we add _method=PUT
        data.append('_method', 'PUT');
        await api.post(`/admin/users/${editingUser.id}`, data, {
          headers: { 'Content-Type': 'multipart/form-data' }
        });
        toast.success('User updated successfully');
      } else {
        await api.post('/admin/users', data, {
          headers: { 'Content-Type': 'multipart/form-data' }
        });
        toast.success('User created successfully');
      }
      setIsModalOpen(false);
      resetForm();
      fetchUsers();
    } catch (error) {
      console.error('Error saving user:', error);
      const message = error.response?.data?.message || 'Failed to save user';
      toast.error(message);
    } finally {
      setLoading(false);
    }
  };

  const openEditModal = (user) => {
    setEditingUser(user);
    setFormData({
      name: user.name || '',
      email: user.email || '',
      password: '', // Don't pre-fill password
      phone: user.phone || '',
      city: user.city || '',
      address: user.address || '',
      pincode: user.pincode || '',
      state: user.state || '',
      country: user.country || 'India',
      gender: user.gender || 'male',
      about: user.about || '',
      activated: user.activated === undefined ? true : !!user.activated,
      image: null
    });
    setImagePreview(user.image ? getImageUrl(user.image) : null);
    setIsModalOpen(true);
  };

  const resetForm = () => {
    setEditingUser(null);
    setImagePreview(null);
    setFormData({
      name: '',
      email: '',
      password: '',
      phone: '',
      city: '',
      address: '',
      pincode: '',
      state: '',
      country: 'India',
      gender: 'male',
      about: '',
      activated: true,
      image: null
    });
  };

  const openNotificationModal = (user = null) => {
    setNotificationTarget(user);
    setNotificationForm({
      title: '',
      body: ''
    });
    setIsNotificationModalOpen(true);
  };

  const closeNotificationModal = () => {
    setNotificationTarget(null);
    setNotificationForm({
      title: '',
      body: ''
    });
    setIsNotificationModalOpen(false);
  };

  const handleNotificationSubmit = async (e) => {
    e.preventDefault();

    if (!notificationForm.title.trim() || !notificationForm.body.trim()) {
      toast.error('Please add both notification title and message.');
      return;
    }

    try {
      setSendingNotification(true);

      const endpoint = notificationTarget
        ? `/admin/notifications/users/${notificationTarget.id}`
        : '/admin/notifications/send-all';

      const response = await api.post(endpoint, {
        title: notificationForm.title.trim(),
        body: notificationForm.body.trim()
      });

      const storedInSendResponse = hasStoredNotifications(response.data);
      let fallbackStored = true;

      if (!storedInSendResponse) {
        fallbackStored = await storeNotificationRecords({
          title: notificationForm.title.trim(),
          body: notificationForm.body.trim(),
          userIds: notificationTarget ? [notificationTarget.id] : [],
          broadcast: !notificationTarget,
        });
      }

      toast.success(response.data.message || 'Notification sent successfully');
      if (!storedInSendResponse && !fallbackStored) {
        toast.error('Notification was sent, but storage fallback API is missing on the server.');
      }
      closeNotificationModal();
    } catch (error) {
      console.error('Error sending notification:', error);
      toast.error(error.response?.data?.message || 'Failed to send notification');
    } finally {
      setSendingNotification(false);
    }
  };

  const filteredUsers = users.filter(user => 
    user.name?.toLowerCase().includes(searchTerm.toLowerCase()) ||
    user.email?.toLowerCase().includes(searchTerm.toLowerCase()) ||
    user.city?.toLowerCase().includes(searchTerm.toLowerCase())
  );

  return (
    <div className={styles.container}>
      <header className={styles.header}>
        <div>
          <h1 className={styles.title}>User Management</h1>
          <p className={styles.subtitle}>View and manage registered devotees and donors</p>
        </div>
        <div className={styles.headerActions}>
          <button className={styles.notifyAllBtn} onClick={() => openNotificationModal()}>
            <Bell size={18} />
            <span>Notify All Users</span>
          </button>
          <button className="btn-primary" onClick={() => { resetForm(); setIsModalOpen(true); }}>
            <Plus size={20} />
            <span>Add New User</span>
          </button>
        </div>
      </header>

      <div className={`glass-card ${styles.controls}`}>
        <div className={styles.searchBox}>
          <Search size={20} className={styles.searchIcon} />
          <input 
            type="text" 
            placeholder="Search by name, email or city..." 
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
          />
        </div>
      </div>

      {loading && !users.length ? (
        <div className={styles.loader}>
          <Loader2 className="animate-spin" size={40} />
          <p>Loading users...</p>
        </div>
      ) : (
        <div className={styles.userGrid}>
          {filteredUsers.map((user) => (
            <div key={user.id} className={`glass-card ${styles.userCard}`}>
              <div className={styles.userHeader}>
                <div className={styles.avatar}>
                  {user.image ? (
                    <img src={getImageUrl(user.image)} alt={user.name} />
                  ) : (
                    <div className={styles.avatarPlaceholder}>
                      {user.name?.charAt(0).toUpperCase()}
                    </div>
                  )}
                </div>
                <div className={styles.userInfo}>
                  <h3>{user.name}</h3>
                  <div className={user.activated ? styles.statusActive : styles.statusInactive}>
                    {user.activated ? <UserCheck size={14} /> : <UserX size={14} />}
                    <span>{user.activated ? 'Active' : 'Inactive'}</span>
                  </div>
                </div>
                <div className={styles.userActions}>
                  <button className={styles.actionBtn} onClick={() => openNotificationModal(user)} title="Send notification">
                    <Bell size={14} />
                  </button>
                  <button className={styles.actionBtn} onClick={() => openEditModal(user)}>
                    <Edit size={14} />
                  </button>
                  <button className={`${styles.actionBtn} ${styles.delete}`} onClick={() => handleDelete(user.id)}>
                    <Trash2 size={14} />
                  </button>
                </div>
              </div>
              <div className={styles.userDetails}>
                <div className={styles.detailItem}>
                  <Mail size={16} />
                  <span>{user.email}</span>
                </div>
                {user.phone && (
                  <div className={styles.detailItem}>
                    <Phone size={16} />
                    <span>{user.phone}</span>
                  </div>
                )}
                <div className={styles.detailItem}>
                  <MapPin size={16} />
                  <span>{user.city || 'Location not specified'}</span>
                </div>
                <div className={styles.detailItem}>
                  <Calendar size={16} />
                  <span>Joined {new Date(user.created_at).toLocaleDateString()}</span>
                </div>
              </div>
            </div>
          ))}
        </div>
      )}

      {/* Add/Edit User Modal */}
      {isModalOpen && (
        <div className={styles.modalOverlay}>
          <div className={`glass-card ${styles.modal}`}>
            <div className={styles.modalHeader}>
              <h2>{editingUser ? 'Edit User' : 'Add New User'}</h2>
              <button onClick={() => setIsModalOpen(false)}><X size={24} /></button>
            </div>
            <form onSubmit={handleSubmit} className={styles.form}>
              <div className={styles.imageUploadSection}>
                <div className={styles.imagePreviewWrapper}>
                  {imagePreview ? (
                    <img src={imagePreview} alt="Preview" className={styles.previewImage} />
                  ) : (
                    <div className={styles.imagePlaceholder}>
                      <Users size={40} />
                    </div>
                  )}
                  <label htmlFor="user-image" className={styles.uploadBtn}>
                    <Camera size={18} />
                    <input 
                      type="file" 
                      id="user-image" 
                      accept="image/*" 
                      onChange={handleImageChange}
                      hidden 
                    />
                  </label>
                </div>
                <p className={styles.uploadText}>Upload Profile Picture</p>
              </div>

              <div className={styles.formGrid}>
                <div className={styles.formGroup}>
                  <label>Full Name</label>
                  <input 
                    type="text" 
                    required 
                    placeholder="John Doe"
                    value={formData.name}
                    onChange={(e) => setFormData({...formData, name: e.target.value})}
                  />
                </div>
                <div className={styles.formGroup}>
                  <label>Email Address</label>
                  <input 
                    type="email" 
                    required 
                    placeholder="john@example.com"
                    value={formData.email}
                    onChange={(e) => setFormData({...formData, email: e.target.value})}
                  />
                </div>
                <div className={styles.formGroup}>
                  <label>Phone Number</label>
                  <input 
                    type="text" 
                    placeholder="+91 9876543210"
                    value={formData.phone}
                    onChange={(e) => setFormData({...formData, phone: e.target.value})}
                  />
                </div>
                <div className={styles.formGroup}>
                  <label>Gender</label>
                  <select 
                    value={formData.gender}
                    onChange={(e) => setFormData({...formData, gender: e.target.value})}
                  >
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                  </select>
                </div>
                <div className={styles.formGroup}>
                  <label>City</label>
                  <input 
                    type="text" 
                    placeholder="City"
                    value={formData.city}
                    onChange={(e) => setFormData({...formData, city: e.target.value})}
                  />
                </div>
                <div className={styles.formGroup}>
                  <label>State</label>
                  <input 
                    type="text" 
                    placeholder="State"
                    value={formData.state}
                    onChange={(e) => setFormData({...formData, state: e.target.value})}
                  />
                </div>
                <div className={styles.formGroup}>
                  <label>Pincode</label>
                  <input 
                    type="text" 
                    placeholder="110001"
                    value={formData.pincode}
                    onChange={(e) => setFormData({...formData, pincode: e.target.value})}
                  />
                </div>
                <div className={styles.formGroup}>
                  <label>Country</label>
                  <input 
                    type="text" 
                    placeholder="India"
                    value={formData.country}
                    onChange={(e) => setFormData({...formData, country: e.target.value})}
                  />
                </div>
                <div className={`${styles.formGroup} ${styles.fullWidth}`}>
                  <label>Address</label>
                  <textarea 
                    placeholder="Full Address"
                    value={formData.address}
                    onChange={(e) => setFormData({...formData, address: e.target.value})}
                  />
                </div>
                <div className={`${styles.formGroup} ${styles.fullWidth}`}>
                  <label>About / Bio</label>
                  <textarea 
                    placeholder="Tell us about this devotee..."
                    value={formData.about}
                    onChange={(e) => setFormData({...formData, about: e.target.value})}
                  />
                </div>
                <div className={styles.formGroup}>
                  <label>{editingUser ? 'New Password (leave blank to keep current)' : 'Password'}</label>
                  <div className={styles.inputWithIcon}>
                    <Lock size={18} />
                    <input 
                      type="password" 
                      required={!editingUser}
                      placeholder="••••••••"
                      value={formData.password}
                      onChange={(e) => setFormData({...formData, password: e.target.value})}
                    />
                  </div>
                </div>
                <div className={styles.checkboxGroup}>
                  <input 
                    type="checkbox" 
                    id="activated"
                    checked={formData.activated}
                    onChange={(e) => setFormData({...formData, activated: e.target.checked})}
                  />
                  <label htmlFor="activated">Account Activated</label>
                </div>
              </div>
              <div className={styles.modalFooter}>
                <button type="button" className="btn-secondary" onClick={() => setIsModalOpen(false)}>Cancel</button>
                <button type="submit" className="btn-primary" disabled={loading}>
                  {loading ? 'Saving...' : (editingUser ? 'Update User' : 'Create User')}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {isNotificationModalOpen && (
        <div className={styles.modalOverlay}>
          <div className={`glass-card ${styles.notificationModal}`}>
            <div className={styles.modalHeader}>
              <div>
                <h2>{notificationTarget ? `Notify ${notificationTarget.name}` : 'Notify All Users'}</h2>
                <p className={styles.notificationSubtitle}>
                  {notificationTarget
                    ? `This push notification will be sent only to ${notificationTarget.email}.`
                    : 'This push notification will be sent to every user with a registered Firebase token.'}
                </p>
              </div>
              <button onClick={closeNotificationModal}>
                <X size={24} />
              </button>
            </div>

            <form onSubmit={handleNotificationSubmit} className={styles.form}>
              <div className={styles.formGroup}>
                <label>Notification Title</label>
                <input
                  type="text"
                  maxLength={255}
                  value={notificationForm.title}
                  onChange={(e) => setNotificationForm({ ...notificationForm, title: e.target.value })}
                  placeholder="Enter push notification title"
                />
              </div>

              <div className={styles.formGroup}>
                <label>Message</label>
                <textarea
                  maxLength={1000}
                  value={notificationForm.body}
                  onChange={(e) => setNotificationForm({ ...notificationForm, body: e.target.value })}
                  placeholder="Write the notification message users should receive"
                />
              </div>

              <div className={styles.modalFooter}>
                <button type="button" className="btn-secondary" onClick={closeNotificationModal}>
                  Cancel
                </button>
                <button type="submit" className="btn-primary" disabled={sendingNotification}>
                  <Send size={16} />
                  <span>{sendingNotification ? 'Sending...' : 'Send Notification'}</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
