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
  Stack,
  TextField,
  type Column,
} from '@/ui';
import { Plus, Pencil, Trash2, Search, FileSpreadsheet } from '@/icons';
import { formatDate } from '@/lib/format';
import type { Paginator } from '@/types';

interface Customer extends Record<string, unknown> {
  id: number;
  name: string | null;
  phone: string;
  created_at: string | null;
}

interface Props {
  customers: Paginator<Customer>;
  filters: { search?: string };
}

export default function Index({ customers, filters }: Props) {
  const [open, setOpen] = useState(false);
  const [editing, setEditing] = useState<Customer | null>(null);
  const [toDelete, setToDelete] = useState<Customer | null>(null);
  const [search, setSearch] = useState(filters.search ?? '');

  const form = useForm({ name: '', phone: '' });

  const openCreate = () => {
    setEditing(null);
    form.reset();
    form.clearErrors();
    setOpen(true);
  };

  const openEdit = (c: Customer) => {
    setEditing(c);
    form.clearErrors();
    form.setData({ name: c.name ?? '', phone: c.phone });
    setOpen(true);
  };

  const submit = (e: FormEvent) => {
    e.preventDefault();
    const opts = { onSuccess: () => setOpen(false), preserveScroll: true };
    if (editing) form.post(`/customers/update/${editing.id}`, opts);
    else form.post('/customers', opts);
  };

  const submitSearch = (e: FormEvent) => {
    e.preventDefault();
    router.get('/customers', { search }, { preserveState: true, replace: true });
  };

  const columns: Column<Customer>[] = [
    {
      key: 'no',
      label: '#',
      width: 60,
      render: (_c, i) => (customers.from ?? 0) + i,
    },
    { key: 'name', label: 'Nama Pelanggan', render: (c) => c.name || '-' },
    { key: 'phone', label: 'Nomor WA' },
    { key: 'created_at', label: 'Dibuat', render: (c) => formatDate(c.created_at) },
    {
      key: 'aksi',
      label: 'Aksi',
      align: 'right',
      render: (c) => (
        <Stack direction="row" spacing={0.5} justifyContent="flex-end">
          <IconButton onClick={() => openEdit(c)}>
            <Pencil size={16} />
          </IconButton>
          <IconButton color="error" onClick={() => setToDelete(c)}>
            <Trash2 size={16} />
          </IconButton>
        </Stack>
      ),
    },
  ];

  return (
    <>
      <Head title="Pelanggan" />
      <PageHeader
        title="Pelanggan"
        actions={
          <Stack direction="row" spacing={1}>
            <Button
              component="a"
              href={`/customers/export${search ? `?search=${encodeURIComponent(search)}` : ''}`}
              color="info"
              startIcon={<FileSpreadsheet size={18} />}
            >
              Export Excel
            </Button>
            <Button startIcon={<Plus size={18} />} onClick={openCreate}>
              Tambah Pelanggan
            </Button>
          </Stack>
        }
      />

      <Card sx={{ mb: 2 }}>
        <Stack component="form" direction="row" spacing={1} onSubmit={submitSearch}>
          <TextField
            placeholder="Cari nama / nomor WA..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
          />
          <Button type="submit" startIcon={<Search size={18} />}>
            Cari
          </Button>
        </Stack>
      </Card>

      <Card disableContentPadding>
        <DataTable
          columns={columns}
          rows={customers.data}
          getRowId={(c) => c.id}
          emptyMessage="Belum ada data pelanggan."
        />
      </Card>
      <Box sx={{ px: 1 }}>
        <Pagination paginator={customers} />
      </Box>

      <Dialog
        open={open}
        onClose={() => setOpen(false)}
        title={editing ? 'Ubah Pelanggan' : 'Tambah Pelanggan'}
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
            label="Nama Pelanggan"
            placeholder="Masukkan nama..."
            value={form.data.name}
            onChange={(e) => form.setData('name', e.target.value)}
            error={!!form.errors.name}
            helperText={form.errors.name}
            autoFocus
          />
          <TextField
            label="Nomor WA"
            placeholder="08xxxxxxxxx"
            value={form.data.phone}
            onChange={(e) => form.setData('phone', e.target.value)}
            error={!!form.errors.phone}
            helperText={form.errors.phone}
          />
        </Stack>
      </Dialog>

      <ConfirmDialog
        open={!!toDelete}
        title="Hapus Pelanggan"
        message={`Apakah anda yakin menghapus pelanggan ${toDelete?.name || toDelete?.phone}?`}
        onClose={() => setToDelete(null)}
        onConfirm={() => {
          if (toDelete)
            router.get(
              `/customers/delete/${toDelete.id}`,
              {},
              { preserveScroll: true, onFinish: () => setToDelete(null) },
            );
        }}
      />
    </>
  );
}
