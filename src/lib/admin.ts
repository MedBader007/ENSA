import { createClient } from '@supabase/supabase-js';
import type { Database } from '../types/supabase';

const supabaseUrl = import.meta.env.VITE_SUPABASE_URL;
const supabaseKey = import.meta.env.VITE_SUPABASE_ANON_KEY;

const supabase = createClient<Database>(supabaseUrl, supabaseKey);

export async function getAdminSettings(category?: string) {
  const query = supabase
    .from('admin_settings')
    .select('*');
    
  if (category) {
    query.eq('category', category);
  }
  
  const { data, error } = await query;
  
  if (error) throw error;
  return data;
}

export async function updateAdminSetting(key: string, value: any) {
  const { data, error } = await supabase
    .from('admin_settings')
    .update({ 
      setting_value: value,
      updated_at: new Date()
    })
    .eq('setting_key', key)
    .select()
    .single();
    
  if (error) throw error;
  return data;
}

export async function getStatistics(period: 'day' | 'week' | 'month' = 'day') {
  const now = new Date();
  let startDate = new Date();
  
  switch (period) {
    case 'week':
      startDate.setDate(now.getDate() - 7);
      break;
    case 'month':
      startDate.setMonth(now.getMonth() - 1);
      break;
    default:
      startDate.setDate(now.getDate() - 1);
  }
  
  const { data, error } = await supabase
    .from('admin_statistics')
    .select('*')
    .gte('period_start', startDate.toISOString())
    .lte('period_end', now.toISOString())
    .order('period_start', { ascending: false });
    
  if (error) throw error;
  return data;
}

export async function updateStatistics(key: string, value: any, period: { start: Date; end: Date }) {
  const { data, error } = await supabase
    .from('admin_statistics')
    .upsert({
      stat_key: key,
      stat_value: value,
      period_start: period.start,
      period_end: period.end,
      updated_at: new Date()
    })
    .select()
    .single();
    
  if (error) throw error;
  return data;
}