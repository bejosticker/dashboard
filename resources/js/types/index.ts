import type { PageProps as InertiaPageProps } from '@inertiajs/core';

/** User yang login (dari session `data`). */
export interface AuthUser {
  name: string | null;
  username: string | null;
  level: number;
}

/** Item menu sidebar (port verticalMenu.json). */
export interface MenuItem {
  url?: string;
  name?: string;
  icon?: string;
  slug?: string;
  menuHeader?: string;
  submenu?: MenuItem[];
}

/** Props yang di-share ke semua halaman lewat HandleInertiaRequests. */
export interface SharedProps extends InertiaPageProps {
  auth: { user: AuthUser | null };
  flash: { success: string | null; error: string | null };
  menu: MenuItem[];
}

/** Satu link paginasi bawaan Laravel paginator. */
export interface PaginationLink {
  url: string | null;
  label: string;
  active: boolean;
}

/** Bentuk paginator Laravel saat di-serialize ke JSON (`->paginate()`). */
export interface Paginator<T> {
  data: T[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number | null;
  to: number | null;
  links: PaginationLink[];
  first_page_url: string | null;
  last_page_url: string | null;
  next_page_url: string | null;
  prev_page_url: string | null;
  path: string;
}
