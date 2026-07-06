import type { ReactNode } from 'react';
import { Typography } from '@mui/material';
import { Dialog } from './Dialog';
import { Button } from './Button';

export interface ConfirmDialogProps {
  open: boolean;
  title?: ReactNode;
  message?: ReactNode;
  confirmLabel?: string;
  cancelLabel?: string;
  loading?: boolean;
  /** Warna tombol konfirmasi (mis. "error" untuk hapus). */
  confirmColor?: 'primary' | 'error' | 'warning';
  onConfirm: () => void;
  onClose: () => void;
}

/**
 * Dialog konfirmasi standar — pengganti pola "yakin hapus?".
 */
export function ConfirmDialog({
  open,
  title = 'Konfirmasi',
  message = 'Apakah Anda yakin?',
  confirmLabel = 'Ya, lanjutkan',
  cancelLabel = 'Batal',
  loading = false,
  confirmColor = 'error',
  onConfirm,
  onClose,
}: ConfirmDialogProps) {
  return (
    <Dialog
      open={open}
      onClose={onClose}
      title={title}
      maxWidth="xs"
      actions={
        <>
          <Button variant="text" color="secondary" onClick={onClose} disabled={loading}>
            {cancelLabel}
          </Button>
          <Button color={confirmColor} onClick={onConfirm} disabled={loading}>
            {confirmLabel}
          </Button>
        </>
      }
    >
      <Typography variant="body2">{message}</Typography>
    </Dialog>
  );
}
