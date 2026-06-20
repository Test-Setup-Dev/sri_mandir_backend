'use client';

import { useState } from 'react';
import { useRouter } from 'next/navigation';
import { Lock, Mail, Loader2 } from 'lucide-react';
import { authService } from '@/lib/auth';
import styles from './Login.module.css';

export default function LoginPage() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const router = useRouter();

  const handleLogin = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError('');

    try {
      await authService.login(email, password);
      router.push('/');
    } catch (err) {
      setError(err.message || 'Invalid credentials');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className={styles.container}>
      <div className={styles.glassCircle1}></div>
      <div className={styles.glassCircle2}></div>
      
      <div className={`${styles.loginCard} glass`}>
        <div className={styles.header}>
          <div className={styles.logo}>SL</div>
          <h1>Admin Portal</h1>
          <p>Login to manage Sanatan Lok</p>
        </div>

        {error && <div className={styles.error}>{error}</div>}

        <form className={styles.form} onSubmit={handleLogin}>
          <div className={styles.inputGroup}>
            <Mail size={20} className={styles.icon} />
            <input 
              type="email" 
              placeholder="Email address" 
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              required 
            />
          </div>

          <div className={styles.inputGroup}>
            <Lock size={20} className={styles.icon} />
            <input 
              type="password" 
              placeholder="Password" 
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              required 
            />
          </div>

          <button type="submit" className={`${styles.submitBtn} bg-gradient-primary`} disabled={loading}>
            {loading ? <Loader2 className={styles.spin} /> : 'Sign In'}
          </button>
        </form>

        <div className={styles.footer}>
          <p>Protected by Sanatan Lok Security</p>
        </div>
      </div>
    </div>
  );
}
