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
  InputAdornment,
  PageHeader,
  Pagination,
  Select,
  Stack,
  StatCard,
  TextField,
  Typography,
  type Column,
} from '@/ui';
import { Plus, Trash2, Eye, Search } from '@/icons';
import { formatRupiah, formatDate } from '@/lib/format';
import type { Paginator } from '@/types';

interface SaleItemDetail extends Record<string, unknown> {
  id: number;
  product: string;
  price: number;
  price_type: string;
  qty_label: string;
  subtotal: number;
}

interface SaleRow extends Record<string, unknown> {
  id: number;
  customer: string;
  customer_phone: string;
  date: string;
  total: number;
  laba: number;
  payment_method: string;
  items_count: number;
  items: SaleItemDetail[];
}

interface CetakProductOption {
  id: number;
  name: string;
  price_grosir: number | string;
  price_umum: number | string;
  price_eceran_grosir: number | string;
  price_eceran_umum: number | string;
}

interface PaymentMethodOption {
  id: number;
  name: string;
}

interface CustomerOption {
  name: string | null;
  phone: string | null;
}

interface Props {
  sales: Paginator<SaleRow>;
  products: CetakProductOption[];
  paymentMethods: PaymentMethodOption[];
  customers: CustomerOption[];
  allLaba: number;
  allTotal: number;
  filters: { from: string | null; to: string | null };
}

interface ItemForm {
  product_id: string;
  price_type: string;
  quantity: string;
  price: number;
  subtotal: number;
}

// Jenis harga tersedia (urut mengikuti komponen Livewire lama)
const PRICE_TYPES: { value: string; label: string }[] = [
  { value: 'price_grosir', label: 'Harga Grosir' },
  { value: 'price_umum', label: 'Harga Umum' },
  { value: 'price_eceran_grosir', label: 'Harga Eceran Grosir' },
  { value: 'price_eceran_umum', label: 'Harga Eceran Umum' },
];

// Satuan otomatis mengikuti jenis harga: per lembar (eceran) atau per cm
const unitLabel = (priceType: string): string =>
  priceType === 'price_eceran_grosir' || priceType === 'price_eceran_umum' ? 'Lembar' : 'CM';

const emptyItem = (): ItemForm => ({ product_id: '', price_type: '', quantity: '', price: 0, subtotal: 0 });

export default function Index({ sales, products, paymentMethods, customers, allLaba, allTotal, filters }: Props) {
  const [open, setOpen] = useState(false);
  const [detail, setDetail] = useState<SaleRow | null>(null);
  const [toDelete, setToDelete] = useState<SaleRow | null>(null);
  const [from, setFrom] = useState(filters.from ?? '');
  const [to, setTo] = useState(filters.to ?? '');

  const form = useForm<{
    customer: string;
    customer_phone: string;
    date: string;
    discount: string;
    payment_method_id: string;
    items: ItemForm[];
  }>({
    customer: '',
    customer_phone: '',
    date: '',
    discount: '',
    payment_method_id: '',
    items: [],
  });

  const errors = form.errors as Record<string, string>;

  const priceOf = (productId: string, priceType: string): number => {
    const p = products.find((pr) => String(pr.id) === String(productId));
    if (!p || !priceType) return 0;
    return Number((p as unknown as Record<string, unknown>)[priceType] ?? 0);
  };

  const recalc = (it: ItemForm): ItemForm => ({
    ...it,
    subtotal: Math.ceil(Number(it.quantity || 0) * it.price),
  });

  const patchItem = (index: number, patch: Partial<ItemForm>) => {
    const items = form.data.items.map((it, i) => {
      if (i !== index) return it;
      const next = { ...it, ...patch };
      next.price = priceOf(next.product_id, next.price_type);
      return recalc(next);
    });
    form.setData('items', items);
  };

  const addItem = () => form.setData('items', [...form.data.items, emptyItem()]);
  const removeItem = (index: number) =>
    form.setData('items', form.data.items.filter((_, i) => i !== index));

  const onPhoneChange = (value: string) => {
    const match = customers.find((c) => c.phone === value);
    form.setData((data) => ({
      ...data,
      customer_phone: value,
      customer: match?.name ? match.name : data.customer,
    }));
  };

  const total = form.data.items.reduce((s, it) => s + it.subtotal, 0) - Number(form.data.discount || 0);

  const openCreate = () => {
    form.reset();
    form.clearErrors();
    setOpen(true);
  };

  const submit = (e: FormEvent) => {
    e.preventDefault();
    form.post('/cetak-sales', {
      preserveScroll: true,
      onSuccess: () => {
        setOpen(false);
        form.reset();
      },
    });
  };

  const applyFilter = () =>
    router.get('/cetak-sales', { from, to }, { preserveState: true, replace: true });

  const columns: Column<SaleRow>[] = [
    { key: 'customer', label: 'Nama Customer' },
    { key: 'customer_phone', label: 'No. WA' },
    { key: 'date', label: 'Tanggal', render: (r) => formatDate(r.date) },
    { key: 'total', label: 'Total Nominal', align: 'right', render: (r) => formatRupiah(r.total) },
    { key: 'laba', label: 'Laba', align: 'right', render: (r) => formatRupiah(r.laba) },
    { key: 'payment_method', label: 'Metode Pembayaran' },
    { key: 'items_count', label: 'Total Produk', align: 'right', render: (r) => `${r.items_count} Produk` },
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

  const detailColumns: Column<SaleItemDetail>[] = [
    { key: 'no', label: 'No.', render: (_r, i) => i + 1 },
    { key: 'product', label: 'Nama Produk' },
    { key: 'price', label: 'Harga', align: 'right', render: (r) => formatRupiah(r.price) },
    { key: 'price_type', label: 'Jenis Harga' },
    { key: 'qty_label', label: 'Quantity', align: 'right' },
    { key: 'subtotal', label: 'Subtotal', align: 'right', render: (r) => formatRupiah(r.subtotal) },
  ];

  return (
    <>
      <Head title="Penjualan Cetak" />
      <PageHeader
        title="Penjualan Cetak"
        actions={
          <Button startIcon={<Plus size={18} />} onClick={openCreate}>
            Tambah Penjualan
          </Button>
        }
      />

      <Card sx={{ mb: 3 }}>
        <Stack direction={{ xs: 'column', sm: 'row' }} spacing={2} alignItems={{ sm: 'flex-end' }}>
          <TextField
            type="date"
            label="Tanggal Awal"
            value={from}
            onChange={(e) => setFrom(e.target.value)}
            InputLabelProps={{ shrink: true }}
          />
          <TextField
            type="date"
            label="Tanggal Akhir"
            value={to}
            onChange={(e) => setTo(e.target.value)}
            InputLabelProps={{ shrink: true }}
          />
          <Button startIcon={<Search size={18} />} onClick={applyFilter}>
            Filter
          </Button>
        </Stack>
      </Card>

      <Box
        sx={{
          display: 'grid',
          gap: 2,
          gridTemplateColumns: { xs: '1fr', sm: '1fr 1fr' },
          mb: 3,
        }}
      >
        <StatCard label="Grand Total" value={formatRupiah(allTotal)} color="primary" />
        <StatCard label="Total Laba" value={formatRupiah(allLaba)} color="success" />
      </Box>

      <Card disableContentPadding>
        <DataTable
          columns={columns}
          rows={sales.data}
          getRowId={(r) => r.id}
          emptyMessage="Belum ada data penjualan."
        />
      </Card>
      <Box sx={{ px: 1 }}>
        <Pagination paginator={sales} />
      </Box>

      {/* Modal Rincian */}
      <Dialog
        open={!!detail}
        onClose={() => setDetail(null)}
        title="Rincian Penjualan"
        maxWidth="lg"
        actions={
          <Button variant="text" color="secondary" onClick={() => setDetail(null)}>
            Tutup
          </Button>
        }
      >
        {detail && (
          <Stack spacing={2}>
            <DataTable
              columns={detailColumns}
              rows={detail.items}
              getRowId={(r) => r.id}
              emptyMessage="Tidak ada item."
            />
            <Stack spacing={0.5}>
              <Typography variant="body2">
                <strong>Nama Customer:</strong> {detail.customer}
              </Typography>
              <Typography variant="body2">
                <strong>No. WA:</strong> {detail.customer_phone}
              </Typography>
              <Typography variant="subtitle1">
                <strong>Total:</strong> {formatRupiah(detail.total)}
              </Typography>
            </Stack>
          </Stack>
        )}
      </Dialog>

      {/* Modal Tambah */}
      <Dialog
        open={open}
        onClose={() => setOpen(false)}
        title="Tambah Penjualan Cetak"
        maxWidth="lg"
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
          <Stack direction={{ xs: 'column', md: 'row' }} spacing={2}>
            <TextField
              type="date"
              label="Tanggal Penjualan"
              value={form.data.date}
              onChange={(e) => form.setData('date', e.target.value)}
              InputLabelProps={{ shrink: true }}
              error={!!errors.date}
              helperText={errors.date}
            />
            <TextField
              label="Nomor Telepon (WA)"
              placeholder="08xxxxxxxxx"
              value={form.data.customer_phone}
              onChange={(e) => onPhoneChange(e.target.value)}
              error={!!errors.customer_phone}
              helperText={errors.customer_phone}
            />
            <TextField
              label="Nama Customer (opsional)"
              value={form.data.customer}
              onChange={(e) => form.setData('customer', e.target.value)}
              error={!!errors.customer}
              helperText={errors.customer}
            />
          </Stack>

          {form.data.date !== '' && (
            <>
              <Typography variant="subtitle2">Daftar Produk</Typography>
              {form.data.items.map((item, i) => (
                <Stack
                  key={i}
                  direction={{ xs: 'column', md: 'row' }}
                  spacing={1}
                  alignItems={{ md: 'flex-start' }}
                >
                  <Box sx={{ pt: { md: 1 }, minWidth: 24, textAlign: 'center' }}>
                    <Typography variant="body2">{i + 1}.</Typography>
                  </Box>
                  <Select
                    label="Produk"
                    placeholder="-- Pilih Produk --"
                    value={item.product_id}
                    onChange={(e) => patchItem(i, { product_id: e.target.value })}
                    options={products.map((p) => ({ value: p.id, label: p.name }))}
                    error={!!errors[`items.${i}.product_id`]}
                    helperText={errors[`items.${i}.product_id`]}
                  />
                  <Select
                    label="Jenis Harga"
                    placeholder="-- Pilih Jenis Harga --"
                    value={item.price_type}
                    onChange={(e) => patchItem(i, { price_type: e.target.value })}
                    options={PRICE_TYPES}
                    error={!!errors[`items.${i}.price_type`]}
                    helperText={errors[`items.${i}.price_type`]}
                  />
                  <TextField
                    type="number"
                    label="Quantity"
                    value={item.quantity}
                    onChange={(e) => patchItem(i, { quantity: e.target.value })}
                    inputProps={{ step: 'any', inputMode: 'decimal' }}
                    InputProps={{
                      endAdornment: <InputAdornment position="end">{unitLabel(item.price_type)}</InputAdornment>,
                    }}
                    error={!!errors[`items.${i}.quantity`]}
                    helperText={errors[`items.${i}.quantity`]}
                  />
                  <TextField
                    label="Harga"
                    value={formatRupiah(item.price)}
                    InputProps={{ readOnly: true }}
                  />
                  <TextField
                    label="Subtotal"
                    value={formatRupiah(item.subtotal)}
                    InputProps={{ readOnly: true }}
                  />
                  <Box sx={{ pt: { md: 0.5 } }}>
                    <IconButton color="error" onClick={() => removeItem(i)} aria-label="Hapus produk">
                      <Trash2 size={16} />
                    </IconButton>
                  </Box>
                </Stack>
              ))}

              <Box>
                <Button variant="outlined" startIcon={<Plus size={18} />} onClick={addItem}>
                  Tambah Produk
                </Button>
              </Box>

              <Stack direction={{ xs: 'column', md: 'row' }} spacing={2} alignItems={{ md: 'center' }}>
                <TextField
                  type="number"
                  label="Diskon (Rp)"
                  value={form.data.discount}
                  onChange={(e) => form.setData('discount', e.target.value)}
                  inputProps={{ step: 'any', inputMode: 'decimal' }}
                  error={!!errors.discount}
                  helperText={errors.discount}
                />
                <Select
                  label="Metode Pembayaran"
                  placeholder="-- Pilih Metode Pembayaran --"
                  value={form.data.payment_method_id}
                  onChange={(e) => form.setData('payment_method_id', e.target.value)}
                  options={paymentMethods.map((pm) => ({ value: pm.id, label: pm.name }))}
                  error={!!errors.payment_method_id}
                  helperText={errors.payment_method_id}
                />
                <Box sx={{ flex: 1, textAlign: { md: 'right' } }}>
                  <Typography variant="h6">Total: {formatRupiah(total)}</Typography>
                </Box>
              </Stack>
            </>
          )}
        </Stack>
      </Dialog>

      <ConfirmDialog
        open={!!toDelete}
        title="Hapus Penjualan"
        message="Apakah anda yakin menghapus penjualan ini?"
        onClose={() => setToDelete(null)}
        onConfirm={() => {
          if (toDelete) {
            router.get(
              `/cetak-sales/delete/${toDelete.id}`,
              {},
              { preserveScroll: true, onFinish: () => setToDelete(null) },
            );
          }
        }}
      />
    </>
  );
}
