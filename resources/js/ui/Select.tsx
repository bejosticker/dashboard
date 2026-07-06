import { MenuItem, TextField, type TextFieldProps } from '@mui/material';

export interface SelectOption {
  value: string | number;
  label: string;
}

export type SelectProps = Omit<TextFieldProps, 'select'> & {
  options: SelectOption[];
  /** Teks opsi kosong di paling atas (mis. "-- Pilih --"). */
  placeholder?: string;
};

/**
 * Dropdown standar berbasis MUI TextField(select) + daftar `options`.
 * Contoh:
 *   <Select label="Toko" value={v} onChange={e => set(e.target.value)}
 *           placeholder="-- Pilih Toko --" options={tokos.map(t => ({ value: t.id, label: t.name }))} />
 */
export function Select({ options, placeholder, ...props }: SelectProps) {
  return (
    <TextField select {...props}>
      {placeholder !== undefined && (
        <MenuItem value="">
          <em>{placeholder}</em>
        </MenuItem>
      )}
      {options.map((opt) => (
        <MenuItem key={String(opt.value)} value={opt.value}>
          {opt.label}
        </MenuItem>
      ))}
    </TextField>
  );
}
