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
  StatCard,
  TextField,
  type Column,
} from '@/ui';
import { Plus, Pencil, Trash2, Search, Banknote } from '@/icons';
import { formatRupiah, formatDate } from '@/lib/format';
import type { Paginator } from '@/types';

interface Toko {
  id: number;
  name: string;
}

interface Income extends Record<string, unknown> {
  id: number;
  toko_id: number | null;
  amount: number;
  date: string;
  name: string | null;
  toko: Toko | null;
}

interface Props {
  incomes: Paginator<Income>;
  tokos: Toko[];
  totalIncome: number;
  filters: { from?: string; to?: string; toko_id?: string | number };
}

export default function Index({ incomes, tokos, totalIncome, filters }: Props) {
  const [open, setOpen] = useState(false);
  const [editing, setEditing] = useState<Income | null>(null);
  const [toDelete, setToDelete] = useState<Income | null>(null);

  const [from, setFrom] = useState(filters.from ?? '');
  const [to, setTo] = useState(filters.to ?? '');
  const [tokoFilter, setTokoFilter] = useState(String(filters.toko_id ?? ''));

  const form = useForm({ toko_id: '' as string | number, amount: '' as string | number, date: '' });

  const tokoOptions = tokos.map((t) => ({ value: t.id, label: t.name }));

  const openCreate = () => {
    setEditing(null);
    form.reset();
    form.clearErrors();
    setOpen(true);
  };

  const openEdit = (i: Income) => {
    setEditing(i);
    form.clearErrors();
    form.setData({ toko_id: i.toko_id ?? '', amount: i.amount, date: i.date });
    setOpen(true);
  };

  const submit = (e: FormEvent) => {
    e.preventDefault();
    const opts = { onSuccess: () => setOpen(false), preserveScroll: true };
    if (editing) form.post(`/toko-income/update/${editing.id}`, opts);
    else form.post('/toko-income', opts);
  };

  const applyFilter = (e: FormEvent) => {
    e.preventDefault();
    router.get(
      '/toko-income',
      { from, to, toko_id: tokoFilter },
      { preserveState: true, replace: true },
    );
  };

  const columns: Column<Income>[] = [
    { key: 'no', label: '#', width: 60, render: (_r, i) => (incomes.from ?? 1) + i },
    { key: 'toko', label: 'Toko', render: (r) => r.toko?.name ?? '-' },
    { key: 'amount', label: 'Nominal', align: 'right', render: (r) => formatRupiah(r.amount) },
    { key: 'date', label: 'Tanggal', render: (r) => formatDate(r.date) },
    {
      key: 'aksi',
      label: '',
      align: 'right',
      render: (r) => (
        <Stack direction="row" spacing={0.5} justifyContent="flex-end">
          <IconButton onClick={() => openEdit(r)}>
            <Pencil size={16} />
          </IconButton>
          <IconButton color="error" onClick={() => setToDelete(r)}>
            <Trash2 size={16} />
          </IconButton>
        </Stack>
      ),
    },
  ];

  return (
    <>
      <Head title="Pemasukan Toko" />
      <PageHeader
        title="Pemasukan Toko"
        actions={
          <Button startIcon={<Plus size={18} />} onClick={openCreate}>
            Tambah Pemasukan
          </Button>
        }
      />

      <Card sx={{ mb: 2 }}>
        <Stack
          component="form"
          direction={{ xs: 'column', md: 'row' }}
          spacing={2}
          alignItems={{ md: 'flex-end' }}
          onSubmit={applyFilter}
        >
          <Select
            label="Toko"
            placeholder="Semua Toko"
            value={tokoFilter}
            onChange={(e) => setTokoFilter(e.target.value)}
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

      <Box sx={{ mb: 2, maxWidth: 320 }}>
        <StatCard
          label="Total Pemasukan"
          value={formatRupiah(totalIncome)}
          color="success"
          icon={<Banknote size={22} />}
        />
      </Box>

      <Card disableContentPadding>
        <DataTable
          columns={columns}
          rows={incomes.data}
          getRowId={(r) => r.id}
          emptyMessage="Belum ada data pemasukan toko."
        />
      </Card>
      <Box sx={{ px: 1 }}>
        <Pagination paginator={incomes} />
      </Box>

      <Dialog
        open={open}
        onClose={() => setOpen(false)}
        title={editing ? 'Edit Pemasukan Toko' : 'Tambah Pemasukan Toko'}
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
          <Select
            label="Toko"
            placeholder="Pilih Toko"
            value={form.data.toko_id}
            onChange={(e) => form.setData('toko_id', e.target.value)}
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
        title="Hapus Pemasukan Toko"
        message={`Hapus pemasukan ${toDelete?.toko?.name ?? ''} senilai ${formatRupiah(toDelete?.amount ?? 0)}?`}
        onClose={() => setToDelete(null)}
        onConfirm={() => {
          if (toDelete)
            router.get(
              `/toko-income/delete/${toDelete.id}`,
              {},
              { preserveScroll: true, onFinish: () => setToDelete(null) },
            );
        }}
      />
    </>
  );
}
