import { useState, type FormEvent, type KeyboardEvent } from 'react';
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
import { Plus, Pencil, Trash2, Search } from '@/icons';
import { formatRupiah, formatNumber } from '@/lib/format';

interface ProdukCetak extends Record<string, unknown> {
  id: number;
  name: string;
  price_grosir: number;
  price_umum: number;
  price_eceran_grosir: number;
  price_eceran_umum: number;
  kulak_price: number;
  stock: number;
}

interface Props {
  products: ProdukCetak[];
  filters: { search?: string };
}

type ProdukForm = {
  name: string;
  price_grosir: string;
  price_umum: string;
  price_eceran_grosir: string;
  price_eceran_umum: string;
  kulak_price: string;
  stock: string;
};

const emptyForm: ProdukForm = {
  name: '',
  price_grosir: '',
  price_umum: '',
  price_eceran_grosir: '',
  price_eceran_umum: '',
  kulak_price: '',
  stock: '',
};

export default function Index({ products, filters }: Props) {
  const [open, setOpen] = useState(false);
  const [editing, setEditing] = useState<ProdukCetak | null>(null);
  const [toDelete, setToDelete] = useState<ProdukCetak | null>(null);
  const [search, setSearch] = useState(filters.search ?? '');

  const form = useForm<ProdukForm>({ ...emptyForm });

  const openCreate = () => {
    setEditing(null);
    form.reset();
    form.clearErrors();
    setOpen(true);
  };

  const openEdit = (p: ProdukCetak) => {
    setEditing(p);
    form.clearErrors();
    form.setData({
      name: p.name ?? '',
      price_grosir: String(p.price_grosir ?? ''),
      price_umum: String(p.price_umum ?? ''),
      price_eceran_grosir: String(p.price_eceran_grosir ?? ''),
      price_eceran_umum: String(p.price_eceran_umum ?? ''),
      kulak_price: String(p.kulak_price ?? ''),
      stock: String(p.stock ?? ''),
    });
    setOpen(true);
  };

  const submit = (e: FormEvent) => {
    e.preventDefault();
    const opts = { onSuccess: () => setOpen(false), preserveScroll: true };
    if (editing) form.post(`/cetak-products/update/${editing.id}`, opts);
    else form.post('/cetak-products', opts);
  };

  const runSearch = () => {
    router.get('/cetak-products', { search }, { preserveState: true, replace: true });
  };

  const onSearchKeyDown = (e: KeyboardEvent<HTMLInputElement>) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      runSearch();
    }
  };

  const columns: Column<ProdukCetak>[] = [
    { key: 'no', label: '#', width: 56, render: (_p, i) => i + 1 },
    { key: 'name', label: 'Nama Produk Cetak' },
    { key: 'price_grosir', label: 'Harga Grosir', align: 'right', render: (p) => formatRupiah(p.price_grosir) },
    { key: 'price_umum', label: 'Harga Umum', align: 'right', render: (p) => formatRupiah(p.price_umum) },
    { key: 'price_eceran_grosir', label: 'Eceran Grosir', align: 'right', render: (p) => formatRupiah(p.price_eceran_grosir) },
    { key: 'price_eceran_umum', label: 'Eceran Umum', align: 'right', render: (p) => formatRupiah(p.price_eceran_umum) },
    { key: 'kulak_price', label: 'Harga Kulak', align: 'right', render: (p) => formatRupiah(p.kulak_price) },
    { key: 'stock', label: 'Stok', align: 'right', render: (p) => `${formatNumber(p.stock)} cm` },
    {
      key: 'aksi',
      label: 'Aksi',
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

  const numberFields: { key: keyof ProdukForm; label: string }[] = [
    { key: 'price_grosir', label: 'Harga Grosir (per centimeter)' },
    { key: 'price_umum', label: 'Harga Umum (per centimeter)' },
    { key: 'price_eceran_grosir', label: 'Harga Eceran Grosir (per lembar)' },
    { key: 'price_eceran_umum', label: 'Harga Eceran Umum (per lembar)' },
    { key: 'kulak_price', label: 'Harga Kulak (per centimeter)' },
    { key: 'stock', label: 'Stok (centimeter)' },
  ];

  return (
    <>
      <Head title="Produk Cetak" />
      <PageHeader
        title="Produk Cetak"
        actions={
          <Button startIcon={<Plus size={18} />} onClick={openCreate}>
            Tambah Produk
          </Button>
        }
      />

      <Card sx={{ mb: 2 }}>
        <Stack direction="row" spacing={1} sx={{ maxWidth: 420 }}>
          <TextField
            placeholder="Cari produk..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            onKeyDown={onSearchKeyDown}
          />
          <Button startIcon={<Search size={18} />} onClick={runSearch} sx={{ flexShrink: 0 }}>
            Cari
          </Button>
        </Stack>
      </Card>

      <Card disableContentPadding>
        <DataTable
          columns={columns}
          rows={products}
          getRowId={(p) => p.id}
          emptyMessage="Belum ada data produk."
        />
      </Card>

      <Dialog
        open={open}
        onClose={() => setOpen(false)}
        title={editing ? 'Ubah Produk' : 'Tambah Produk'}
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
            placeholder="Masukkan nama..."
            value={form.data.name}
            onChange={(e) => form.setData('name', e.target.value)}
            error={!!form.errors.name}
            helperText={form.errors.name}
            autoFocus
          />
          <Box
            sx={{
              display: 'grid',
              gap: 2,
              gridTemplateColumns: { xs: '1fr', sm: '1fr 1fr' },
            }}
          >
            {numberFields.map((f) => (
              <TextField
                key={f.key}
                label={f.label}
                type="number"
                inputProps={{ step: 'any', inputMode: 'decimal' }}
                value={form.data[f.key]}
                onChange={(e) => form.setData(f.key, e.target.value)}
                error={!!form.errors[f.key]}
                helperText={form.errors[f.key]}
              />
            ))}
          </Box>
        </Stack>
      </Dialog>

      <ConfirmDialog
        open={!!toDelete}
        title="Hapus Produk"
        message={`Apakah anda yakin menghapus produk "${toDelete?.name}"?`}
        onClose={() => setToDelete(null)}
        onConfirm={() => {
          if (toDelete)
            router.get(
              `/cetak-products/delete/${toDelete.id}`,
              {},
              { preserveScroll: true, onFinish: () => setToDelete(null) },
            );
        }}
      />
    </>
  );
}
