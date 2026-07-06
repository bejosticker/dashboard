import { useState, type FormEvent, type ChangeEvent } from 'react';
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
  Stack,
  StatCard,
  StatusChip,
  TextField,
  Typography,
  type Column,
} from '@/ui';
import { Plus, Pencil, Trash2, Search } from '@/icons';
import { formatRupiah, formatNumber } from '@/lib/format';
import type { Paginator } from '@/types';

interface ProductRow extends Record<string, unknown> {
  id: number;
  name: string;
  image: string;
  stock_cm: number;
  per_roll_cm: number;
  minimum_stock_cm: number;
  price_kulak: number;
  price_agent: number;
  price_grosir: number;
  price_umum_roll: number;
  price_grosir_meter: number;
  price_umum_meter: number;
  price_eceran_grosir_cm: number;
  price_eceran_umum_cm: number;
}

type PriceKey =
  | 'price_kulak'
  | 'price_agent'
  | 'price_grosir'
  | 'price_umum_roll'
  | 'price_grosir_meter'
  | 'price_umum_meter'
  | 'price_eceran_grosir_cm'
  | 'price_eceran_umum_cm';

interface Totals {
  kulak: number;
  agen: number;
  grosir: number;
  rollUmum: number;
  meteranGrosir: number;
  meteranUmum: number;
}

interface Props {
  products: Paginator<ProductRow>;
  filters: { search?: string };
  totals: Totals;
}

interface ProductFormData {
  name: string;
  image: File | null;
  price_kulak: string;
  price_agent: string;
  price_grosir: string;
  price_umum_roll: string;
  price_grosir_meter: string;
  price_umum_meter: string;
  price_eceran_grosir_cm: string;
  price_eceran_umum_cm: string;
  minimum_stock_cm: string;
  per_roll_cm: string;
}

const emptyForm: ProductFormData = {
  name: '',
  image: null,
  price_kulak: '',
  price_agent: '',
  price_grosir: '',
  price_umum_roll: '',
  price_grosir_meter: '',
  price_umum_meter: '',
  price_eceran_grosir_cm: '',
  price_eceran_umum_cm: '',
  minimum_stock_cm: '',
  per_roll_cm: '',
};

/** Format stok cm menjadi "X Roll Y Meter" (mirror logika blade). */
function formatStock(stockCm: number, rollCm: number): string {
  const roll = rollCm > 0 ? Math.floor(stockCm / rollCm) : 0;
  const sisaCm = rollCm > 0 ? stockCm % rollCm : stockCm;
  const meter = Math.floor(sisaCm / 100);
  return meter > 0 ? `${roll} Roll ${meter} Meter` : `${roll} Roll`;
}

export default function Index({ products, filters, totals }: Props) {
  const [open, setOpen] = useState(false);
  const [editing, setEditing] = useState<ProductRow | null>(null);
  const [toDelete, setToDelete] = useState<ProductRow | null>(null);
  const [search, setSearch] = useState(filters.search ?? '');

  const form = useForm<ProductFormData>({ ...emptyForm });

  const openCreate = () => {
    setEditing(null);
    form.setData({ ...emptyForm });
    form.clearErrors();
    setOpen(true);
  };

  const openEdit = (p: ProductRow) => {
    setEditing(p);
    form.clearErrors();
    form.setData({
      name: p.name ?? '',
      image: null,
      price_kulak: String(p.price_kulak ?? ''),
      price_agent: String(p.price_agent ?? ''),
      price_grosir: String(p.price_grosir ?? ''),
      price_umum_roll: String(p.price_umum_roll ?? ''),
      price_grosir_meter: String(p.price_grosir_meter ?? ''),
      price_umum_meter: String(p.price_umum_meter ?? ''),
      price_eceran_grosir_cm: String(p.price_eceran_grosir_cm ?? ''),
      price_eceran_umum_cm: String(p.price_eceran_umum_cm ?? ''),
      minimum_stock_cm: String(p.per_roll_cm > 0 ? p.minimum_stock_cm / p.per_roll_cm : 0),
      per_roll_cm: String(p.per_roll_cm / 100),
    });
    setOpen(true);
  };

  const submit = (e: FormEvent) => {
    e.preventDefault();
    const opts = { onSuccess: () => setOpen(false), preserveScroll: true, forceFormData: true };
    if (editing) form.post(`/products/update/${editing.id}`, opts);
    else form.post('/products', opts);
  };

  const submitSearch = (e: FormEvent) => {
    e.preventDefault();
    router.get(window.location.pathname, { search }, { preserveState: true, replace: true });
  };

  const onImage = (e: ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    const target = e.target as HTMLInputElement;
    form.setData('image', target.files?.[0] ?? null);
  };

  const columns: Column<ProductRow>[] = [
    {
      key: 'name',
      label: 'Nama',
      render: (p) => (
        <Stack direction="row" spacing={1.5} alignItems="center">
          <Box
            component="img"
            src={`/assets/img/products/${p.image}`}
            alt={p.name}
            sx={{ width: 48, height: 48, borderRadius: 1, objectFit: 'cover', flexShrink: 0 }}
          />
          <Typography variant="body2">{p.name}</Typography>
        </Stack>
      ),
    },
    {
      key: 'stock_cm',
      label: 'Stok',
      render: (p) => (
        <Stack direction="row" spacing={1} alignItems="center">
          <span>{formatStock(p.stock_cm, p.per_roll_cm)}</span>
          {p.stock_cm <= p.minimum_stock_cm && <StatusChip label="Menipis" color="warning" />}
        </Stack>
      ),
    },
    {
      key: 'price_kulak',
      label: 'Kulak',
      align: 'right',
      render: (p) => (
        <Box>
          <div>{formatRupiah(p.price_kulak)}</div>
          <Typography variant="caption" color="success.main" sx={{ fontWeight: 600 }}>
            Total: {formatRupiah(p.per_roll_cm > 0 ? (p.price_kulak * p.stock_cm) / p.per_roll_cm : 0)}
          </Typography>
        </Box>
      ),
    },
    { key: 'price_agent', label: 'Agen', align: 'right', render: (p) => formatRupiah(p.price_agent) },
    { key: 'price_grosir', label: 'Grosir', align: 'right', render: (p) => formatRupiah(p.price_grosir) },
    { key: 'price_umum_roll', label: 'Roll Umum', align: 'right', render: (p) => formatRupiah(p.price_umum_roll) },
    { key: 'price_grosir_meter', label: 'Meter Grosir', align: 'right', render: (p) => formatRupiah(p.price_grosir_meter) },
    { key: 'price_umum_meter', label: 'Meter Umum', align: 'right', render: (p) => formatRupiah(p.price_umum_meter) },
    {
      key: 'minimum_stock_cm',
      label: 'Minimal',
      align: 'right',
      render: (p) => `${formatNumber(p.per_roll_cm > 0 ? p.minimum_stock_cm / p.per_roll_cm : 0)} Roll`,
    },
    {
      key: 'aksi',
      label: '',
      align: 'right',
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

  const priceField = (key: PriceKey, label: string) => (
    <TextField
      label={label}
      type="number"
      inputProps={{ step: 'any', inputMode: 'decimal' }}
      value={form.data[key]}
      onChange={(e) => form.setData(key, e.target.value)}
      error={!!form.errors[key]}
      helperText={form.errors[key]}
    />
  );

  return (
    <>
      <Head title="Produk" />
      <PageHeader
        title="Produk"
        actions={
          <Button startIcon={<Plus size={18} />} onClick={openCreate}>
            Tambah Produk
          </Button>
        }
      />

      <Box
        sx={{
          display: 'grid',
          gap: 2,
          gridTemplateColumns: { xs: '1fr 1fr', sm: 'repeat(3, 1fr)', lg: 'repeat(6, 1fr)' },
          mb: 3,
        }}
      >
        <StatCard label="Total Kulak" value={formatRupiah(totals.kulak)} color="warning" />
        <StatCard label="Total Agen" value={formatRupiah(totals.agen)} color="primary" />
        <StatCard label="Total Grosir" value={formatRupiah(totals.grosir)} color="info" />
        <StatCard label="Total Roll Umum" value={formatRupiah(totals.rollUmum)} color="success" />
        <StatCard label="Total Meter Grosir" value={formatRupiah(totals.meteranGrosir)} color="secondary" />
        <StatCard label="Total Meter Umum" value={formatRupiah(totals.meteranUmum)} color="error" />
      </Box>

      <Card sx={{ mb: 3 }}>
        <Stack component="form" direction="row" spacing={1} onSubmit={submitSearch}>
          <TextField
            placeholder="Cari produk..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            InputProps={{
              startAdornment: (
                <InputAdornment position="start">
                  <Search size={18} />
                </InputAdornment>
              ),
            }}
          />
          <Button type="submit" sx={{ flexShrink: 0 }}>
            Cari
          </Button>
        </Stack>
      </Card>

      <Card disableContentPadding>
        <DataTable
          columns={columns}
          rows={products.data}
          getRowId={(p) => p.id}
          emptyMessage="Belum ada data produk."
        />
      </Card>
      <Box sx={{ px: 1 }}>
        <Pagination paginator={products} />
      </Box>

      <Dialog
        open={open}
        onClose={() => setOpen(false)}
        title={editing ? 'Ubah Produk' : 'Tambah Produk'}
        maxWidth="md"
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
            label="Nama Produk"
            value={form.data.name}
            onChange={(e) => form.setData('name', e.target.value)}
            error={!!form.errors.name}
            helperText={form.errors.name}
            autoFocus
          />
          <TextField
            label="Foto Produk"
            type="file"
            inputProps={{ accept: 'image/*' }}
            InputLabelProps={{ shrink: true }}
            onChange={onImage}
            error={!!form.errors.image}
            helperText={form.errors.image}
          />

          <Box sx={{ display: 'grid', gap: 2, gridTemplateColumns: { xs: '1fr', sm: '1fr 1fr' } }}>
            {priceField('price_kulak', 'Harga Kulak (per roll)')}
            {priceField('price_agent', 'Harga Agen (per roll)')}
            {priceField('price_grosir', 'Harga Grosir (per roll)')}
            {priceField('price_umum_roll', 'Harga Roll Umum (per roll)')}
            {priceField('price_grosir_meter', 'Harga Meteran Grosir (per meter)')}
            {priceField('price_umum_meter', 'Harga Meteran Umum (per meter)')}
            {priceField('price_eceran_grosir_cm', 'Harga Eceran Grosir (per cm)')}
            {priceField('price_eceran_umum_cm', 'Harga Eceran Umum (per cm)')}
          </Box>

          <Box sx={{ display: 'grid', gap: 2, gridTemplateColumns: { xs: '1fr', sm: '1fr 1fr' } }}>
            <TextField
              label="Stok Minimal"
              type="number"
              inputProps={{ step: 'any', inputMode: 'decimal' }}
              value={form.data.minimum_stock_cm}
              onChange={(e) => form.setData('minimum_stock_cm', e.target.value)}
              error={!!form.errors.minimum_stock_cm}
              helperText={form.errors.minimum_stock_cm}
              InputProps={{ endAdornment: <InputAdornment position="end">Roll</InputAdornment> }}
            />
            <TextField
              label="Per Roll"
              type="number"
              inputProps={{ step: 'any', inputMode: 'decimal' }}
              value={form.data.per_roll_cm}
              onChange={(e) => form.setData('per_roll_cm', e.target.value)}
              error={!!form.errors.per_roll_cm}
              helperText={form.errors.per_roll_cm}
              InputProps={{ endAdornment: <InputAdornment position="end">Meter</InputAdornment> }}
            />
          </Box>
        </Stack>
      </Dialog>

      <ConfirmDialog
        open={!!toDelete}
        title="Hapus Produk"
        message={`Apakah Anda yakin menghapus produk "${toDelete?.name}"?`}
        onClose={() => setToDelete(null)}
        onConfirm={() => {
          if (toDelete)
            router.get(
              `/products/delete/${toDelete.id}`,
              {},
              { preserveScroll: true, onFinish: () => setToDelete(null) },
            );
        }}
      />
    </>
  );
}
