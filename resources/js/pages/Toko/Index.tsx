import { useState, type FormEvent } from 'react';
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
  Pagination,
  Select,
  Stack,
  StatusChip,
  TextField,
  type Column,
} from '@/ui';
import { Plus, Pencil, Trash2 } from '@/icons';
import { formatDate } from '@/lib/format';
import type { Paginator } from '@/types';

interface Toko extends Record<string, unknown> {
  id: number;
  name: string;
  description: string | null;
  type: string;
  created_at: string | null;
}

interface Props {
  tokos: Paginator<Toko>;
}

const TYPE_OPTIONS = [
  { value: 'Offline', label: 'Offline' },
  { value: 'Online', label: 'Online' },
];

export default function Index({ tokos }: Props) {
  const [open, setOpen] = useState(false);
  const [editing, setEditing] = useState<Toko | null>(null);
  const [toDelete, setToDelete] = useState<Toko | null>(null);

  const form = useForm({ name: '', description: '', type: 'Offline' });

  const openCreate = () => {
    setEditing(null);
    form.reset();
    form.clearErrors();
    setOpen(true);
  };

  const openEdit = (t: Toko) => {
    setEditing(t);
    form.clearErrors();
    form.setData({
      name: t.name,
      description: t.description ?? '',
      type: t.type || 'Offline',
    });
    setOpen(true);
  };

  const submit = (e: FormEvent) => {
    e.preventDefault();
    const opts = { onSuccess: () => setOpen(false), preserveScroll: true };
    if (editing) form.post(`/toko/update/${editing.id}`, opts);
    else form.post('/toko', opts);
  };

  const columns: Column<Toko>[] = [
    { key: 'name', label: 'Nama' },
    {
      key: 'description',
      label: 'Keterangan',
      render: (t) => t.description || '-',
    },
    {
      key: 'type',
      label: 'Tipe',
      render: (t) => (
        <StatusChip label={t.type} color={t.type === 'Online' ? 'success' : 'primary'} />
      ),
    },
    {
      key: 'created_at',
      label: 'Dibuat',
      render: (t) => formatDate(t.created_at),
    },
    {
      key: 'aksi',
      label: '',
      align: 'right',
      render: (t) => (
        <Stack direction="row" spacing={0.5} justifyContent="flex-end">
          <IconButton onClick={() => openEdit(t)}>
            <Pencil size={16} />
          </IconButton>
          <IconButton color="error" onClick={() => setToDelete(t)}>
            <Trash2 size={16} />
          </IconButton>
        </Stack>
      ),
    },
  ];

  return (
    <>
      <Head title="Toko" />
      <PageHeader
        title="Toko"
        actions={
          <Button startIcon={<Plus size={18} />} onClick={openCreate}>
            Tambah Toko
          </Button>
        }
      />

      <Card disableContentPadding>
        <DataTable
          columns={columns}
          rows={tokos.data}
          getRowId={(t) => t.id}
          emptyMessage="Belum ada data toko."
        />
      </Card>
      <Box sx={{ px: 1 }}>
        <Pagination paginator={tokos} />
      </Box>

      <Dialog
        open={open}
        onClose={() => setOpen(false)}
        title={editing ? 'Edit Toko' : 'Tambah Toko'}
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
            label="Nama Toko"
            value={form.data.name}
            onChange={(e) => form.setData('name', e.target.value)}
            error={!!form.errors.name}
            helperText={form.errors.name}
            autoFocus
          />
          <TextField
            label="Keterangan (opsional)"
            value={form.data.description}
            onChange={(e) => form.setData('description', e.target.value)}
            error={!!form.errors.description}
            helperText={form.errors.description}
            multiline
            minRows={3}
          />
          <Select
            label="Tipe"
            value={form.data.type}
            onChange={(e) => form.setData('type', e.target.value)}
            options={TYPE_OPTIONS}
            error={!!form.errors.type}
            helperText={form.errors.type}
          />
        </Stack>
      </Dialog>

      <ConfirmDialog
        open={!!toDelete}
        title="Hapus Toko"
        message={`Apakah anda yakin menghapus toko "${toDelete?.name}"?`}
        onClose={() => setToDelete(null)}
        onConfirm={() => {
          if (toDelete)
            router.get(
              `/toko/delete/${toDelete.id}`,
              {},
              { preserveScroll: true, onFinish: () => setToDelete(null) },
            );
        }}
      />
    </>
  );
}
