import { useState, type FormEvent } from 'react';
import { Head, useForm, router } from '@inertiajs/react';
import {
  Box, Button, Card, ConfirmDialog, DataTable, Dialog, IconButton, PageHeader,
  Pagination, Select, Stack, StatCard, TextField, Typography, type Column,
} from '@/ui';
import { Plus, Pencil, Trash2, Search } from '@/icons';
import { formatRupiah, formatDate } from '@/lib/format';
import type { Paginator } from '@/types';

interface Toko {
  id: number;
  name: string;
  vendor: string | null;
}

interface IncomeRow extends Record<string, unknown> {
  id: number;
  online_market_id: number | null;
  amount: number;
  date: string;
  shop: { id: number; name: string; vendor: string | null } | null;
}

interface Filters {
  online_market_id: string | number;
  from: string;
  to: string;
}

interface Props {
  incomes: Paginator<IncomeRow>;
  tokos: Toko[];
  totalIncome: number;
  filters: Filters;
}

export default function Index({ incomes, tokos, totalIncome, filters }: Props) {
  const [open, setOpen] = useState(false);
  const [editing, setEditing] = useState<IncomeRow | null>(null);
  const [toDelete, setToDelete] = useState<IncomeRow | null>(null);

  // Filter state (dikirim via GET, dipertahankan oleh Pagination)
  const [marketId, setMarketId] = useState<string | number>(filters.online_market_id ?? '');
  const [from, setFrom] = useState(filters.from ?? '');
  const [to, setTo] = useState(filters.to ?? '');

  const form = useForm({ online_market_id: '' as string | number, amount: '' as string | number, date: '' });

  const openCreate = () => {
    setEditing(null);
    form.reset();
    form.clearErrors();
    setOpen(true);
  };

  const openEdit = (row: IncomeRow) => {
    setEditing(row);
    form.clearErrors();
    form.setData({
      online_market_id: row.online_market_id ?? '',
      amount: row.amount,
      date: row.date,
    });
    setOpen(true);
  };

  const submit = (e: FormEvent) => {
    e.preventDefault();
    const opts = { onSuccess: () => setOpen(false), preserveScroll: true };
    if (editing) form.post(`/online-incomes/update/${editing.id}`, opts);
    else form.post('/online-incomes', opts);
  };

  const applyFilter = (e: FormEvent) => {
    e.preventDefault();
    router.get(
      '/online-incomes',
      { online_market_id: marketId, from, to },
      { preserveState: true, replace: true },
    );
  };

  const tokoOptions = tokos.map((t) => ({
    value: t.id,
    label: t.vendor ? `${t.name} - ${t.vendor}` : t.name,
  }));

  const columns: Column<IncomeRow>[] = [
    { key: 'no', label: '#', render: (_r, i) => (incomes.from ?? 1) + i },
    { key: 'toko', label: 'Toko', render: (r) => r.shop?.name ?? '-' },
    { key: 'vendor', label: 'Vendor', render: (r) => r.shop?.vendor ?? '-' },
    { key: 'amount', label: 'Nominal', align: 'right', render: (r) => formatRupiah(r.amount) },
    { key: 'date', label: 'Tanggal', render: (r) => formatDate(r.date) },
    {
      key: 'aksi',
      label: '',
      align: 'right',
      render: (r) => (
        <Stack direction="row" spacing={0.5} justifyContent="flex-end">
          <IconButton onClick={() => openEdit(r)}><Pencil size={16} /></IconButton>
          <IconButton color="error" onClick={() => setToDelete(r)}><Trash2 size={16} /></IconButton>
        </Stack>
      ),
    },
  ];

  return (
    <>
      <Head title="Pemasukan Market Online" />
      <PageHeader
        title="Pemasukan Market Online"
        actions={<Button startIcon={<Plus size={18} />} onClick={openCreate}>Tambah Pemasukan</Button>}
      />

      <Card sx={{ mb: 2 }}>
        <Stack
          component="form"
          onSubmit={applyFilter}
          direction={{ xs: 'column', md: 'row' }}
          spacing={2}
          alignItems={{ xs: 'stretch', md: 'flex-end' }}
        >
          <Select
            label="Toko"
            placeholder="Pilih Toko"
            value={marketId}
            onChange={(e) => setMarketId(e.target.value)}
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
          <Button type="submit" startIcon={<Search size={18} />} sx={{ flexShrink: 0 }}>Filter</Button>
        </Stack>
      </Card>

      <Box sx={{ mb: 2 }}>
        <StatCard
          label="Grand Total Pemasukan"
          value={formatRupiah(totalIncome)}
          color="success"
        />
      </Box>

      <Card disableContentPadding>
        <DataTable
          columns={columns}
          rows={incomes.data}
          getRowId={(r) => r.id}
          emptyMessage="Belum ada data pemasukan online."
        />
      </Card>
      <Box sx={{ px: 1 }}><Pagination paginator={incomes} /></Box>

      <Dialog
        open={open}
        onClose={() => setOpen(false)}
        title={editing ? 'Edit Pemasukan Online' : 'Tambah Pemasukan'}
        actions={
          <>
            <Button variant="text" color="secondary" onClick={() => setOpen(false)}>Batal</Button>
            <Button onClick={submit} disabled={form.processing}>Simpan</Button>
          </>
        }
      >
        <Stack component="form" spacing={2} onSubmit={submit} sx={{ pt: 1 }}>
          <Select
            label="Toko"
            placeholder="Pilih Toko"
            value={form.data.online_market_id}
            onChange={(e) => form.setData('online_market_id', e.target.value)}
            options={tokoOptions}
            error={!!form.errors.online_market_id}
            helperText={form.errors.online_market_id}
          />
          <TextField
            label="Nominal"
            type="number"
            inputProps={{ step: 'any', inputMode: 'decimal' }}
            placeholder="1000000"
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
            InputLabelProps={{ shrink: true }}
            error={!!form.errors.date}
            helperText={form.errors.date}
          />
        </Stack>
      </Dialog>

      <ConfirmDialog
        open={!!toDelete}
        title="Hapus Pemasukan"
        message={`Hapus pemasukan ${toDelete?.shop?.name ?? ''} sebesar ${formatRupiah(toDelete?.amount ?? 0)}?`}
        onClose={() => setToDelete(null)}
        onConfirm={() => {
          if (toDelete) {
            router.get(`/online-incomes/delete/${toDelete.id}`, {}, {
              preserveScroll: true,
              onFinish: () => setToDelete(null),
            });
          }
        }}
      />

      {incomes.data.length === 0 && (
        <Typography variant="caption" color="text.secondary" sx={{ display: 'block', mt: 1 }}>
          Tidak ada pemasukan pada periode ini.
        </Typography>
      )}
    </>
  );
}
