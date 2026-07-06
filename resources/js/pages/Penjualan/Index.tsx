import { useState, type FormEvent } from 'react';
import { Head, useForm, router } from '@inertiajs/react';
import {
  Box,
  Button,
  Card,
  ConfirmDialog,
  DataTable,
  Dialog,
  Divider,
  IconButton,
  InputAdornment,
  PageHeader,
  Pagination,
  Select,
  StatCard,
  Stack,
  TextField,
  Typography,
  type Column,
} from '@/ui';
import { Plus, Trash2, Search } from '@/icons';
import { formatRupiah, formatDate } from '@/lib/format';
import type { Paginator } from '@/types';

const PRICE_TYPES: { value: string; label: string }[] = [
  { value: 'price_agent', label: 'Harga Agen' },
  { value: 'price_grosir', label: 'Harga Grosir' },
  { value: 'price_umum_roll', label: 'Harga Roll Umum' },
  { value: 'price_grosir_meter', label: 'Harga Meteran Grosir' },
  { value: 'price_umum_meter', label: 'Harga Meteran Umum' },
];

const ROLL_PRICE_TYPES = ['price_agent', 'price_grosir', 'price_umum_roll'];

interface SaleRow extends Record<string, unknown> {
  id: number;
  customer: string | null;
  customer_phone: string | null;
  date: string;
  total: number;
  discount: number;
  laba: number;
  items_count: number;
  payment_method: string | null;
}

interface PaymentMethod {
  id: number;
  name: string;
}

interface Product extends Record<string, unknown> {
  id: number;
  name: string;
  price_agent: number;
  price_grosir: number;
  price_umum_roll: number;
  price_grosir_meter: number;
  price_umum_meter: number;
}

interface Customer {
  name: string | null;
  phone: string;
}

interface SaleItemForm {
  product_id: number | '';
  price_type: string;
  jumlah: string;
  price: number;
  subtotal: number;
}

interface Filters {
  from: string;
  to: string;
  payment_method_id: string;
}

interface Props {
  sales: Paginator<SaleRow>;
  paymentMethods: PaymentMethod[];
  products: Product[];
  customers: Customer[];
  labaTotal: number;
  total: number;
  filters: Filters;
}

const blankItem = (): SaleItemForm => ({
  product_id: '',
  price_type: '',
  jumlah: '',
  price: 0,
  subtotal: 0,
});

const priceForType = (products: Product[], productId: number | '', priceType: string): number => {
  if (!productId || !priceType) return 0;
  const p = products.find((pr) => pr.id === productId);
  if (!p) return 0;
  return Number((p as Record<string, unknown>)[priceType] ?? 0);
};

const calcSubtotal = (jumlah: string, price: number): number =>
  Math.ceil((parseFloat(jumlah) || 0) * price);

export default function Index({
  sales,
  paymentMethods,
  products,
  customers,
  labaTotal,
  total,
  filters,
}: Props) {
  // Filter (GET) state.
  const [from, setFrom] = useState(filters.from ?? '');
  const [to, setTo] = useState(filters.to ?? '');
  const [paymentFilter, setPaymentFilter] = useState(filters.payment_method_id ?? '');

  const [open, setOpen] = useState(false);
  const [toDelete, setToDelete] = useState<SaleRow | null>(null);

  const form = useForm<{
    date: string;
    customer: string;
    customer_phone: string;
    payment_method_id: string;
    discount: string;
    items: SaleItemForm[];
    total: number;
  }>({
    date: '',
    customer: '',
    customer_phone: '',
    payment_method_id: '',
    discount: '',
    items: [],
    total: 0,
  });

  const errs = form.errors as Record<string, string>;

  const items = form.data.items;
  const grandSubtotal = items.reduce((sum, it) => sum + it.subtotal, 0);
  const formTotal = grandSubtotal - (parseFloat(form.data.discount) || 0);

  const applyFilter = (e: FormEvent) => {
    e.preventDefault();
    router.get(
      '/sales',
      { from, to, payment_method_id: paymentFilter },
      { preserveState: true, replace: true },
    );
  };

  const openCreate = () => {
    form.reset();
    form.clearErrors();
    setOpen(true);
  };

  const setItems = (next: SaleItemForm[]) => form.setData('items', next);

  const addItem = () => setItems([...items, blankItem()]);

  const removeItem = (index: number) => setItems(items.filter((_, i) => i !== index));

  const changeProduct = (index: number, productId: number | '') => {
    setItems(
      items.map((it, i) => {
        if (i !== index) return it;
        const price = priceForType(products, productId, it.price_type);
        return { ...it, product_id: productId, price, subtotal: calcSubtotal(it.jumlah, price) };
      }),
    );
  };

  const changePriceType = (index: number, priceType: string) => {
    setItems(
      items.map((it, i) => {
        if (i !== index) return it;
        const price = priceForType(products, it.product_id, priceType);
        return { ...it, price_type: priceType, price, subtotal: calcSubtotal(it.jumlah, price) };
      }),
    );
  };

  const changeJumlah = (index: number, jumlah: string) => {
    setItems(
      items.map((it, i) =>
        i === index ? { ...it, jumlah, subtotal: calcSubtotal(jumlah, it.price) } : it,
      ),
    );
  };

  const pickCustomer = (phone: string) => {
    const c = customers.find((cc) => cc.phone === phone);
    form.setData((data) => ({
      ...data,
      customer_phone: phone,
      customer: c?.name ?? data.customer,
    }));
  };

  const submit = (e: FormEvent) => {
    e.preventDefault();
    form.transform((data) => ({ ...data, total: formTotal }));
    form.post('/sales', {
      preserveScroll: true,
      onSuccess: () => {
        form.reset();
        setOpen(false);
      },
    });
  };

  const columns: Column<SaleRow>[] = [
    { key: 'customer', label: 'Nama Customer', render: (r) => r.customer || '-' },
    { key: 'customer_phone', label: 'No. WA', render: (r) => r.customer_phone || '-' },
    { key: 'date', label: 'Tanggal', render: (r) => formatDate(r.date) },
    { key: 'total', label: 'Total Nominal', align: 'right', render: (r) => formatRupiah(r.total) },
    { key: 'laba', label: 'Laba', align: 'right', render: (r) => formatRupiah(r.laba) },
    { key: 'discount', label: 'Diskon', align: 'right', render: (r) => formatRupiah(r.discount) },
    { key: 'items_count', label: 'Total Produk', align: 'right', render: (r) => `${r.items_count} Produk` },
    { key: 'payment_method', label: 'Metode Pembayaran', render: (r) => r.payment_method || '-' },
    {
      key: 'aksi',
      label: '',
      align: 'right',
      render: (r) => (
        <IconButton color="error" onClick={() => setToDelete(r)} aria-label="Hapus">
          <Trash2 size={16} />
        </IconButton>
      ),
    },
  ];

  const paymentOptions = paymentMethods.map((pm) => ({ value: pm.id, label: pm.name }));
  const productOptions = products.map((p) => ({ value: p.id, label: p.name }));
  const customerOptions = customers.map((c) => ({
    value: c.phone,
    label: c.name ? `${c.name} — ${c.phone}` : c.phone,
  }));

  return (
    <>
      <Head title="Penjualan" />
      <PageHeader
        title="Penjualan"
        actions={
          <Button startIcon={<Plus size={18} />} onClick={openCreate}>
            Tambah Penjualan
          </Button>
        }
      />

      <Card sx={{ mb: 3 }}>
        <Stack
          component="form"
          onSubmit={applyFilter}
          direction={{ xs: 'column', md: 'row' }}
          spacing={2}
          alignItems={{ xs: 'stretch', md: 'flex-end' }}
        >
          <Select
            label="Metode Pembayaran"
            value={paymentFilter}
            onChange={(e) => setPaymentFilter(e.target.value)}
            placeholder="Semua Metode"
            options={paymentOptions}
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

      <Box
        sx={{
          display: 'grid',
          gap: 2,
          gridTemplateColumns: { xs: '1fr', sm: '1fr 1fr' },
          mb: 3,
        }}
      >
        <StatCard label="Grand Total" value={formatRupiah(total)} color="primary" />
        <StatCard label="Total Laba" value={formatRupiah(labaTotal)} color="success" />
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

      <Dialog
        open={open}
        onClose={() => setOpen(false)}
        title="Tambah Penjualan"
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
              label="Tanggal Penjualan"
              type="date"
              value={form.data.date}
              onChange={(e) => form.setData('date', e.target.value)}
              InputLabelProps={{ shrink: true }}
              error={!!errs.date}
              helperText={errs.date}
            />
            <Select
              label="Pelanggan (opsional)"
              value={form.data.customer_phone}
              onChange={(e) => pickCustomer(e.target.value)}
              placeholder="-- Pilih Pelanggan --"
              options={customerOptions}
            />
          </Stack>
          <Stack direction={{ xs: 'column', md: 'row' }} spacing={2}>
            <TextField
              label="Nomor WA (08xxxx)"
              value={form.data.customer_phone}
              onChange={(e) => form.setData('customer_phone', e.target.value)}
              error={!!errs.customer_phone}
              helperText={errs.customer_phone}
            />
            <TextField
              label="Nama Customer (opsional)"
              value={form.data.customer}
              onChange={(e) => form.setData('customer', e.target.value)}
              error={!!errs.customer}
              helperText={errs.customer}
            />
          </Stack>

          <Divider />

          {form.data.date === '' ? (
            <Typography variant="body2" color="text.secondary">
              Isi tanggal penjualan terlebih dahulu untuk menambah produk.
            </Typography>
          ) : (
            <>
              <Stack spacing={1.5}>
                {items.map((item, i) => {
                  const isRoll = ROLL_PRICE_TYPES.includes(item.price_type);
                  return (
                    <Stack
                      key={i}
                      direction={{ xs: 'column', md: 'row' }}
                      spacing={1}
                      alignItems={{ xs: 'stretch', md: 'flex-start' }}
                    >
                      <Typography sx={{ minWidth: 24, pt: { md: 1 } }}>{i + 1}.</Typography>
                      <Select
                        label="Produk"
                        value={item.product_id}
                        onChange={(e) =>
                          changeProduct(i, e.target.value === '' ? '' : Number(e.target.value))
                        }
                        placeholder="-- Pilih Produk --"
                        options={productOptions}
                        error={!!errs[`items.${i}.product_id`]}
                        helperText={errs[`items.${i}.product_id`]}
                      />
                      <Select
                        label="Jenis Harga"
                        value={item.price_type}
                        onChange={(e) => changePriceType(i, e.target.value)}
                        placeholder="-- Pilih Jenis Harga --"
                        options={PRICE_TYPES}
                        error={!!errs[`items.${i}.price_type`]}
                        helperText={errs[`items.${i}.price_type`]}
                      />
                      <TextField
                        label="Jumlah"
                        type="number"
                        value={item.jumlah}
                        onChange={(e) => changeJumlah(i, e.target.value)}
                        inputProps={{ step: 'any', inputMode: 'decimal' }}
                        InputProps={{
                          endAdornment: (
                            <InputAdornment position="end">{isRoll ? 'Roll' : 'Meter'}</InputAdornment>
                          ),
                        }}
                        error={!!errs[`items.${i}.jumlah`]}
                        helperText={errs[`items.${i}.jumlah`]}
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
                      <IconButton
                        color="error"
                        onClick={() => removeItem(i)}
                        aria-label="Hapus produk"
                        sx={{ alignSelf: { xs: 'flex-end', md: 'center' } }}
                      >
                        <Trash2 size={18} />
                      </IconButton>
                    </Stack>
                  );
                })}
              </Stack>

              <Box>
                <Button
                  variant="outlined"
                  startIcon={<Plus size={18} />}
                  onClick={addItem}
                >
                  Tambah Produk
                </Button>
              </Box>

              <Divider />

              <Stack direction={{ xs: 'column', md: 'row' }} spacing={2} alignItems={{ md: 'flex-end' }}>
                <TextField
                  label="Diskon (Rp)"
                  type="number"
                  value={form.data.discount}
                  onChange={(e) => form.setData('discount', e.target.value)}
                  inputProps={{ inputMode: 'numeric' }}
                  error={!!errs.discount}
                  helperText={errs.discount}
                />
                <Select
                  label="Metode Pembayaran"
                  value={form.data.payment_method_id}
                  onChange={(e) => form.setData('payment_method_id', e.target.value)}
                  placeholder="-- Pilih Metode Pembayaran --"
                  options={paymentOptions}
                  error={!!errs.payment_method_id}
                  helperText={errs.payment_method_id}
                />
                <Box sx={{ flexGrow: 1, textAlign: { md: 'right' } }}>
                  <Typography variant="h6">Total: {formatRupiah(formTotal)}</Typography>
                </Box>
              </Stack>
            </>
          )}
        </Stack>
      </Dialog>

      <ConfirmDialog
        open={!!toDelete}
        title="Hapus Penjualan"
        message="Apakah Anda yakin menghapus penjualan ini? Stok produk terkait akan dikembalikan."
        onClose={() => setToDelete(null)}
        onConfirm={() => {
          if (toDelete) {
            router.get(
              `/sales/delete/${toDelete.id}`,
              {},
              { preserveScroll: true, onFinish: () => setToDelete(null) },
            );
          }
        }}
      />
    </>
  );
}
