import { createTheme } from '@mui/material/styles';
import { tokens } from './tokens';

/**
 * Tema MUI terpusat. Semua default look-and-feel komponen di-set di sini
 * lewat `components.MuiXxx`. Untuk perubahan menyeluruh (mis. semua tombol),
 * ubah di sini atau di `tokens.ts` — bukan per pemakaian.
 */
export const theme = createTheme({
  palette: {
    mode: 'light',
    primary: { main: tokens.color.primary, dark: tokens.color.primaryDark, light: tokens.color.primaryLight },
    secondary: { main: tokens.color.secondary },
    success: { main: tokens.color.success },
    info: { main: tokens.color.info },
    warning: { main: tokens.color.warning },
    error: { main: tokens.color.danger },
    background: { default: tokens.color.bg, paper: tokens.color.paper },
    text: { primary: tokens.color.textPrimary, secondary: tokens.color.textMuted },
    divider: tokens.color.divider,
  },
  shape: { borderRadius: tokens.radius },
  typography: {
    fontFamily: tokens.font.family,
    h1: { color: tokens.color.textHeading, fontWeight: 600 },
    h2: { color: tokens.color.textHeading, fontWeight: 600 },
    h3: { color: tokens.color.textHeading, fontWeight: 600 },
    h4: { color: tokens.color.textHeading, fontWeight: 600 },
    h5: { color: tokens.color.textHeading, fontWeight: 600 },
    h6: { color: tokens.color.textHeading, fontWeight: 600 },
    button: { textTransform: 'none', fontWeight: 500 },
  },
  components: {
    MuiButton: {
      defaultProps: { disableElevation: true },
      // Semua tombol berbentuk pill (fully rounded) — diatur terpusat di sini.
      styleOverrides: { root: { borderRadius: tokens.radiusPill } },
    },
    MuiTextField: {
      defaultProps: { size: 'small', fullWidth: true },
    },
    MuiPaper: {
      styleOverrides: { rounded: { borderRadius: tokens.radius } },
    },
    MuiCard: {
      defaultProps: { elevation: 0 },
      styleOverrides: {
        root: { borderRadius: tokens.radius, boxShadow: tokens.shadow.card },
      },
    },
    MuiTableCell: {
      styleOverrides: {
        head: { fontWeight: 600, color: tokens.color.textHeading, whiteSpace: 'nowrap' },
      },
    },
    // Menu aktif (sidebar) pakai bg primary + teks/ikon putih — diatur terpusat.
    MuiListItemButton: {
      styleOverrides: {
        root: {
          borderRadius: tokens.radiusPill,
          '&.Mui-selected': {
            backgroundColor: tokens.color.primary,
            color: '#fff',
            '& .MuiListItemIcon-root': { color: '#fff' },
            '&:hover': { backgroundColor: tokens.color.primaryDark },
          },
        },
      },
    },
  },
});

export { tokens };
