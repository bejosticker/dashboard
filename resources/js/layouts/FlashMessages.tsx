import { useEffect, useState } from 'react';
import { Alert, Snackbar } from '@mui/material';
import { usePage } from '@inertiajs/react';
import type { SharedProps } from '@/types';

/**
 * Menampilkan flash message (`success`/`error`) dari session sebagai Snackbar.
 * Muncul ulang setiap kali pesan berubah.
 */
export function FlashMessages() {
  const { props } = usePage<SharedProps>();
  const success = props.flash?.success ?? null;
  const error = props.flash?.error ?? null;

  const [open, setOpen] = useState(false);
  const message = error ?? success;
  const severity = error ? 'error' : 'success';

  useEffect(() => {
    setOpen(Boolean(message));
  }, [message]);

  if (!message) return null;

  return (
    <Snackbar
      open={open}
      autoHideDuration={4000}
      onClose={() => setOpen(false)}
      anchorOrigin={{ vertical: 'bottom', horizontal: 'right' }}
    >
      <Alert onClose={() => setOpen(false)} severity={severity} variant="filled" sx={{ width: '100%' }}>
        {message}
      </Alert>
    </Snackbar>
  );
}
