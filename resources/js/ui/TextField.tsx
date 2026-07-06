import { forwardRef } from 'react';
import { TextField as MuiTextField, type TextFieldProps } from '@mui/material';

export type { TextFieldProps };

/**
 * Input teks standar. Default `size="small"`, `fullWidth` (dari theme).
 * Untuk input angka desimal gunakan:
 *   <TextField type="number" inputProps={{ step: 'any', inputMode: 'decimal' }} />
 */
export const TextField = forwardRef<HTMLDivElement, TextFieldProps>(function TextField(props, ref) {
  return <MuiTextField ref={ref} {...props} />;
});
