'use client';

import { useState, useEffect } from 'react';
import api from '@/lib/axios';
import { 
  HelpCircle, 
  Search, 
  Mail, 
  Phone, 
  Calendar,
  Loader2,
  User
} from 'lucide-react';
import styles from './Support.module.css';

export default function SupportPage() {
  const [requests, setRequests] = useState([]);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');

  useEffect(() => {
    fetchSupportRequests();
  }, []);

  const fetchSupportRequests = async () => {
    try {
      setLoading(true);
      const response = await api.get('/user-support');
      // The API returns { status: true, data: [...] }
      setRequests(response.data.data || []);
    } catch (error) {
      console.error('Error fetching support requests:', error);
    } finally {
      setLoading(false);
    }
  };

  const filteredRequests = requests.filter(request => 
    request.name?.toLowerCase().includes(searchTerm.toLowerCase()) ||
    request.email?.toLowerCase().includes(searchTerm.toLowerCase()) ||
    request.phone_number?.includes(searchTerm)
  );

  return (
    <div className={styles.container}>
      <header className={styles.header}>
        <div>
          <h1 className={styles.title}>Support Requests</h1>
          <p className={styles.subtitle}>Manage and respond to user inquiries and support tickets</p>
        </div>
      </header>

      <div className={`glass-card ${styles.controls}`}>
        <div className={styles.searchBox}>
          <Search size={20} className={styles.searchIcon} />
          <input 
            type="text" 
            placeholder="Search by name, email or phone..." 
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
          />
        </div>
      </div>

      {loading ? (
        <div className={styles.loader}>
          <Loader2 className="animate-spin" size={40} />
          <p>Loading requests...</p>
        </div>
      ) : (
        <div className="glass-card">
          <div className={styles.tableWrapper}>
            <table className={styles.table}>
              <thead>
                <tr>
                  <th>User Details</th>
                  <th>Contact Information</th>
                  <th>Request Date</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                {filteredRequests.length > 0 ? (
                  filteredRequests.map((request) => (
                    <tr key={request.id}>
                      <td>
                        <div className={styles.supportInfo}>
                          <span className={styles.name}>{request.name}</span>
                          <span className={styles.email}>{request.email}</span>
                        </div>
                      </td>
                      <td>
                        <div className={styles.phone}>
                          <Phone size={14} />
                          <span>{request.phone_number}</span>
                        </div>
                      </td>
                      <td>
                        <div className={styles.date}>
                          <Calendar size={14} style={{ display: 'inline', marginRight: '4px' }} />
                          {new Date(request.created_at).toLocaleDateString()}
                        </div>
                      </td>
                      <td>
                        <span className="status-badge" style={{ 
                          background: 'rgba(59, 130, 246, 0.1)', 
                          color: '#3b82f6',
                          padding: '0.25rem 0.75rem',
                          borderRadius: '20px',
                          fontSize: '0.75rem',
                          fontWeight: '500'
                        }}>
                          New
                        </span>
                      </td>
                    </tr>
                  ))
                ) : (
                  <tr>
                    <td colSpan="4" className={styles.emptyState}>
                      No support requests found
                    </td>
                  </tr>
                )}
              </tbody>
            </table>
          </div>
        </div>
      )}
    </div>
  );
}
