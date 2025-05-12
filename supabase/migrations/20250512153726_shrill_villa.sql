/*
  # Admin Settings and Statistics Tables

  1. New Tables
    - `settings`
      - General application settings
      - School information
      - Academic calendar
    - `security_settings`
      - Authentication settings
      - Session configuration
      - Access control defaults
    - `statistics_cache`
      - Cached statistics data
      - Performance optimization

  2. Security
    - Enable RLS on all tables
    - Add policies for admin access
*/

-- Settings table
CREATE TABLE IF NOT EXISTS settings (
  id INT PRIMARY KEY DEFAULT 1,
  school_name TEXT NOT NULL DEFAULT 'ENSA Kénitra',
  school_email TEXT NOT NULL DEFAULT 'contact@ensak.ma',
  school_phone TEXT NOT NULL DEFAULT '+212 537 374 000',
  school_website TEXT NOT NULL DEFAULT 'https://www.ensak.ma',
  year_start DATE NOT NULL DEFAULT CURRENT_DATE,
  year_end DATE NOT NULL DEFAULT CURRENT_DATE + INTERVAL '1 year',
  updated_at TIMESTAMPTZ DEFAULT now(),
  CONSTRAINT single_settings_row CHECK (id = 1)
);

-- Security settings table
CREATE TABLE IF NOT EXISTS security_settings (
  id INT PRIMARY KEY DEFAULT 1,
  two_factor_required BOOLEAN DEFAULT false,
  login_attempts_limit INT DEFAULT 5,
  session_timeout INT DEFAULT 30, -- minutes
  default_teacher_access TEXT DEFAULT 'consultation',
  updated_at TIMESTAMPTZ DEFAULT now(),
  CONSTRAINT single_security_settings_row CHECK (id = 1)
);

-- Statistics cache table
CREATE TABLE IF NOT EXISTS statistics_cache (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  cache_key TEXT NOT NULL UNIQUE,
  cache_data JSONB NOT NULL,
  created_at TIMESTAMPTZ DEFAULT now(),
  expires_at TIMESTAMPTZ NOT NULL,
  CONSTRAINT valid_expiration CHECK (expires_at > created_at)
);

-- Enable RLS
ALTER TABLE settings ENABLE ROW LEVEL SECURITY;
ALTER TABLE security_settings ENABLE ROW LEVEL SECURITY;
ALTER TABLE statistics_cache ENABLE ROW LEVEL SECURITY;

-- RLS Policies
CREATE POLICY "Only admins can read settings" ON settings
  FOR SELECT TO authenticated
  USING (auth.jwt() ->> 'role' = 'admin');

CREATE POLICY "Only admins can modify settings" ON settings
  FOR ALL TO authenticated
  USING (auth.jwt() ->> 'role' = 'admin');

CREATE POLICY "Only admins can read security settings" ON security_settings
  FOR SELECT TO authenticated
  USING (auth.jwt() ->> 'role' = 'admin');

CREATE POLICY "Only admins can modify security settings" ON security_settings
  FOR ALL TO authenticated
  USING (auth.jwt() ->> 'role' = 'admin');

CREATE POLICY "Only admins can manage statistics cache" ON statistics_cache
  FOR ALL TO authenticated
  USING (auth.jwt() ->> 'role' = 'admin');

-- Insert default settings
INSERT INTO settings (id) VALUES (1)
ON CONFLICT (id) DO NOTHING;

INSERT INTO security_settings (id) VALUES (1)
ON CONFLICT (id) DO NOTHING;

-- Create indexes
CREATE INDEX idx_statistics_cache_expires ON statistics_cache(expires_at);
CREATE INDEX idx_statistics_cache_key ON statistics_cache(cache_key);