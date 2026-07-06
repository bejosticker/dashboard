import type { ReactNode } from 'react';
import { Box, Card, CardContent, Typography } from '@mui/material';

export interface StatCardProps {
  label: ReactNode;
  value: ReactNode;
  icon?: ReactNode;
  /** Warna aksen ikon. */
  color?: 'primary' | 'success' | 'warning' | 'error' | 'info' | 'secondary';
}

/**
 * Kartu statistik untuk dashboard/ringkasan.
 */
export function StatCard({ label, value, icon, color = 'primary' }: StatCardProps) {
  return (
    <Card sx={{ height: '100%' }}>
      <CardContent
        sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: 2 }}
      >
        <Box>
          <Typography variant="body2" color="text.secondary" gutterBottom>
            {label}
          </Typography>
          <Typography variant="h5">{value}</Typography>
        </Box>
        {icon && (
          <Box
            sx={{
              width: 44,
              height: 44,
              borderRadius: 2,
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              bgcolor: (t) => `${t.palette[color].main}1a`,
              color: (t) => t.palette[color].main,
              flexShrink: 0,
            }}
          >
            {icon}
          </Box>
        )}
      </CardContent>
    </Card>
  );
}
