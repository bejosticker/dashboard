import { useState, type FormEvent } from 'react';
import { Head, useForm, router } from '@inertiajs/react';
import {
  Box, Button, Card, ConfirmDialog, DataTable, Dialog, Divider, IconButton, PageHeader,
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

interface AdRow extends Record<string, unknown> {
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
  ads: Paginator<AdRow>;
  tokos: Toko[];
  totalAd: number;
  filters: Filters;
}

interface AdItem {
  online_market_id: string | number;
  amount: string | number;
}

export default function Index({ ads, tokos, totalAd, filters }: Props) {
  const [createOpen, setCreateOpen] = useState(false);
  const [editing, setEditing] = useState<AdRow | null>(null);
  const [toDelete, setToDelete] = useState<AdRow | null>(null);

  // Filter state (dikirim via GET, dipertahankan oleh Pagination)
  const [marketId, setMarketId] = useState<string | number>(filters.online_market_id ?? '');
  const [from, setFrom] = useState(filters.from ?? '');
  const [to, setTo] = useState(filters.to ?? '');

  // Form tambah: satu tanggal + banyak baris toko/nominal (port IklanForm Livewire).
  const createForm = useForm<{ date: string; items: AdItem[] }>({
    date: '',
    items: [{ online_market_id: '', amount: '' }],
  });

  // Form edit: satu baris (route /online-ads/update/{id}).
  const editForm = useForm<{ date: string; online_market_id: string | number; amount: string | number }>({
    date: '',
    online_market_id: '',
    amount: '',
  });

  const createItemErrors = createForm.errors as Record<string, string>;

  const tokoOptions = tokos.map((t) => ({
    value: t.id,
    label: t.vendor ? `${t.name} - ${t.vendor}` : t.name,
  }));

  const createTotal = createForm.data.items.reduce(
    (sum, it) => sum + (Number(it.amount) || 0),
    0,
  );

  const openCreate = () => {
    createForm.clearErrors();
    createForm.setData({ date: '', items: [{ online_market_id: '', amount: '' }] });
    setCreateOpen(true);
  };

  const addItem = () => {
    createForm.setData('items', [...createForm.data.items, { online_market_id: '', amount: '' }]);
  };

  const removeItem = (index: number) => {
    if (createForm.data.items.length === 1) return;
    createForm.setData(
      'items',
      createForm.data.items.filter((_, i) => i !== index),
    );
  };

  const updateItem = (index: number, key: keyof AdItem, value: string | number) => {
    createForm.setData(
      'items',
      createForm.data.items.map((it, i) => (i === index ? { ...it, [key]: value } : it)),
    );
  };

  const submitCreate = (e: FormEvent) => {
    e.preventDefault();
    createForm.post('/online-ads', {
      preserveScroll: true,
      onSuccess: () => setCreateOpen(false),
    });
  };

  const openEdit = (row: AdRow) => {
    setEditing(row);
    editForm.clearErrors();
    editForm.setData({
      date: row.date,
      online_market_id: row.online_market_id ?? '',
      amount: row.amount,
    });
  };

  const submitEdit = (e: FormEvent) => {
    e.preventDefault();
    if (!editing) return;
    editForm.post(`/online-ads/update/${editing.id}`, {
      preserveScroll: true,
      onSuccess: () => setEditing(null),
    });
  };

  const applyFilter = (e: FormEvent) => {
    e.preventDefault();
    router.get(
      '/online-ads',
      { online_market_id: marketId, from, to },
      { preserveState: true, replace: true },
    );
  };

  const columns: Column<AdRow>[] = [
    { key: 'no', label: '#', render: (_r, i) => (ads.from ?? 1) + i },
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
      <Head title="Iklan Market Online" />
      <PageHeader
        title="Iklan Market Online"
        actions={<Button startIcon={<Plus size={18} />} onClick={openCreate}>Tambah Iklan</Button>}
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
        <StatCard label="Grand Total Iklan" value={formatRupiah(totalAd)} color="warning" />
      </Box>

      <Card disableContentPadding>
        <DataTable
          columns={columns}
          rows={ads.data}
          getRowId={(r) => r.id}
          emptyMessage="Belum ada data iklan online."
        />
      </Card>
      <Box sx={{ px: 1 }}><Pagination paginator={ads} /></Box>

      {/* Modal Tambah — multi-baris */}
      <Dialog
        open={createOpen}
        onClose={() => setCreateOpen(false)}
        title="Tambah Iklan"
        maxWidth="md"
        actions={
          <>
            <Button variant="text" color="secondary" onClick={() => setCreateOpen(false)}>Batal</Button>
            <Button onClick={submitCreate} disabled={createForm.processing}>Simpan</Button>
          </>
        }
      >
        <Stack component="form" spacing={2} onSubmit={submitCreate} sx={{ pt: 1 }}>
          <Stack
            direction={{ xs: 'column', sm: 'row' }}
            spacing={2}
            alignItems={{ xs: 'stretch', sm: 'flex-end' }}
            justifyContent="space-between"
          >
            <TextField
              label="Tanggal Iklan"
              type="date"
              value={createForm.data.date}
              onChange={(e) => createForm.setData('date', e.target.value)}
              InputLabelProps={{ shrink: true }}
              error={!!createForm.errors.date}
              helperText={createForm.errors.date}
              sx={{ maxWidth: { sm: 220 } }}
            />
            <Typography variant="h6" sx={{ textAlign: { sm: 'right' } }}>
              Total: {formatRupiah(createTotal)}
            </Typography>
          </Stack>

          <Divider />

          {createForm.errors.items && (
            <Typography variant="caption" color="error">{createForm.errors.items}</Typography>
          )}

          <Stack spacing={1.5}>
            {createForm.data.items.map((item, i) => (
              <Stack key={i} direction="row" spacing={1} alignItems="flex-start">
                <Box sx={{ flex: 2 }}>
                  <Select
                    label="Toko"
                    placeholder="-- Pilih Toko --"
                    value={item.online_market_id}
                    onChange={(e) => updateItem(i, 'online_market_id', e.target.value)}
                    options={tokoOptions}
                    error={!!createItemErrors[`items.${i}.online_market_id`]}
                    helperText={createItemErrors[`items.${i}.online_market_id`]}
                  />
                </Box>
                <Box sx={{ flex: 1.5 }}>
                  <TextField
                    label="Nominal Iklan"
                    type="number"
                    inputProps={{ step: 'any', inputMode: 'decimal' }}
                    placeholder="1000000"
                    value={item.amount}
                    onChange={(e) => updateItem(i, 'amount', e.target.value)}
                    error={!!createItemErrors[`items.${i}.amount`]}
                    helperText={createItemErrors[`items.${i}.amount`]}
                  />
                </Box>
                <IconButton
                  color="error"
                  onClick={() => removeItem(i)}
                  disabled={createForm.data.items.length === 1}
                  sx={{ mt: 0.5 }}
                >
                  <Trash2 size={16} />
                </IconButton>
              </Stack>
            ))}
          </Stack>

          <Box>
            <Button variant="outlined" startIcon={<Plus size={18} />} onClick={addItem}>Tambah Toko</Button>
          </Box>
        </Stack>
      </Dialog>

      {/* Modal Edit — satu baris */}
      <Dialog
        open={!!editing}
        onClose={() => setEditing(null)}
        title="Edit Iklan Online"
        actions={
          <>
            <Button variant="text" color="secondary" onClick={() => setEditing(null)}>Batal</Button>
            <Button onClick={submitEdit} disabled={editForm.processing}>Simpan</Button>
          </>
        }
      >
        <Stack component="form" spacing={2} onSubmit={submitEdit} sx={{ pt: 1 }}>
          <Select
            label="Toko"
            placeholder="-- Pilih Toko --"
            value={editForm.data.online_market_id}
            onChange={(e) => editForm.setData('online_market_id', e.target.value)}
            options={tokoOptions}
            error={!!editForm.errors.online_market_id}
            helperText={editForm.errors.online_market_id}
          />
          <TextField
            label="Nominal"
            type="number"
            inputProps={{ step: 'any', inputMode: 'decimal' }}
            placeholder="1000000"
            value={editForm.data.amount}
            onChange={(e) => editForm.setData('amount', e.target.value)}
            error={!!editForm.errors.amount}
            helperText={editForm.errors.amount}
          />
          <TextField
            label="Tanggal"
            type="date"
            value={editForm.data.date}
            onChange={(e) => editForm.setData('date', e.target.value)}
            InputLabelProps={{ shrink: true }}
            error={!!editForm.errors.date}
            helperText={editForm.errors.date}
          />
        </Stack>
      </Dialog>

      <ConfirmDialog
        open={!!toDelete}
        title="Hapus Iklan"
        message={`Hapus iklan ${toDelete?.shop?.name ?? ''} sebesar ${formatRupiah(toDelete?.amount ?? 0)}?`}
        onClose={() => setToDelete(null)}
        onConfirm={() => {
          if (toDelete) {
            router.get(`/online-ads/delete/${toDelete.id}`, {}, {
              preserveScroll: true,
              onFinish: () => setToDelete(null),
            });
          }
        }}
      />
    </>
  );
}
