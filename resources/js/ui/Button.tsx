import { forwardRef } from 'react';
import { Button as MuiButton, type ButtonProps } from '@mui/material';

export type { ButtonProps };

/**
 * Tombol standar aplikasi. Default `variant="contained"`.
 * Ubah gaya semua tombol di sini atau di theme (`MuiButton`).
 */
export const Button = forwardRef<HTMLButtonElement, ButtonProps>(function Button(
  { variant = 'contained', ...props },
  ref,
) {
  return <MuiButton ref={ref} variant={variant} {...props} />;
});
