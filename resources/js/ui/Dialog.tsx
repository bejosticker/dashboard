import type { ReactNode } from 'react';
import {
  Dialog as MuiDialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  IconButton,
  type DialogProps as MuiDialogProps,
} from '@mui/material';
import { X } from 'lucide-react';

export type DialogProps = Omit<MuiDialogProps, 'title'> & {
  title?: ReactNode;
  actions?: ReactNode;
  onClose?: () => void;
};

/**
 * Modal standar (pengganti modal Bootstrap). Struktur: title + content + actions.
 * Contoh:
 *   <Dialog open={open} onClose={close} title="Tambah Toko"
 *           actions={<><Button variant="text" onClick={close}>Batal</Button><Button onClick={submit}>Simpan</Button></>}>
 *     ...form...
 *   </Dialog>
 */
export function Dialog({
  title,
  actions,
  onClose,
  children,
  maxWidth = 'sm',
  fullWidth = true,
  ...props
}: DialogProps) {
  return (
    <MuiDialog onClose={onClose} maxWidth={maxWidth} fullWidth={fullWidth} {...props}>
      {title && (
        <DialogTitle sx={{ pr: 6 }}>
          {title}
          {onClose && (
            <IconButton
              onClick={onClose}
              size="small"
              sx={{ position: 'absolute', right: 12, top: 12 }}
              aria-label="Tutup"
            >
              <X size={18} />
            </IconButton>
          )}
        </DialogTitle>
      )}
      <DialogContent dividers>{children}</DialogContent>
      {actions && <DialogActions sx={{ px: 3, py: 2 }}>{actions}</DialogActions>}
    </MuiDialog>
  );
}
