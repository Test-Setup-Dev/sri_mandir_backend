'use client';

import { useState, useEffect } from 'react';
import api from '@/lib/axios';
import { 
  HeartHandshake, 
  Search, 
  Filter, 
  Download, 
  Loader2,
  TrendingUp,
  CreditCard,
  User,
  Calendar
} from 'lucide-react';
import styles from './Donations.module.css';

export default function DonationsPage() {
  const [donations, setDonations] = useState([]);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  const [statusFilter, setStatusFilter] = useState('all');

  useEffect(() => {
    fetchDonations();
  }, [statusFilter]);

  const fetchDonations = async () => {
    try {
      setLoading(true);
      const statusParam = statusFilter !== 'all' ? `?status=${statusFilter}` : '';
      const response = await api.get(`/admin/donations${statusParam}`);
      setDonations(response.data.data.data || []);
    } catch (error) {
      console.error('Error fetching donations:', error);
    } finally {
      setLoading(false);
    }
  };

  const filteredDonations = donations.filter(donation => 
    donation.user?.name?.toLowerCase().includes(searchTerm.toLowerCase()) ||
    donation.user?.email?.toLowerCase().includes(searchTerm.toLowerCase()) ||
    donation.order_id?.toLowerCase().includes(searchTerm.toLowerCase())
  );

  const totalAmount = donations
    .filter(d => d.status === 'success')
    .reduce((sum, d) => sum + parseFloat(d.amount), 0);

  return (
    <div className={styles.container}>
      <header className={styles.header}>
        <div>
          <h1 className={styles.title}>Donation History</h1>
          <p className={styles.subtitle}>Track all contributions to the Sanatan Lok mission</p>
        </div>
        <div className={`glass-card ${styles.totalCard}`}>
          <div className={styles.totalIcon}><TrendingUp size={24} /></div>
          <div>
            <p className={styles.totalLabel}>Total Contributions</p>
            <h2 className={styles.totalValue}>₹{totalAmount.toLocaleString()}</h2>
          </div>
        </div>
      </header>

      <div className={`glass-card ${styles.controls}`}>
        <div className={styles.searchBox}>
          <Search size={20} className={styles.searchIcon} />
          <input 
            type="text" 
            placeholder="Search by donor name, email or order ID..." 
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
          />
        </div>
        <div className={styles.filters}>
          <div className={styles.filterItem}>
            <Filter size={18} />
            <select value={statusFilter} onChange={(e) => setStatusFilter(e.target.value)}>
              <option value="all">All Status</option>
              <option value="success">Success</option>
              <option value="pending">Pending</option>
              <option value="failed">Failed</option>
            </select>
          </div>
          <button className={styles.exportBtn}>
            <Download size={18} />
            <span>Export CSV</span>
          </button>
        </div>
      </div>

      {loading ? (
        <div className={styles.loader}>
          <Loader2 className="animate-spin" size={40} />
          <p>Loading donation records...</p>
        </div>
      ) : (
        <div className={`glass-card ${styles.tableWrapper}`}>
          <table className={styles.table}>
            <thead>
              <tr>
                <th>Donor</th>
                <th>Order ID / Payment ID</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              {filteredDonations.map((donation) => (
                <tr key={donation.id}>
                  <td>
                    <div className={styles.donorInfo}>
                      <div className={styles.miniAvatar}>
                        <User size={14} />
                      </div>
                      <div>
                        <div className={styles.donorName}>{donation.user?.name || 'Anonymous'}</div>
                        <div className={styles.donorEmail}>{donation.user?.email || 'No email'}</div>
                      </div>
                    </div>
                  </td>
                  <td>
                    <div className={styles.orderId}>{donation.order_id}</div>
                    <div className={styles.paymentId}>{donation.payment_id || 'N/A'}</div>
                  </td>
                  <td>
                    <div className={styles.amount}>₹{donation.amount}</div>
                  </td>
                  <td>
                    <span className={`${styles.statusBadge} ${styles[donation.status]}`}>
                      {donation.status}
                    </span>
                  </td>
                  <td>
                    <div className={styles.date}>
                      <Calendar size={14} />
                      {new Date(donation.created_at).toLocaleDateString()}
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
          {filteredDonations.length === 0 && (
            <div className={styles.emptyState}>
              <p>No donation records found matching your criteria.</p>
            </div>
          )}
        </div>
      )}
    </div>
  );
}
