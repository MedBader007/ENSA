import { useState, useEffect } from 'react';
import { getAdminSettings, updateAdminSetting } from '../../lib/admin';

export function SettingsForm() {
  const [settings, setSettings] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    loadSettings();
  }, []);

  async function loadSettings() {
    try {
      const data = await getAdminSettings();
      setSettings(data);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to load settings');
    } finally {
      setLoading(false);
    }
  }

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setLoading(true);
    
    try {
      // Update each setting category
      for (const setting of settings) {
        await updateAdminSetting(setting.setting_key, setting.setting_value);
      }
      
      showNotification('success', 'Settings updated successfully');
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to update settings');
      showNotification('error', 'Failed to update settings');
    } finally {
      setLoading(false);
    }
  }

  if (loading) return <div>Loading...</div>;
  if (error) return <div>Error: {error}</div>;
  if (!settings) return null;

  return (
    <form onSubmit={handleSubmit} className="space-y-6">
      {settings.map((setting: any) => (
        <div key={setting.setting_key} className="space-y-4">
          <h3 className="text-lg font-semibold capitalize">
            {setting.category} Settings
          </h3>
          
          {Object.entries(setting.setting_value).map(([key, value]: [string, any]) => (
            <div key={key} className="form-group">
              <label htmlFor={key} className="block text-sm font-medium text-gray-700">
                {key.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ')}
              </label>
              
              {typeof value === 'boolean' ? (
                <input
                  type="checkbox"
                  id={key}
                  checked={value}
                  onChange={e => {
                    const newSettings = [...settings];
                    const settingIndex = newSettings.findIndex(s => s.setting_key === setting.setting_key);
                    newSettings[settingIndex].setting_value[key] = e.target.checked;
                    setSettings(newSettings);
                  }}
                  className="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                />
              ) : (
                <input
                  type={typeof value === 'number' ? 'number' : 'text'}
                  id={key}
                  value={value}
                  onChange={e => {
                    const newSettings = [...settings];
                    const settingIndex = newSettings.findIndex(s => s.setting_key === setting.setting_key);
                    newSettings[settingIndex].setting_value[key] = 
                      typeof value === 'number' ? Number(e.target.value) : e.target.value;
                    setSettings(newSettings);
                  }}
                  className="mt-1 block w-full shadow-sm sm:text-sm focus:ring-indigo-500 focus:border-indigo-500 border-gray-300 rounded-md"
                />
              )}
            </div>
          ))}
        </div>
      ))}
      
      <div className="flex justify-end">
        <button
          type="submit"
          disabled={loading}
          className="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        >
          {loading ? 'Saving...' : 'Save Changes'}
        </button>
      </div>
    </form>
  );
}