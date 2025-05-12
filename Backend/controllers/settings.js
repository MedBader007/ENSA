import { createClient } from '@supabase/supabase-js';

const supabase = createClient(
  process.env.VITE_SUPABASE_URL,
  process.env.VITE_SUPABASE_ANON_KEY
);

// Get all settings
export async function getSettings() {
  try {
    const [{ data: generalSettings }, { data: securitySettings }] = await Promise.all([
      supabase.from('settings').select('*').single(),
      supabase.from('security_settings').select('*').single()
    ]);

    return {
      general: generalSettings,
      security: securitySettings
    };
  } catch (error) {
    console.error('Error fetching settings:', error);
    throw error;
  }
}

// Update general settings
export async function updateGeneralSettings(settings) {
  try {
    const { data, error } = await supabase
      .from('settings')
      .update({
        school_name: settings.school_name,
        school_email: settings.school_email,
        school_phone: settings.school_phone,
        school_website: settings.school_website,
        year_start: settings.year_start,
        year_end: settings.year_end,
        updated_at: new Date()
      })
      .eq('id', 1)
      .single();

    if (error) throw error;
    return data;
  } catch (error) {
    console.error('Error updating general settings:', error);
    throw error;
  }
}

// Update security settings
export async function updateSecuritySettings(settings) {
  try {
    const { data, error } = await supabase
      .from('security_settings')
      .update({
        two_factor_required: settings.two_factor_required,
        login_attempts_limit: settings.login_attempts_limit,
        session_timeout: settings.session_timeout,
        default_teacher_access: settings.default_teacher_access,
        updated_at: new Date()
      })
      .eq('id', 1)
      .single();

    if (error) throw error;
    return data;
  } catch (error) {
    console.error('Error updating security settings:', error);
    throw error;
  }
}