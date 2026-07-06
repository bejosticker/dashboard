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
  Stack,
  TextField,
  type Column,
} from '@/ui';
import { Plus, Pencil, Trash2 } from '@/icons';
import { formatDate } from '@/lib/format';

interface Supplier extends Record<string, unknown> {
  id: number;
  name: string;
  description: string | null;
  created_at: string | null;
}

interface Props {
  suppliers: Supplier[];
}

export default function Index({ suppliers }: Props) {
  const [open, setOpen] = useState(false);
  const [editing, setEditing] = useState<Supplier | null>(null);
  const [toDelete, setToDelete] = useState<Supplier | null>(null);

  const form = useForm({ name: '', description: '' });

  const openCreate = () => {
    setEditing(null);
    form.reset();
    form.clearErrors();
    setOpen(true);
  };

  const openEdit = (s: Supplier) => {
    setEditing(s);
    form.clearErrors();
    form.setData({ name: s.name, description: s.description ?? '' });
    setOpen(true);
  };

  const submit = (e: FormEvent) => {
    e.preventDefault();
    const opts = { onSuccess: () => setOpen(false), preserveScroll: true };
    if (editing) form.post(`/suppliers/update/${editing.id}`, opts);
    else form.post('/suppliers', opts);
  };

  const columns: Column<Supplier>[] = [
    { key: 'no', label: '#', width: 56, render: (_s, i) => i + 1 },
    { key: 'name', label: 'Nama' },
    {
      key: 'description',
      label: 'Keterangan',
      render: (s) => s.description || '-',
    },
    {
      key: 'created_at',
      label: 'Dibuat',
      render: (s) => formatDate(s.created_at),
    },
    {
      key: 'aksi',
      label: '',
      align: 'right',
      render: (s) => (
        <Stack direction="row" spacing={0.5} justifyContent="flex-end">
          <IconButton onClick={() => openEdit(s)} aria-label="Ubah">
            <Pencil size={16} />
          </IconButton>
          <IconButton color="error" onClick={() => setToDelete(s)} aria-label="Hapus">
            <Trash2 size={16} />
          </IconButton>
        </Stack>
      ),
    },
  ];

  return (
    <>
      <Head title="Supplier" />
      <PageHeader
        title="Supplier"
        actions={
          <Button startIcon={<Plus size={18} />} onClick={openCreate}>
            Tambah Supplier
          </Button>
        }
      />

      <Card disableContentPadding>
        <DataTable
          columns={columns}
          rows={suppliers}
          getRowId={(s) => s.id}
          emptyMessage="Belum ada data supplier."
        />
      </Card>

      <Dialog
        open={open}
        onClose={() => setOpen(false)}
        title={editing ? 'Edit Supplier' : 'Tambah Supplier'}
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
            label="Nama Supplier"
            value={form.data.name}
            onChange={(e) => form.setData('name', e.target.value)}
            error={!!form.errors.name}
            helperText={form.errors.name}
            placeholder="Masukkan nama..."
            autoFocus
          />
          <TextField
            label="Keterangan (opsional)"
            value={form.data.description}
            onChange={(e) => form.setData('description', e.target.value)}
            error={!!form.errors.description}
            helperText={form.errors.description}
            placeholder="Keterangan..."
            multiline
            minRows={3}
          />
        </Stack>
      </Dialog>

      <ConfirmDialog
        open={!!toDelete}
        title="Hapus Supplier"
        message={`Apakah anda yakin menghapus supplier ${toDelete?.name}?`}
        onClose={() => setToDelete(null)}
        onConfirm={() => {
          if (toDelete)
            router.get(
              `/suppliers/delete/${toDelete.id}`,
              {},
              { preserveScroll: true, onFinish: () => setToDelete(null) },
            );
        }}
      />
    </>
  );
}
