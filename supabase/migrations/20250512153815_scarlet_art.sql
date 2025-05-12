/*
  # Admin Dashboard Schema

  1. New Tables
    - `admin_settings`
      - General application settings
      - Security configurations
      - Customization options
    
    - `admin_statistics`
      - Cached statistics data
      - Performance metrics
      - Usage analytics

  2. Security
    - Enable RLS on all tables
    - Admin-only access policies
    - Data validation constraints

  3. Changes
    - Add indexes for performance
    - Add default values
*/

-- Admin settings table
CREATE TABLE admin_settings (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  setting_key TEXT NOT NULL UNIQUE,
  setting_value JSONB NOT NULL,
  category TEXT NOT NULL,
  description TEXT,
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

-- Admin statistics table
CREATE TABLE admin_statistics (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  stat_key TEXT NOT NULL,
  stat_value JSONB NOT NULL,
  period_start TIMESTAMPTZ NOT NULL,
  period_end TIMESTAMPTZ NOT NULL,
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now(),
  CONSTRAINT valid_period CHECK (period_end > period_start)
);

-- Enable RLS
ALTER TABLE admin_settings ENABLE ROW LEVEL SECURITY;
ALTER TABLE admin_statistics ENABLE ROW LEVEL SECURITY;

-- RLS Policies
CREATE POLICY "Only admins can read settings"
  ON admin_settings
  FOR SELECT
  TO authenticated
  USING (auth.jwt() ->> 'role' = 'admin');

CREATE POLICY "Only admins can modify settings"
  ON admin_settings
  FOR ALL
  TO authenticated
  USING (auth.jwt() ->> 'role' = 'admin');

CREATE POLICY "Only admins can read statistics"
  ON admin_statistics
  FOR SELECT
  TO authenticated
  USING (auth.jwt() ->> 'role' = 'admin');

CREATE POLICY "Only admins can modify statistics"
  ON admin_statistics
  FOR ALL
  TO authenticated
  USING (auth.jwt() ->> 'role' = 'admin');

-- Indexes
CREATE INDEX idx_admin_settings_key ON admin_settings(setting_key);
CREATE INDEX idx_admin_settings_category ON admin_settings(category);
CREATE INDEX idx_admin_statistics_key ON admin_statistics(stat_key);
CREATE INDEX idx_admin_statistics_period ON admin_statistics(period_start, period_end);

-- Insert default settings
INSERT INTO admin_settings 
  (setting_key, setting_value, category, description)
VALUES
  (
    'general_settings',
    '{
      "school_name": "ENSA Kénitra",
      "school_email": "contact@ensak.ma",
      "school_phone": "+212 537 374 000",
      "school_website": "https://www.ensak.ma",
      "academic_year": {
        "start": "2024-09-01",
        "end": "2025-06-30"
      }
    }'::jsonb,
    'general',
    'General school settings'
  ),
  (
    'security_settings',
    '{
      "two_factor_required": false,
      "login_attempts_limit": 5,
      "session_timeout": 30,
      "default_teacher_access": "consultation"
    }'::jsonb,
    'security',
    'Security configuration'
  ),
  (
    'notification_settings',
    '{
      "email_notifications": true,
      "project_submission": true,
      "evaluation_complete": true,
      "account_activity": true
    }'::jsonb,
    'notifications',
    'Notification preferences'
  );