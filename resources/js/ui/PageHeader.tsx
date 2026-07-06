import type { ReactNode } from 'react';
import { Box, Typography } from '@mui/material';

export interface PageHeaderProps {
  title: ReactNode;
  subtitle?: ReactNode;
  /** Tombol/aksi di kanan (mis. "Tambah"). */
  actions?: ReactNode;
}

/**
 * Header halaman standar: judul kiri, aksi kanan.
 */
export function PageHeader({ title, subtitle, actions }: PageHeaderProps) {
  return (
    <Box
      sx={{
        display: 'flex',
        flexWrap: 'wrap',
        gap: 2,
        alignItems: 'center',
        justifyContent: 'space-between',
        mb: 3,
      }}
    >
      <Box>
        <Typography variant="h5">{title}</Typography>
        {subtitle && (
          <Typography variant="body2" color="text.secondary">
            {subtitle}
          </Typography>
        )}
      </Box>
      {actions && <Box sx={{ display: 'flex', gap: 1, flexWrap: 'wrap' }}>{actions}</Box>}
    </Box>
  );
}
