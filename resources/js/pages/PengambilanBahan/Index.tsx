import { useState } from 'react';
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
  StatCard,
  Stack,
  TextField,
  Typography,
  type Column,
} from '@/ui';
import { Plus, Trash2, Eye, Save, Search, Download, FileSpreadsheet } from '@/icons';
import { formatRupiah, formatDate } from '@/lib/format';
import type { Paginator } from '@/types';

interface TokoOption {
  id: number;
  name: string;
}

interface ProductOption {
  id: number;
  name: string;
  price_agent: number;
  price_grosir_meter: number;
  per_roll_cm: number;
}

interface BahanItem extends Record<string, unknown> {
  id: number;
  product_id: number;
  product_type: string;
  price: number;
  quantity: number;
  subtotal: number;
  product: { id: number; name: string } | null;
}

interface BahanRow extends Record<string, unknown> {
  id: number;
  toko: { id: number; name: string } | null;
  total: number;
  laba: number;
  date: string;
  items: BahanItem[];
}

interface Props {
  datas: Paginator<BahanRow>;
  tokos: TokoOption[];
  products: ProductOption[];
  total: number;
  labaTotal: number;
  filters: { from: string; to: string; toko_id: string };
}

interface ItemInput {
  product_id: number | '';
  product_type: 'roll' | 'meter';
  jumlah: string;
  harga: string;
}

const emptyItem = (): ItemInput => ({ product_id: '', product_type: 'roll', jumlah: '', harga: '' });

export default function Index({ datas, tokos, products, total, labaTotal, filters }: Props) {
  // Filter (GET) — dikendalikan lokal, disubmit via router.get.
  const [filterFrom, setFilterFrom] = useState(filters.from ?? '');
  const [filterTo, setFilterTo] = useState(filters.to ?? '');
  const [filterToko, setFilterToko] = useState<string>(filters.toko_id ?? '');

  const applyFilter = () => {
    router.get(
      '/pengambilan-bahan',
      { from: filterFrom, to: filterTo, toko_id: filterToko },
      { preserveScroll: true, preserveState: true },
    );
  };

  // Modal tambah + form.
  const [open, setOpen] = useState(false);
  const [search, setSearch] = useState('');
  const form = useForm<{ toko_id: string; date: string; items: ItemInput[] }>({
    toko_id: '',
    date: '',
    items: [emptyItem()],
  });
  const fieldErrors = form.errors as Record<string, string | undefined>;

  // Detail (Rincian) + hapus.
  const [detail, setDetail] = useState<BahanRow | null>(null);
  const [toDelete, setToDelete] = useState<BahanRow | null>(null);

  const openCreate = () => {
    form.reset();
    form.clearErrors();
    setSearch('');
    setOpen(true);
  };

  const updateItem = (index: number, patch: Partial<ItemInput>) => {
    form.setData(
      'items',
      form.data.items.map((it, i) => (i === index ? { ...it, ...patch } : it)),
    );
  };

  const onProductChange = (index: number, value: string | number) => {
    const productId = value === '' ? '' : Number(value);
    const product = products.find((p) => p.id === productId);
    const type = form.data.items[index].product_type;
    const harga = product ? (type === 'meter' ? product.price_grosir_meter : product.price_agent) : 0;
    updateItem(index, { product_id: productId, harga: String(harga) });
  };

  const onTypeChange = (index: number, type: 'roll' | 'meter') => {
    const it = form.data.items[index];
    const product = products.find((p) => p.id === it.product_id);
    const harga = product
      ? type === 'meter'
        ? product.price_grosir_meter
        : product.price_agent
      : Number(it.harga) || 0;
    updateItem(index, { product_type: type, harga: String(harga) });
  };

  const addItem = () => form.setData('items', [...form.data.items, emptyItem()]);
  const removeItem = (index: number) =>
    form.setData('items', form.data.items.filter((_, i) => i !== index));
  const clearItems = () => form.setData('items', []);

  const rowSubtotal = (it: ItemInput) =>
    it.product_id === '' ? 0 : (Number(it.jumlah) || 0) * (Number(it.harga) || 0);

  const grandTotal = form.data.items.reduce((sum, it) => sum + rowSubtotal(it), 0);

  const visibleIndexes = form.data.items
    .map((_, i) => i)
    .filter((i) => {
      if (search.trim() === '') return true;
      const it = form.data.items[i];
      const product = products.find((p) => p.id === it.product_id);
      return !product || product.name.toLowerCase().includes(search.trim().toLowerCase());
    });

  const submit = () => {
    form.transform((data) => ({
      toko_id: data.toko_id,
      date: data.date,
      items: data.items
        .filter((it) => it.product_id !== '')
        .map((it) => ({
          product_id: it.product_id,
          product_type: it.product_type,
          jumlah: it.jumlah,
          harga: it.harga,
        })),
    }));
    form.post('/pengambilan-bahan', {
      preserveScroll: true,
      onSuccess: () => {
        setOpen(false);
        form.reset();
      },
    });
  };

  const columns: Column<BahanRow>[] = [
    { key: 'no', label: '#', render: (_r, i) => (datas.from ?? 1) + i },
    { key: 'toko', label: 'Toko', render: (r) => r.toko?.name ?? '-' },
    { key: 'total', label: 'Total', align: 'right', render: (r) => formatRupiah(r.total) },
    { key: 'laba', label: 'Laba', align: 'right', render: (r) => formatRupiah(r.laba) },
    { key: 'date', label: 'Tanggal Pengambilan', render: (r) => formatDate(r.date) },
    {
      key: 'aksi',
      label: '',
      align: 'right',
      render: (r) => (
        <Stack direction="row" spacing={0.5} justifyContent="flex-end">
          <IconButton onClick={() => setDetail(r)} aria-label="Rincian">
            <Eye size={16} />
          </IconButton>
          <IconButton color="error" onClick={() => setToDelete(r)} aria-label="Hapus">
            <Trash2 size={16} />
          </IconButton>
        </Stack>
      ),
    },
  ];

  const detailColumns: Column<BahanItem>[] = [
    { key: 'no', label: 'No.', render: (_r, i) => i + 1 },
    { key: 'product', label: 'Nama Produk', render: (r) => r.product?.name ?? '-' },
    { key: 'price', label: 'Harga', align: 'right', render: (r) => formatRupiah(r.price) },
    {
      key: 'quantity',
      label: 'Quantity',
      align: 'right',
      render: (r) => `${r.quantity} ${r.product_type}`,
    },
    { key: 'subtotal', label: 'Subtotal', align: 'right', render: (r) => formatRupiah(r.subtotal) },
  ];

  return (
    <>
      <Head title="Pengambilan Bahan" />
      <PageHeader
        title="Pengambilan Bahan"
        actions={
          <Button startIcon={<Plus size={18} />} onClick={openCreate}>
            Tambah Pengambilan Bahan
          </Button>
        }
      />

      <Box
        sx={{
          display: 'grid',
          gap: 2,
          gridTemplateColumns: { xs: '1fr', sm: '1fr 1fr' },
          mb: 3,
        }}
      >
        <StatCard label="Grand Total" value={formatRupiah(total)} color="primary" icon={<Download size={22} />} />
        <StatCard label="Total Laba" value={formatRupiah(labaTotal)} color="success" icon={<FileSpreadsheet size={22} />} />
      </Box>

      <Card sx={{ mb: 3 }}>
        <Stack
          direction={{ xs: 'column', md: 'row' }}
          spacing={2}
          alignItems={{ md: 'flex-end' }}
        >
          <Box sx={{ flex: 1, width: '100%' }}>
            <Select
              label="Toko"
              placeholder="Pilih Toko"
              value={filterToko}
              onChange={(e) => setFilterToko(e.target.value)}
              options={tokos.map((t) => ({ value: t.id, label: t.name }))}
            />
          </Box>
          <Box sx={{ flex: 1, width: '100%' }}>
            <TextField
              type="date"
              label="Tanggal Awal"
              value={filterFrom}
              onChange={(e) => setFilterFrom(e.target.value)}
              InputLabelProps={{ shrink: true }}
            />
          </Box>
          <Box sx={{ flex: 1, width: '100%' }}>
            <TextField
              type="date"
              label="Tanggal Akhir"
              value={filterTo}
              onChange={(e) => setFilterTo(e.target.value)}
              InputLabelProps={{ shrink: true }}
            />
          </Box>
          <Button startIcon={<Search size={18} />} onClick={applyFilter}>
            Filter
          </Button>
        </Stack>
      </Card>

      <Card disableContentPadding>
        <DataTable
          columns={columns}
          rows={datas.data}
          getRowId={(r) => r.id}
          emptyMessage="Belum ada data pengambilan bahan."
        />
      </Card>
      <Box sx={{ px: 1 }}>
        <Pagination paginator={datas} />
      </Box>

      {/* Modal Tambah */}
      <Dialog
        open={open}
        onClose={() => setOpen(false)}
        title="Tambah Pengambilan Bahan"
        maxWidth="lg"
        actions={
          <>
            <Button variant="text" color="secondary" onClick={() => setOpen(false)}>
              Batal
            </Button>
            <Button startIcon={<Save size={18} />} onClick={submit} disabled={form.processing}>
              Simpan
            </Button>
          </>
        }
      >
        <Stack spacing={2} sx={{ pt: 1 }}>
          <Stack direction={{ xs: 'column', md: 'row' }} spacing={2}>
            <Box sx={{ flex: 1 }}>
              <Select
                label="Pilih Toko"
                placeholder="-- Pilih Toko --"
                value={form.data.toko_id}
                onChange={(e) => form.setData('toko_id', e.target.value)}
                options={tokos.map((t) => ({ value: t.id, label: t.name }))}
                error={!!form.errors.toko_id}
                helperText={form.errors.toko_id}
              />
            </Box>
            <Box sx={{ flex: 1 }}>
              <TextField
                type="date"
                label="Tanggal Pengambilan"
                value={form.data.date}
                onChange={(e) => form.setData('date', e.target.value)}
                InputLabelProps={{ shrink: true }}
                error={!!form.errors.date}
                helperText={form.errors.date}
              />
            </Box>
            <Box sx={{ flex: 1 }}>
              <TextField
                label="Cari Produk"
                placeholder="Cari produk..."
                value={search}
                onChange={(e) => setSearch(e.target.value)}
              />
            </Box>
          </Stack>

          <Typography variant="h6">Total: {formatRupiah(grandTotal)}</Typography>

          <Box sx={{ maxHeight: '45vh', overflowY: 'auto', pr: 1 }}>
            <Stack spacing={1.5}>
              {visibleIndexes.length === 0 ? (
                <Typography variant="body2" color="text.secondary">
                  Tidak ada produk. Tambahkan produk di bawah.
                </Typography>
              ) : (
                visibleIndexes.map((i) => {
                  const it = form.data.items[i];
                  return (
                    <Stack
                      key={i}
                      direction={{ xs: 'column', md: 'row' }}
                      spacing={1}
                      alignItems={{ md: 'flex-start' }}
                    >
                      <Box sx={{ flex: 2, minWidth: 180 }}>
                        <Select
                          label="Produk"
                          placeholder="-- Pilih Produk --"
                          value={it.product_id}
                          onChange={(e) => onProductChange(i, e.target.value)}
                          options={products.map((p) => ({ value: p.id, label: p.name }))}
                          error={!!fieldErrors[`items.${i}.product_id`]}
                          helperText={fieldErrors[`items.${i}.product_id`]}
                        />
                      </Box>
                      <Box sx={{ minWidth: 120 }}>
                        <Select
                          label="Jenis"
                          value={it.product_type}
                          onChange={(e) => onTypeChange(i, e.target.value as 'roll' | 'meter')}
                          options={[
                            { value: 'roll', label: 'Roll' },
                            { value: 'meter', label: 'Meter' },
                          ]}
                        />
                      </Box>
                      <Box sx={{ minWidth: 110 }}>
                        <TextField
                          type="number"
                          label="Jumlah"
                          placeholder="Jumlah"
                          value={it.jumlah}
                          onChange={(e) => updateItem(i, { jumlah: e.target.value })}
                          inputProps={{ step: 'any', inputMode: 'decimal' }}
                          error={!!fieldErrors[`items.${i}.jumlah`]}
                          helperText={fieldErrors[`items.${i}.jumlah`]}
                        />
                      </Box>
                      <Box sx={{ minWidth: 130 }}>
                        <TextField
                          type="number"
                          label="Harga"
                          placeholder="Harga"
                          value={it.harga}
                          onChange={(e) => updateItem(i, { harga: e.target.value })}
                          inputProps={{ step: 'any', inputMode: 'decimal' }}
                          error={!!fieldErrors[`items.${i}.harga`]}
                          helperText={fieldErrors[`items.${i}.harga`]}
                        />
                      </Box>
                      <Box sx={{ minWidth: 130, pt: { md: 1 } }}>
                        <Typography variant="caption" color="text.secondary">
                          Subtotal
                        </Typography>
                        <Typography variant="body2">{formatRupiah(rowSubtotal(it))}</Typography>
                      </Box>
                      <IconButton color="error" onClick={() => removeItem(i)} aria-label="Hapus item">
                        <Trash2 size={16} />
                      </IconButton>
                    </Stack>
                  );
                })
              )}
            </Stack>
          </Box>

          <Stack direction="row" spacing={1}>
            <Button variant="outlined" startIcon={<Plus size={18} />} onClick={addItem}>
              Tambah Produk
            </Button>
            <Button variant="outlined" color="error" startIcon={<Trash2 size={18} />} onClick={clearItems}>
              Hapus Semua Produk
            </Button>
          </Stack>
        </Stack>
      </Dialog>

      {/* Modal Rincian */}
      <Dialog
        open={!!detail}
        onClose={() => setDetail(null)}
        title="Rincian Pengambilan Bahan"
        maxWidth="md"
        actions={
          <Button variant="text" color="secondary" onClick={() => setDetail(null)}>
            Tutup
          </Button>
        }
      >
        {detail && (
          <Stack spacing={2} sx={{ pt: 1 }}>
            <DataTable
              columns={detailColumns}
              rows={detail.items}
              getRowId={(r) => r.id}
              emptyMessage="Tidak ada item."
            />
            <Stack direction="row" justifyContent="space-between">
              <Typography variant="body2" color="text.secondary">
                Tanggal: {formatDate(detail.date)}
              </Typography>
              <Typography variant="subtitle1">Total: {formatRupiah(detail.total)}</Typography>
            </Stack>
          </Stack>
        )}
      </Dialog>

      {/* Konfirmasi Hapus */}
      <ConfirmDialog
        open={!!toDelete}
        title="Hapus Pengambilan Bahan"
        message="Apakah Anda yakin menghapus data ini? Stok akan dikembalikan untuk produk terkait."
        onClose={() => setToDelete(null)}
        onConfirm={() => {
          if (toDelete) {
            router.get(
              `/pengambilan-bahan/delete/${toDelete.id}`,
              {},
              { preserveScroll: true, onFinish: () => setToDelete(null) },
            );
          }
        }}
      />
    </>
  );
}
