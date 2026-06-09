create table if not exists categories (
  id uuid primary key default gen_random_uuid(),
  name text not null,
  slug text unique not null,
  description text,
  image_url text,
  is_new boolean default false,
  active boolean default true,
  created_at timestamp default now()
);

create table if not exists products (
  id uuid primary key default gen_random_uuid(),
  category_id uuid references categories(id) on delete set null,
  name text not null,
  slug text unique not null,
  short_description text,
  description text,
  price numeric,
  old_price numeric,
  image_url text,
  gallery jsonb default '[]',
  specs jsonb default '{}',
  badge text,
  featured boolean default false,
  active boolean default true,
  created_at timestamp default now(),
  updated_at timestamp default now()
);

create table if not exists homepage_sections (
  id uuid primary key default gen_random_uuid(),
  section_key text unique not null,
  title text,
  subtitle text,
  layout_type text,
  active boolean default true,
  sort_order integer default 0,
  created_at timestamp default now(),
  updated_at timestamp default now()
);

create table if not exists homepage_section_products (
  id uuid primary key default gen_random_uuid(),
  section_id uuid references homepage_sections(id) on delete cascade,
  product_id uuid references products(id) on delete cascade,
  sort_order integer default 0,
  active boolean default true,
  created_at timestamp default now()
);

create table if not exists homepage_navigation_cards (
  id uuid primary key default gen_random_uuid(),
  section_id uuid references homepage_sections(id) on delete cascade,
  title text not null,
  link text,
  image_url text,
  active boolean default true,
  sort_order integer default 0,
  created_at timestamp default now(),
  updated_at timestamp default now()
);

create table if not exists homepage_banners (
  id uuid primary key default gen_random_uuid(),
  section_id uuid references homepage_sections(id) on delete cascade,
  section_heading text,
  eyebrow text,
  title text not null,
  subtitle text,
  price_text text,
  button_text text,
  button_link text,
  background_video_url text,
  background_image_url text,
  mobile_background_image_url text,
  text_color text default 'light',
  text_alignment text default 'left',
  active boolean default true,
  sort_order integer default 0,
  created_at timestamp default now(),
  updated_at timestamp default now()
);

create table if not exists homepage_promo_cards (
  id uuid primary key default gen_random_uuid(),
  section_key text not null default 'new_products',
  label text,
  title text not null,
  subtitle text,
  button_text text,
  button_link text,
  category_slug text,
  background_image_url text,
  mobile_background_image_url text,
  text_color text default 'light',
  active boolean default true,
  sort_order integer default 0,
  created_at timestamp default now(),
  updated_at timestamp default now()
);

create index if not exists homepage_promo_cards_section_active_sort_idx
on homepage_promo_cards (section_key, active, sort_order);

insert into storage.buckets (id, name, public)
values ('product-images', 'product-images', true)
on conflict (id) do update set public = true;

drop policy if exists "Public image read access" on storage.objects;
create policy "Public image read access"
on storage.objects for select
using (bucket_id = 'product-images');

drop policy if exists "Dashboard image upload access" on storage.objects;
create policy "Dashboard image upload access"
on storage.objects for insert
with check (bucket_id = 'product-images');

drop policy if exists "Dashboard image update access" on storage.objects;
create policy "Dashboard image update access"
on storage.objects for update
using (bucket_id = 'product-images')
with check (bucket_id = 'product-images');

drop policy if exists "Dashboard image delete access" on storage.objects;
create policy "Dashboard image delete access"
on storage.objects for delete
using (bucket_id = 'product-images');
