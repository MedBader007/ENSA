import { useState, useEffect } from 'react';
import { getStatistics } from '../../lib/admin';

export function StatisticsPanel() {
  const [stats, setStats] = useState<any>(null);
  const [period, setPeriod] = useState<'day' | 'week' | 'month'>('day');
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    loadStatistics();
  }, [period]);

  async function loadStatistics() {
    try {
      setLoading(true);
      const data = await getStatistics(period);
      setStats(data);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to load statistics');
    } finally {
      setLoading(false);
    }
  }

  if (loading) return <div>Loading...</div>;
  if (error) return <div>Error: {error}</div>;
  if (!stats) return null;

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <h2 className="text-lg font-semibold">Statistics</h2>
        
        <select
          value={period}
          onChange={(e) => setPeriod(e.target.value as 'day' | 'week' | 'month')}
          className="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
        >
          <option value="day">Last 24 Hours</option>
          <option value="week">Last 7 Days</option>
          <option value="month">Last 30 Days</option>
        </select>
      </div>

      <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        {stats.map((stat: any) => (
          <div
            key={stat.stat_key}
            className="bg-white overflow-hidden shadow rounded-lg"
          >
            <div className="px-4 py-5 sm:p-6">
              <dt className="text-sm font-medium text-gray-500 truncate">
                {stat.stat_key.split('_').map((word: string) => 
                  word.charAt(0).toUpperCase() + word.slice(1)
                ).join(' ')}
              </dt>
              
              <dd className="mt-1 text-3xl font-semibold text-gray-900">
                {typeof stat.stat_value === 'object' 
                  ? JSON.stringify(stat.stat_value, null, 2)
                  : stat.stat_value}
              </dd>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}