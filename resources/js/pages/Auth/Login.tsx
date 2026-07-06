import { type FormEvent } from 'react';
import { Head, useForm } from '@inertiajs/react';
import { Alert, Box, Button, Card, Stack, TextField, Typography } from '@/ui';

export default function Login() {
  const form = useForm({ username: '', password: '' });

  const submit = (e: FormEvent) => {
    e.preventDefault();
    form.post('/auth/login', { onFinish: () => form.reset('password') });
  };

  return (
    <>
      <Head title="Masuk" />
      <Box
        sx={{
          minHeight: '100vh',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          bgcolor: 'background.default',
          p: 2,
        }}
      >
        <Card sx={{ width: '100%', maxWidth: 400 }}>
          <Stack spacing={3} alignItems="center" sx={{ mb: 1 }}>
            <Box component="img" src="/assets/img/logo.png" alt="BejoSticker" sx={{ width: 56, height: 56 }} />
            <Box sx={{ textAlign: 'center' }}>
              <Typography variant="h5">BejoSticker</Typography>
              <Typography variant="body2" color="text.secondary">
                Masuk untuk melanjutkan
              </Typography>
            </Box>
          </Stack>

          <Stack component="form" spacing={2} onSubmit={submit}>
            {form.errors.username && <Alert severity="error">{form.errors.username}</Alert>}
            <TextField
              label="Username"
              value={form.data.username}
              onChange={(e) => form.setData('username', e.target.value)}
              autoFocus
            />
            <TextField
              label="Password"
              type="password"
              value={form.data.password}
              onChange={(e) => form.setData('password', e.target.value)}
            />
            <Button type="submit" size="large" disabled={form.processing}>
              {form.processing ? 'Memproses…' : 'Masuk'}
            </Button>
          </Stack>
        </Card>
      </Box>
    </>
  );
}

// Halaman login tidak memakai AppLayout (tanpa sidebar/navbar).
Login.layout = null;
