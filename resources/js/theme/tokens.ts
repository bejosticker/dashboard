/**
 * ⭐ SATU sumber kebenaran untuk warna, radius, spacing, dan elevasi.
 * Ubah brand di sini → seluruh aplikasi ikut berubah. Jangan tulis hex
 * langsung di komponen; selalu rujuk token dari file ini.
 *
 * Palet mengikuti template Sneat (identitas ungu) agar transisi visual halus.
 */
export const tokens = {
  color: {
    primary: '#696cff',
    primaryDark: '#5f61e6',
    primaryLight: '#e7e7ff',
    secondary: '#8592a3',
    success: '#71dd37',
    info: '#03c3ec',
    warning: '#ffab00',
    danger: '#ff3e1d',
    dark: '#233446',

    bg: '#f5f5f9',
    paper: '#ffffff',

    textPrimary: '#566a7f',
    textHeading: '#384551',
    textMuted: '#a1acb8',
    divider: '#e4e6e8',
  },
  radius: 8,
  spacingUnit: 8,
  shadow: {
    card: '0 0.25rem 1.125rem rgba(75, 70, 92, 0.1)',
  },
  font: {
    family:
      '"Public Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif',
  },
} as const;

export type Tokens = typeof tokens;
