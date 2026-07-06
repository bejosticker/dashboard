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
  TextField,
  Typography,
  type Column,
} from '@/ui';
import { Plus, Pencil, Trash2, Search } from '@/icons';
import { formatRupiah, formatDate } from '@/lib/format';
import type { Paginator } from '@/types';

interface Toko {
  id: number;
  name: string;
}

interface TokoRel {
  id: number;
  name: string;
}

interface Pengeluaran extends Record<string, unknown> {
  id: number;
  name: string;
  description: string | null;
  date: string;
  amount: number;
  toko_id: number | null;
  toko: TokoRel | null;
}

interface Props {
  pengeluarans: Paginator<Pengeluaran>;
  tokos: Toko[];
  total: number;
  filters: { toko_id?: string | number; from?: string; to?: string };
}

interface FormShape {
  name: string;
  description: string;
  date: string;
  toko_id: string;
  amount: string;
}

const emptyForm: FormShape = { name: '', description: '', date: '', toko_id: '', amount: '' };

export default function Index({ pengeluarans, tokos, total, filters }: Props) {
  const [open, setOpen] = useState(false);
  const [editing, setEditing] = useState<Pengeluaran | null>(null);
  const [toDelete, setToDelete] = useState<Pengeluaran | null>(null);

  const [tokoFilter, setTokoFilter] = useState(String(filters.toko_id ?? ''));
  const [from, setFrom] = useState(filters.from ?? '');
  const [to, setTo] = useState(filters.to ?? '');

  const form = useForm<FormShape>({ ...emptyForm });

  const tokoOptions = tokos.map((t) => ({ value: t.id, label: t.name }));

  const applyFilter = (e: FormEvent) => {
    e.preventDefault();
    router.get(
      '/pengeluaran',
      { toko_id: tokoFilter, from, to },
      { preserveState: true, preserveScroll: true, replace: true },
    );
  };

  const openCreate = () => {
    setEditing(null);
    form.reset();
    form.clearErrors();
    setOpen(true);
  };

  const openEdit = (p: Pengeluaran) => {
    setEditing(p);
    form.clearErrors();
    form.setData({
      name: p.name ?? '',
      description: p.description ?? '',
      date: p.date ?? '',
      toko_id: p.toko_id != null ? String(p.toko_id) : '',
      amount: p.amount != null ? String(p.amount) : '',
    });
    setOpen(true);
  };

  const submit = (e: FormEvent) => {
    e.preventDefault();
    const opts = { onSuccess: () => setOpen(false), preserveScroll: true };
    if (editing) form.post(`/pengeluaran/update/${editing.id}`, opts);
    else form.post('/pengeluaran', opts);
  };

  const columns: Column<Pengeluaran>[] = [
    { key: 'toko', label: 'Toko', render: (p) => p.toko?.name ?? '-' },
    { key: 'name', label: 'Nama' },
    { key: 'description', label: 'Keterangan', render: (p) => p.description || '-' },
    { key: 'amount', label: 'Nominal', align: 'right', render: (p) => formatRupiah(p.amount) },
    { key: 'date', label: 'Tanggal', render: (p) => formatDate(p.date) },
    {
      key: 'aksi',
      label: '',
      align: 'right',
      width: 100,
      render: (p) => (
        <Stack direction="row" spacing={0.5} justifyContent="flex-end">
          <IconButton onClick={() => openEdit(p)}>
            <Pencil size={16} />
          </IconButton>
          <IconButton color="error" onClick={() => setToDelete(p)}>
            <Trash2 size={16} />
          </IconButton>
        </Stack>
      ),
    },
  ];

  return (
    <>
      <Head title="Pengeluaran" />
      <PageHeader
        title="Pengeluaran"
        actions={
          <Button startIcon={<Plus size={18} />} onClick={openCreate}>
            Tambah Pengeluaran
          </Button>
        }
      />

      <Card sx={{ mb: 3 }}>
        <Stack
          component="form"
          onSubmit={applyFilter}
          direction={{ xs: 'column', md: 'row' }}
          spacing={2}
          alignItems={{ md: 'flex-end' }}
        >
          <Select
            label="Toko"
            value={tokoFilter}
            onChange={(e) => setTokoFilter(e.target.value)}
            placeholder="Semua Toko"
            options={tokoOptions}
          />
          <TextField
            label="Tanggal Awal"
            type="date"
            value={from}
            onChange={(e) => setFrom(e.target.value)}
            InputLabelProps={{ shrink: true }}
          />
          <TextField
            label="Tanggal Akhir"
            type="date"
            value={to}
            onChange={(e) => setTo(e.target.value)}
            InputLabelProps={{ shrink: true }}
          />
          <Button type="submit" startIcon={<Search size={18} />} sx={{ flexShrink: 0 }}>
            Filter
          </Button>
        </Stack>
      </Card>

      <Card disableContentPadding>
        <DataTable
          columns={columns}
          rows={pengeluarans.data}
          getRowId={(p) => p.id}
          emptyMessage="Belum ada data pengeluaran."
        />
      </Card>

      <Box sx={{ px: 1, mt: 1 }}>
        <Stack
          direction="row"
          justifyContent="space-between"
          alignItems="center"
          flexWrap="wrap"
          spacing={1}
        >
          <Typography variant="subtitle2">
            Grand Total: {formatRupiah(total)}
          </Typography>
          <Pagination paginator={pengeluarans} />
        </Stack>
      </Box>

      <Dialog
        open={open}
        onClose={() => setOpen(false)}
        title={editing ? 'Edit Pengeluaran' : 'Tambah Pengeluaran'}
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
            label="Nama Pengeluaran"
            value={form.data.name}
            onChange={(e) => form.setData('name', e.target.value)}
            error={!!form.errors.name}
            helperText={form.errors.name}
            autoFocus
          />
          <TextField
            label="Keterangan"
            value={form.data.description}
            onChange={(e) => form.setData('description', e.target.value)}
            error={!!form.errors.description}
            helperText={form.errors.description}
          />
          <Select
            label="Toko"
            value={form.data.toko_id}
            onChange={(e) => form.setData('toko_id', e.target.value)}
            placeholder="Pilih Toko"
            options={tokoOptions}
            error={!!form.errors.toko_id}
            helperText={form.errors.toko_id}
          />
          <TextField
            label="Nominal"
            type="number"
            inputProps={{ step: 'any', inputMode: 'decimal' }}
            value={form.data.amount}
            onChange={(e) => form.setData('amount', e.target.value)}
            error={!!form.errors.amount}
            helperText={form.errors.amount}
          />
          <TextField
            label="Tanggal"
            type="date"
            value={form.data.date}
            onChange={(e) => form.setData('date', e.target.value)}
            error={!!form.errors.date}
            helperText={form.errors.date}
            InputLabelProps={{ shrink: true }}
          />
        </Stack>
      </Dialog>

      <ConfirmDialog
        open={!!toDelete}
        title="Hapus Pengeluaran"
        message={`Apakah anda yakin menghapus pengeluaran "${toDelete?.name}"?`}
        onClose={() => setToDelete(null)}
        onConfirm={() => {
          if (toDelete)
            router.get(
              `/pengeluaran/delete/${toDelete.id}`,
              {},
              { preserveScroll: true, onFinish: () => setToDelete(null) },
            );
        }}
      />
    </>
  );
}
