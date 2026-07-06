import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';
import { StrictMode, type ReactNode } from 'react';
import { ThemeRegistry } from '@/theme/ThemeRegistry';
import { AppLayout } from '@/layouts/AppLayout';

const APP_NAME = 'BejoSticker';

const pages = import.meta.glob('./pages/**/*.tsx');

type PageModule = {
  default: {
    layout?: ((page: ReactNode) => ReactNode) | null;
  };
};

createInertiaApp({
  title: (title) => (title ? `${title} · ${APP_NAME}` : APP_NAME),
  resolve: async (name) => {
    const importPage = pages[`./pages/${name}.tsx`];

    if (!importPage) {
      throw new Error(`Halaman Inertia tidak ditemukan: ${name}`);
    }

    const page = (await importPage()) as PageModule;

    // Layout default: semua halaman dibungkus AppLayout kecuali halaman yang
    // secara eksplisit meng-set `Page.layout = null` (mis. halaman login).
    if (page.default.layout === undefined) {
      page.default.layout = (pageEl: ReactNode) => <AppLayout>{pageEl}</AppLayout>;
    }

    return page;
  },
  setup({ el, App, props }) {
    createRoot(el).render(
      <StrictMode>
        <ThemeRegistry>
          <App {...props} />
        </ThemeRegistry>
      </StrictMode>,
    );
  },
  progress: {
    color: '#696cff',
  },
});
