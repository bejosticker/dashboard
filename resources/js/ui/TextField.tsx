import { forwardRef } from 'react';
import { TextField as MuiTextField, type TextFieldProps as MuiTextFieldProps } from '@mui/material';

/**
 * Input teks standar. Default `size="small"`, `fullWidth` (dari theme).
 *
 * Kompatibilitas: menerima prop gaya lama `inputProps` / `InputProps` /
 * `InputLabelProps` dan memetakannya ke `slotProps` (MUI v9). Contoh angka desimal:
 *   <TextField type="number" inputProps={{ step: 'any', inputMode: 'decimal' }} />
 */
export type TextFieldProps = MuiTextFieldProps & {
  inputProps?: Record<string, unknown>;
  InputProps?: Record<string, unknown>;
  InputLabelProps?: Record<string, unknown>;
};

export const TextField = forwardRef<HTMLDivElement, TextFieldProps>(function TextField(
  { inputProps, InputProps, InputLabelProps, slotProps, ...props },
  ref,
) {
  const mergedSlotProps = {
    ...(slotProps as Record<string, unknown> | undefined),
    ...(inputProps ? { htmlInput: inputProps } : {}),
    ...(InputProps ? { input: InputProps } : {}),
    ...(InputLabelProps ? { inputLabel: InputLabelProps } : {}),
  };

  return <MuiTextField ref={ref} slotProps={mergedSlotProps} {...props} />;
});
