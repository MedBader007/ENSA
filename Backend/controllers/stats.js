import { createClient } from '@supabase/supabase-js';

const supabase = createClient(
  process.env.VITE_SUPABASE_URL,
  process.env.VITE_SUPABASE_ANON_KEY
);

// Get global statistics
export async function getGlobalStats() {
  try {
    // Check cache first
    const { data: cachedStats } = await supabase
      .from('statistics_cache')
      .select('cache_data')
      .eq('cache_key', 'global_stats')
      .single();

    if (cachedStats && new Date(cachedStats.expires_at) > new Date()) {
      return cachedStats.cache_data;
    }

    // Calculate fresh statistics
    const stats = {
      projects: await getProjectStats(),
      users: await getUserStats(),
      filieres: await getFiliereStats()
    };

    // Cache the results
    await supabase
      .from('statistics_cache')
      .upsert({
        cache_key: 'global_stats',
        cache_data: stats,
        expires_at: new Date(Date.now() + 30 * 60 * 1000) // 30 minutes
      });

    return stats;
  } catch (error) {
    console.error('Error fetching global stats:', error);
    throw error;
  }
}

async function getProjectStats() {
  const { data: projects } = await supabase
    .from('projects')
    .select('status');

  return {
    total: projects.length,
    pending: projects.filter(p => p.status === 'pending').length,
    validated: projects.filter(p => p.status === 'validated').length,
    rejected: projects.filter(p => p.status === 'rejected').length
  };
}

async function getUserStats() {
  const { data: users } = await supabase
    .from('users')
    .select('role');

  return {
    total: users.length,
    students: users.filter(u => u.role === 'etudiant').length,
    teachers: users.filter(u => u.role === 'enseignant').length,
    admins: users.filter(u => u.role === 'admin').length
  };
}

async function getFiliereStats() {
  const { data: students } = await supabase
    .from('students')
    .select('filiere');

  const stats = {};
  students.forEach(student => {
    stats[student.filiere] = (stats[student.filiere] || 0) + 1;
  });

  return stats;
}