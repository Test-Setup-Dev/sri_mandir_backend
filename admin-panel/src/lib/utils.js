/**
 * Get the full URL for an image path from the API storage
 * @param {string} path - The relative path from the database
 * @returns {string|null} - The full URL or null if no path provided
 */
export const getImageUrl = (path) => {
  if (!path) return null;
  if (path.startsWith('http')) return path;
  
  const baseUrl = process.env.NEXT_PUBLIC_API_URL || 'https://rapidload.in/shri_mindir/api/';
  const rootUrl = baseUrl.replace(/\/api\/?$/, '');
  
  return `${rootUrl}/public/storage/${path.startsWith('/') ? path.slice(1) : path}`;
};

export const getMediaUrl = (path) => getImageUrl(path);
