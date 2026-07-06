import type { ReactNode } from 'react';
import {
  Card as MuiCard,
  CardContent,
  CardHeader,
  type CardProps as MuiCardProps,
} from '@mui/material';

export type CardProps = MuiCardProps & {
  title?: ReactNode;
  action?: ReactNode;
  /** Set false untuk menghilangkan padding CardContent (mis. tabel full-bleed). */
  disableContentPadding?: boolean;
};

/**
 * Kartu standar. Bila `title` diisi, otomatis render CardHeader.
 */
export function Card({ title, action, disableContentPadding, children, ...props }: CardProps) {
  return (
    <MuiCard {...props}>
      {(title || action) && <CardHeader title={title} action={action} />}
      <CardContent sx={disableContentPadding ? { p: 0, '&:last-child': { pb: 0 } } : undefined}>
        {children}
      </CardContent>
    </MuiCard>
  );
}
