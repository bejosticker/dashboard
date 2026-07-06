import { useState, type ChangeEvent, type FormEvent } from 'react';
import { Head, useForm, router } from '@inertiajs/react';
import {
  Box,
  Button,
  Card,
  ConfirmDialog,
  DataTable,
  Dialog,
  IconButton,
  PageHeader,
  Stack,
  TextField,
  Typography,
  type Column,
} from '@/ui';
import { Plus, Pencil, Trash2 } from '@/icons';
import { formatDate } from '@/lib/format';

interface PaymentMethod extends Record<string, unknown> {
  id: number;
  name: string;
  image: string;
  image_url: string;
  created_at: string | null;
}

interface Props {
  paymentMethods: PaymentMethod[];
}

export default function Index({ paymentMethods }: Props) {
  const [open, setOpen] = useState(false);
  const [editing, setEditing] = useState<PaymentMethod | null>(null);
  const [toDelete, setToDelete] = useState<PaymentMethod | null>(null);

  const form = useForm<{ name: string; image: File | null }>({
    name: '',
    image: null,
  });

  const openCreate = () => {
    setEditing(null);
    form.reset();
    form.clearErrors();
    setOpen(true);
  };

  const openEdit = (pm: PaymentMethod) => {
    setEditing(pm);
    form.clearErrors();
    form.setData({ name: pm.name, image: null });
    setOpen(true);
  };

  const submit = (e: FormEvent) => {
    e.preventDefault();
    const opts = {
      forceFormData: true,
      preserveScroll: true,
      onSuccess: () => setOpen(false),
    };
    if (editing) form.post(`/metode-pembayaran/update/${editing.id}`, opts);
    else form.post('/metode-pembayaran', opts);
  };

  const onFileChange = (e: ChangeEvent<HTMLInputElement>) => {
    form.setData('image', e.target.files?.[0] ?? null);
  };

  const columns: Column<PaymentMethod>[] = [
    {
      key: 'image',
      label: 'Icon',
      width: 80,
      render: (pm) => (
        <Box
          sx={{
            width: 56,
            height: 56,
            borderRadius: 2,
            overflow: 'hidden',
            bgcolor: 'action.hover',
          }}
        >
          <Box
            component="img"
            src={pm.image_url}
            alt={pm.name}
            sx={{ width: '100%', height: '100%', objectFit: 'cover' }}
          />
        </Box>
      ),
    },
    { key: 'name', label: 'Nama' },
    {
      key: 'created_at',
      label: 'Dibuat',
      render: (pm) => (pm.created_at ? formatDate(pm.created_at) : '-'),
    },
    {
      key: 'aksi',
      label: '',
      align: 'right',
      render: (pm) => (
        <Stack direction="row" spacing={0.5} justifyContent="flex-end">
          <IconButton onClick={() => openEdit(pm)}>
            <Pencil size={16} />
          </IconButton>
          <IconButton color="error" onClick={() => setToDelete(pm)}>
            <Trash2 size={16} />
          </IconButton>
        </Stack>
      ),
    },
  ];

  return (
    <>
      <Head title="Metode Pembayaran" />
      <PageHeader
        title="Metode Pembayaran"
        actions={
          <Button startIcon={<Plus size={18} />} onClick={openCreate}>
            Tambah Metode Pembayaran
          </Button>
        }
      />

      <Card disableContentPadding>
        <DataTable
          columns={columns}
          rows={paymentMethods}
          getRowId={(pm) => pm.id}
          emptyMessage="Belum ada data metode pembayaran."
        />
      </Card>

      <Dialog
        open={open}
        onClose={() => setOpen(false)}
        title={editing ? 'Edit Metode Pembayaran' : 'Tambah Metode Pembayaran'}
        actions={
          <>
            <Button variant="text" color="secondary" onClick={() => setOpen(false)}>
              Batal
            </Button>
            <Button onClick={submit} disabled={form.processing}>
              Simpan
            </Button>
          </>
        }
      >
        <Stack component="form" spacing={2} onSubmit={submit} sx={{ pt: 1 }}>
          <TextField
            label="Nama"
            value={form.data.name}
            onChange={(e) => form.setData('name', e.target.value)}
            error={!!form.errors.name}
            helperText={form.errors.name}
            autoFocus
          />
          <TextField
            type="file"
            label="Icon"
            InputLabelProps={{ shrink: true }}
            inputProps={{ accept: 'image/*' }}
            onChange={onFileChange}
            error={!!form.errors.image}
            helperText={
              form.errors.image ??
              (editing ? 'Kosongkan bila tidak ingin mengubah icon.' : undefined)
            }
          />
        </Stack>
      </Dialog>

      <ConfirmDialog
        open={!!toDelete}
        title="Hapus Metode Pembayaran"
        message={`Apakah anda yakin menghapus Metode Pembayaran ${toDelete?.name}?`}
        onClose={() => setToDelete(null)}
        onConfirm={() => {
          if (toDelete)
            router.get(
              `/metode-pembayaran/delete/${toDelete.id}`,
              {},
              { preserveScroll: true, onFinish: () => setToDelete(null) },
            );
        }}
      />

      {paymentMethods.length > 0 && (
        <Typography variant="caption" color="text.secondary" sx={{ display: 'block', mt: 1 }}>
          {paymentMethods.length} metode pembayaran.
        </Typography>
      )}
    </>
  );
}
