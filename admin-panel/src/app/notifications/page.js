'use client';

import { useEffect, useState } from 'react';
import api from '@/lib/axios';
import toast from 'react-hot-toast';
import {
  Bell,
  Send,
  Search,
  Mail,
  Phone,
  Loader2,
  Users,
  Megaphone,
  Save,
  Pencil,
  Trash2,
  CopyPlus,
  CheckCircle2,
} from 'lucide-react';
import { getImageUrl } from '@/lib/utils';
import styles from './Notifications.module.css';

const emptyForm = {
  title: '',
  body: '',
};

const emptyTemplateForm = {
  name: '',
  title: '',
  body: '',
};

export default function NotificationsPage() {
  const [users, setUsers] = useState([]);
  const [templates, setTemplates] = useState([]);
  const [loading, setLoading] = useState(true);
  const [templatesLoading, setTemplatesLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  const [sendMode, setSendMode] = useState('broadcast');
  const [selectedUserIds, setSelectedUserIds] = useState([]);
  const [broadcastForm, setBroadcastForm] = useState(emptyForm);
  const [individualForm, setIndividualForm] = useState(emptyForm);
  const [templateForm, setTemplateForm] = useState(emptyTemplateForm);
  const [editingTemplateId, setEditingTemplateId] = useState(null);
  const [savingTemplate, setSavingTemplate] = useState(false);
  const [sendingAll, setSendingAll] = useState(false);
  const [sendingUserId, setSendingUserId] = useState(null);
  const [deletingTemplateId, setDeletingTemplateId] = useState(null);
  const [activeTemplateId, setActiveTemplateId] = useState(null);

  useEffect(() => {
    fetchUsers();
    fetchTemplates();
  }, []);

  const fetchUsers = async () => {
    try {
      setLoading(true);
      const response = await api.get('/admin/users');
      const fetchedUsers = response.data.data.data || [];
      setUsers(fetchedUsers);

      if (!selectedUserIds.length && fetchedUsers.length > 0) {
        setSelectedUserIds([fetchedUsers[0].id]);
      }
    } catch (error) {
      console.error('Error fetching users:', error);
      toast.error('Failed to load users');
    } finally {
      setLoading(false);
    }
  };

  const fetchTemplates = async () => {
    try {
      setTemplatesLoading(true);
      const response = await api.get('/admin/notification-templates');
      setTemplates(response.data.data || []);
    } catch (error) {
      console.error('Error fetching templates:', error);
      toast.error('Failed to load notification templates');
    } finally {
      setTemplatesLoading(false);
    }
  };

  const handleTemplateSubmit = async (e) => {
    e.preventDefault();

    if (!templateForm.name.trim() || !templateForm.title.trim() || !templateForm.body.trim()) {
      toast.error('Please complete the template name, title, and message.');
      return;
    }

    try {
      setSavingTemplate(true);
      if (editingTemplateId) {
        await api.put(`/admin/notification-templates/${editingTemplateId}`, {
          name: templateForm.name.trim(),
          title: templateForm.title.trim(),
          body: templateForm.body.trim(),
        });
        toast.success('Template updated successfully');
      } else {
        await api.post('/admin/notification-templates', {
          name: templateForm.name.trim(),
          title: templateForm.title.trim(),
          body: templateForm.body.trim(),
        });
        toast.success('Template created successfully');
      }

      setTemplateForm(emptyTemplateForm);
      setEditingTemplateId(null);
      fetchTemplates();
    } catch (error) {
      console.error('Error saving template:', error);
      toast.error(error.response?.data?.message || 'Failed to save template');
    } finally {
      setSavingTemplate(false);
    }
  };

  const handleEditTemplate = (template) => {
    setEditingTemplateId(template.id);
    setTemplateForm({
      name: template.name || '',
      title: template.title || '',
      body: template.body || '',
    });
  };

  const handleDeleteTemplate = async (template) => {
    if (!window.confirm(`Delete template "${template.name}"?`)) {
      return;
    }

    try {
      setDeletingTemplateId(template.id);
      await api.delete(`/admin/notification-templates/${template.id}`);
      toast.success('Template deleted successfully');

      if (editingTemplateId === template.id) {
        setEditingTemplateId(null);
        setTemplateForm(emptyTemplateForm);
      }

      if (activeTemplateId === template.id) {
        setActiveTemplateId(null);
      }

      fetchTemplates();
    } catch (error) {
      console.error('Error deleting template:', error);
      toast.error(error.response?.data?.message || 'Failed to delete template');
    } finally {
      setDeletingTemplateId(null);
    }
  };

  const cancelTemplateEditing = () => {
    setEditingTemplateId(null);
    setTemplateForm(emptyTemplateForm);
  };

  const applyTemplate = (template) => {
    const nextForm = {
      title: template.title || '',
      body: template.body || '',
    };

    setActiveTemplateId(template.id);

    if (sendMode === 'broadcast') {
      setBroadcastForm(nextForm);
      toast.success(`Applied "${template.name}" to broadcast composer`);
      return;
    }

    setIndividualForm(nextForm);
    toast.success(`Applied "${template.name}" to individual composer`);
  };

  const handleBroadcastSend = async (e) => {
    e.preventDefault();

    if (!broadcastForm.title.trim() || !broadcastForm.body.trim()) {
      toast.error('Please enter both title and message for the broadcast.');
      return;
    }

    try {
      setSendingAll(true);
      const response = await api.post('/admin/notifications/send-all', {
        title: broadcastForm.title.trim(),
        body: broadcastForm.body.trim(),
      });
      toast.success(response.data.message || 'Broadcast sent successfully');
      setBroadcastForm(emptyForm);
      setActiveTemplateId(null);
    } catch (error) {
      console.error('Error broadcasting notification:', error);
      toast.error(error.response?.data?.message || 'Failed to send broadcast');
    } finally {
      setSendingAll(false);
    }
  };

  const handleIndividualSend = async (e) => {
    e.preventDefault();

    if (!selectedUserIds.length) {
      toast.error('Please select at least one user first.');
      return;
    }

    if (!individualForm.title.trim() || !individualForm.body.trim()) {
      toast.error('Please enter both title and message for the selected users.');
      return;
    }

    try {
      setSendingUserId('bulk-selection');

      const results = await Promise.allSettled(
        selectedUserIds.map((userId) =>
          api.post(`/admin/notifications/users/${userId}`, {
            title: individualForm.title.trim(),
            body: individualForm.body.trim(),
          })
        )
      );

      const successCount = results.filter((result) => result.status === 'fulfilled').length;
      const failCount = results.length - successCount;

      if (successCount > 0 && failCount === 0) {
        toast.success(`Notification sent to ${successCount} user${successCount > 1 ? 's' : ''}`);
      } else if (successCount > 0) {
        toast.success(`Sent to ${successCount} users, ${failCount} failed`);
      } else {
        toast.error('Failed to send notification to selected users');
      }

      setIndividualForm(emptyForm);
      setActiveTemplateId(null);
    } catch (error) {
      console.error('Error sending user notification:', error);
      toast.error(error.response?.data?.message || 'Failed to send notification');
    } finally {
      setSendingUserId(null);
    }
  };

  const toggleUserSelection = (userId) => {
    setSelectedUserIds((current) =>
      current.includes(userId)
        ? current.filter((id) => id !== userId)
        : [...current, userId]
    );
  };

  const selectAllFilteredUsers = () => {
    setSelectedUserIds((current) => {
      const idsToAdd = filteredUsers
        .map((user) => user.id)
        .filter((id) => !current.includes(id));

      return [...current, ...idsToAdd];
    });
  };

  const clearUserSelection = () => {
    setSelectedUserIds([]);
  };

  const filteredUsers = users.filter((user) =>
    user.name?.toLowerCase().includes(searchTerm.toLowerCase()) ||
    user.email?.toLowerCase().includes(searchTerm.toLowerCase()) ||
    user.phone?.includes(searchTerm)
  );

  const selectedUsers = users.filter((user) => selectedUserIds.includes(user.id));
  const currentForm = sendMode === 'broadcast' ? broadcastForm : individualForm;
  const isSending = sendMode === 'broadcast' ? sendingAll : sendingUserId === 'bulk-selection';

  return (
    <div className={styles.container}>
      <header className={styles.header}>
        <div>
          <h1 className={styles.title}>Notifications</h1>
          <p className={styles.subtitle}>
            Create templates, choose the audience, and send Firebase notifications from one clear workflow.
          </p>
        </div>
      </header>

      <section className={`glass-card ${styles.templatesPanel}`}>
        <div className={styles.panelHeader}>
          <div className={styles.panelTitleWrap}>
            <div className={styles.panelIcon}>
              <CopyPlus size={20} />
            </div>
            <div>
              <h2 className={styles.panelTitle}>Predefined Templates</h2>
              <p className={styles.panelText}>
                Save your common notification messages here, then apply them into the composer with one click.
              </p>
            </div>
          </div>
        </div>

        <div className={styles.templatesLayout}>
          <form onSubmit={handleTemplateSubmit} className={styles.form}>
            <div className={styles.formGroup}>
              <label>Template Name</label>
              <input
                type="text"
                maxLength={255}
                value={templateForm.name}
                onChange={(e) => setTemplateForm({ ...templateForm, name: e.target.value })}
                placeholder="Morning aarti reminder"
              />
            </div>

            <div className={styles.formGroup}>
              <label>Notification Title</label>
              <input
                type="text"
                maxLength={255}
                value={templateForm.title}
                onChange={(e) => setTemplateForm({ ...templateForm, title: e.target.value })}
                placeholder="Aarti starts in 30 minutes"
              />
            </div>

            <div className={styles.formGroup}>
              <label>Message</label>
              <textarea
                maxLength={1000}
                value={templateForm.body}
                onChange={(e) => setTemplateForm({ ...templateForm, body: e.target.value })}
                placeholder="Join us live for today's aarti and blessings."
              />
            </div>

            <div className={styles.formActions}>
              {editingTemplateId && (
                <button type="button" className="btn-secondary" onClick={cancelTemplateEditing}>
                  Cancel Edit
                </button>
              )}
              <button type="submit" className="btn-primary" disabled={savingTemplate}>
                {savingTemplate ? <Loader2 className="animate-spin" size={16} /> : <Save size={16} />}
                <span>{savingTemplate ? 'Saving...' : editingTemplateId ? 'Update Template' : 'Create Template'}</span>
              </button>
            </div>
          </form>

          <div className={styles.templateList}>
            {templatesLoading ? (
              <div className={styles.loader}>
                <Loader2 className="animate-spin" size={32} />
                <p>Loading templates...</p>
              </div>
            ) : templates.length ? (
              templates.map((template) => (
                <div
                  key={template.id}
                  className={`${styles.templateCard} ${activeTemplateId === template.id ? styles.templateCardActive : ''}`}
                  onClick={() => applyTemplate(template)}
                  onKeyDown={(e) => {
                    if (e.key === 'Enter' || e.key === ' ') {
                      e.preventDefault();
                      applyTemplate(template);
                    }
                  }}
                  role="button"
                  tabIndex={0}
                >
                  <div className={styles.templateCardHeader}>
                    <div className={styles.templateMeta}>
                      <h3>{template.name}</h3>
                      <p className={styles.templateCardTitle}>{template.title}</p>
                    </div>
                    <div className={styles.templateActions} onClick={(e) => e.stopPropagation()}>
                      <button type="button" className={styles.iconBtn} onClick={() => applyTemplate(template)} title="Use template">
                        <Send size={15} />
                      </button>
                      <button type="button" className={styles.iconBtn} onClick={() => handleEditTemplate(template)} title="Edit template">
                        <Pencil size={15} />
                      </button>
                      <button
                        type="button"
                        className={`${styles.iconBtn} ${styles.dangerBtn}`}
                        onClick={() => handleDeleteTemplate(template)}
                        disabled={deletingTemplateId === template.id}
                        title="Delete template"
                      >
                        {deletingTemplateId === template.id ? <Loader2 className="animate-spin" size={15} /> : <Trash2 size={15} />}
                      </button>
                    </div>
                  </div>
                  <p className={styles.templateBody}>{template.body}</p>
                  {activeTemplateId === template.id && (
                    <div className={styles.templateAppliedBadge}>
                      <CheckCircle2 size={14} />
                      <span>Active in composer</span>
                    </div>
                  )}
                </div>
              ))
            ) : (
              <div className={styles.emptyState}>
                <CopyPlus size={28} />
                <p>No predefined templates yet.</p>
              </div>
            )}
          </div>
        </div>
      </section>

      <section className={`glass-card ${styles.workspacePanel}`}>
        <div className={styles.workspaceHeader}>
          <div>
            <h2 className={styles.panelTitle}>Send Workspace</h2>
            <p className={styles.panelText}>
              Pick who should receive the notification, then write or apply a template in one focused composer.
            </p>
          </div>

          <div className={styles.modeTabs}>
            <button
              type="button"
              className={`${styles.modeTab} ${sendMode === 'broadcast' ? styles.modeTabActive : ''}`}
              onClick={() => setSendMode('broadcast')}
            >
              <Megaphone size={16} />
              <span>Broadcast</span>
            </button>
            <button
              type="button"
              className={`${styles.modeTab} ${sendMode === 'individual' ? styles.modeTabActive : ''}`}
              onClick={() => setSendMode('individual')}
            >
              <Bell size={16} />
              <span>Individual</span>
            </button>
          </div>
        </div>

        <div className={styles.workspaceLayout}>
          <aside className={styles.audiencePanel}>
            <div className={styles.audienceHeader}>
              <h3>{sendMode === 'broadcast' ? 'Audience' : 'Select User'}</h3>
              <p>
                {sendMode === 'broadcast'
                  ? 'This mode sends one message to every user with a valid Firebase token.'
                  : 'Choose one or more users, then send the same message to that selected group.'}
              </p>
            </div>

            {sendMode === 'broadcast' ? (
              <div className={styles.broadcastAudienceCard}>
                <div className={styles.broadcastAudienceIcon}>
                  <Users size={18} />
                </div>
                <div>
                  <h4>All Registered Users</h4>
                  <p>Everyone with a saved FCM token will receive this notification.</p>
                </div>
              </div>
            ) : (
              <>
                <div className={styles.searchBox}>
                  <Search size={18} className={styles.searchIcon} />
                  <input
                    type="text"
                    placeholder="Search by name, email or phone..."
                    value={searchTerm}
                    onChange={(e) => setSearchTerm(e.target.value)}
                  />
                </div>

                <div className={styles.selectionToolbar}>
                  <span>{selectedUserIds.length} selected</span>
                  <div className={styles.selectionActions}>
                    <button type="button" className={styles.toolbarBtn} onClick={selectAllFilteredUsers}>
                      Select Search Results
                    </button>
                    <button type="button" className={styles.toolbarBtn} onClick={clearUserSelection}>
                      Clear
                    </button>
                  </div>
                </div>

                {loading ? (
                  <div className={styles.loader}>
                    <Loader2 className="animate-spin" size={28} />
                    <p>Loading users...</p>
                  </div>
                ) : (
                  <div className={styles.userList}>
                    {filteredUsers.map((user) => (
                      <button
                        key={user.id}
                        type="button"
                        className={`${styles.userListItem} ${selectedUserIds.includes(user.id) ? styles.userListItemActive : ''}`}
                        onClick={() => toggleUserSelection(user.id)}
                      >
                        <div className={styles.userCheckbox}>
                          {selectedUserIds.includes(user.id) ? <CheckCircle2 size={18} /> : <span />}
                        </div>
                        <div className={styles.avatar}>
                          {user.image ? (
                            <img src={getImageUrl(user.image)} alt={user.name} />
                          ) : (
                            <div className={styles.avatarPlaceholder}>
                              {user.name?.charAt(0)?.toUpperCase() || <Users size={18} />}
                            </div>
                          )}
                        </div>
                        <div className={styles.userListMeta}>
                          <strong>{user.name}</strong>
                          <span>{user.email}</span>
                          {user.phone && <span>{user.phone}</span>}
                        </div>
                      </button>
                    ))}

                    {!filteredUsers.length && (
                      <div className={styles.emptyState}>
                        <Bell size={26} />
                        <p>No users matched your search.</p>
                      </div>
                    )}
                  </div>
                )}
              </>
            )}
          </aside>

          <div className={styles.composerPanel}>
            <div className={styles.composerHeader}>
              <div className={styles.composerAudience}>
                <div className={styles.composerIcon}>
                  {sendMode === 'broadcast' ? <Megaphone size={18} /> : <Bell size={18} />}
                </div>
                <div>
                  <h3>
                    {sendMode === 'broadcast'
                      ? 'Compose Broadcast Notification'
                      : selectedUsers.length
                        ? `Compose For ${selectedUsers.length} Selected User${selectedUsers.length > 1 ? 's' : ''}`
                        : 'Select Users'}
                  </h3>
                  <p>
                    {sendMode === 'broadcast'
                      ? 'One message will go to everyone.'
                      : selectedUsers.length
                        ? selectedUsers.slice(0, 2).map((user) => user.name).join(', ') + (selectedUsers.length > 2 ? ` +${selectedUsers.length - 2} more` : '')
                        : 'Choose one or more users from the list to start composing.'}
                  </p>
                </div>
              </div>

              {templates.length > 0 && (
                <div className={styles.quickTemplateBar}>
                  <span>Quick apply:</span>
                  <div className={styles.quickTemplateChips}>
                    {templates.slice(0, 4).map((template) => (
                      <button
                        key={template.id}
                        type="button"
                        className={`${styles.quickTemplateChip} ${activeTemplateId === template.id ? styles.quickTemplateChipActive : ''}`}
                        onClick={() => applyTemplate(template)}
                      >
                        {template.name}
                      </button>
                    ))}
                  </div>
                </div>
              )}
            </div>

            <form
              onSubmit={sendMode === 'broadcast' ? handleBroadcastSend : handleIndividualSend}
              className={styles.form}
            >
              <div className={styles.formGroup}>
                <label>Title</label>
                <input
                  type="text"
                  maxLength={255}
                  value={currentForm.title}
                  onChange={(e) =>
                    sendMode === 'broadcast'
                      ? setBroadcastForm({ ...broadcastForm, title: e.target.value })
                      : setIndividualForm({ ...individualForm, title: e.target.value })
                  }
                  placeholder={sendMode === 'broadcast' ? 'Temple event update' : 'Personal message title'}
                  disabled={sendMode === 'individual' && !selectedUserIds.length}
                />
              </div>

              <div className={styles.formGroup}>
                <label>Message</label>
                <textarea
                  maxLength={1000}
                  value={currentForm.body}
                  onChange={(e) =>
                    sendMode === 'broadcast'
                      ? setBroadcastForm({ ...broadcastForm, body: e.target.value })
                      : setIndividualForm({ ...individualForm, body: e.target.value })
                  }
                  placeholder={
                    sendMode === 'broadcast'
                      ? 'Write the notification all users should receive'
                      : 'Write the message for the selected users'
                  }
                  disabled={sendMode === 'individual' && !selectedUserIds.length}
                />
              </div>

              <div className={styles.composerFooter}>
                <div className={styles.helperText}>
                  {sendMode === 'broadcast'
                    ? 'Best for announcements, schedules, new uploads, and reminder campaigns.'
                    : 'Best for support follow-ups, important reminders, or direct outreach.'}
                </div>

                <button
                  type="submit"
                  className="btn-primary"
                  disabled={isSending || (sendMode === 'individual' && !selectedUserIds.length)}
                >
                  {isSending ? <Loader2 className="animate-spin" size={16} /> : <Send size={16} />}
                  <span>
                    {isSending
                      ? 'Sending...'
                      : sendMode === 'broadcast'
                        ? 'Send To All Users'
                        : `Send To ${selectedUserIds.length || ''} Selected User${selectedUserIds.length === 1 ? '' : 's'}`}
                  </span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </section>
    </div>
  );
}
