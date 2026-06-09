import { hasSupabaseConfig, storageBucket, supabase } from './supabase';

const allowedImageTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
const maxImageBytes = 8 * 1024 * 1024;

function safeFileName(name: string) {
  const extension = name.split('.').pop()?.toLowerCase() || 'jpg';
  const base = name
    .replace(/\.[^/.]+$/, '')
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/(^-|-$)/g, '')
    .slice(0, 48);

  return `${base || 'image'}-${crypto.randomUUID()}.${extension}`;
}

export function validateImageFile(file: File) {
  if (!allowedImageTypes.includes(file.type)) {
    throw new Error('Please upload a JPG, PNG, WebP, or GIF image.');
  }

  if (file.size > maxImageBytes) {
    throw new Error('Image must be smaller than 8MB.');
  }
}

export async function uploadImageFile(file: File, folder = 'uploads') {
  validateImageFile(file);

  if (!hasSupabaseConfig || !supabase) {
    return {
      publicUrl: URL.createObjectURL(file),
      path: '',
      isTemporary: true,
    };
  }

  const path = `${folder}/${safeFileName(file.name)}`;
  const { error } = await supabase.storage.from(storageBucket).upload(path, file, {
    cacheControl: '31536000',
    contentType: file.type,
    upsert: false,
  });

  if (error) throw error;

  const { data } = supabase.storage.from(storageBucket).getPublicUrl(path);

  return {
    publicUrl: data.publicUrl,
    path,
    isTemporary: false,
  };
}
