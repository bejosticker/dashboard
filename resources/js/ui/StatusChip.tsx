import { Chip, type ChipProps } from '@mui/material';

export type StatusChipProps = ChipProps;

/**
 * Chip status/label standar (mis. "Stok Menipis", "Lunas").
 */
export function StatusChip(props: StatusChipProps) {
  return <Chip size="small" variant="filled" {...props} />;
}
