import type { ReactNode } from 'react';
import { ThemeProvider } from '@mui/material/styles';
import CssBaseline from '@mui/material/CssBaseline';
import { theme } from './index';

/**
 * Membungkus seluruh aplikasi dengan tema terpusat + reset CSS MUI.
 * Dipasang sekali di `app.tsx`.
 */
export function ThemeRegistry({ children }: { children: ReactNode }) {
  return (
    <ThemeProvider theme={theme}>
      <CssBaseline />
      {children}
    </ThemeProvider>
  );
}
