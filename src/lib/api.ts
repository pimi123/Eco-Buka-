const apiBaseUrl = import.meta.env.VITE_API_BASE_URL;

export const hasLaravelApiConfig = Boolean(apiBaseUrl);

export async function apiGet<T>(path: string): Promise<T> {
  const base = String(apiBaseUrl).replace(/\/$/, '');
  const cleanPath = path.startsWith('/') ? path : `/${path}`;
  const response = await fetch(`${base}${cleanPath}`, {
    headers: { Accept: 'application/json' },
  });

  if (!response.ok) {
    throw new Error(`API request failed: ${response.status}`);
  }

  return response.json() as Promise<T>;
}
