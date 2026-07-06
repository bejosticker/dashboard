import { useMemo, useRef, useState, type FormEvent } from 'react';
import { Head, useForm, router } from '@inertiajs/react';
import {
  Alert,
  Box,
  Button,
  Card,
  Checkbox,
  ConfirmDialog,
  DataTable,
  Dialog,
  Divider,
  IconButton,
  PageHeader,
  Pagination,
  Select,
  Stack,
  TextField,
  Typography,
  type Column,
} from '@/ui';
import { Plus, Trash2, Eye, Search } from '@/icons';
import { formatRupiah, formatDate } from '@/lib/format';
import type { Paginator } from '@/types';

interface KulakDetailItem extends Record<string, unknown> {
  id: number;
  product_name: string;
  price: number;
  rolls: number;
  subtotal: number;
}

interface KulakRow extends Record<string, unknown> {
  id: number;
  supplier_name: string;
  date: string;
  total: number;
  items_count: number;
  items: KulakDetailItem[];
}

interface SupplierOption {
  id: number;
  name: string;
}

interface ProductOption {
  id: number;
  name: string;
  harga: number;
}

interface Filters {
  from?: string;
  to?: string;
  supplier_id?: string | number;
}

interface Props {
  kulaks: Paginator<KulakRow>;
  suppliers: SupplierOption[];
  products: ProductOption[];
  total: number;
  filters: Filters;
}

interface LineItem {
  key: number;
  product_id: number | '';
  jumlah: string;
  harga: string;
  include: boolean;
}

export default function Index({ kulaks, suppliers, products, total, filters }: Props) {
  // Filter bar state
  const [from, setFrom] = useState(filters.from ?? '');
  const [to, setTo] = useState(filters.to ?? '');
  const [supplierId, setSupplierId] = useState(String(filters.supplier_id ?? ''));

  // Dialogs
  const [open, setOpen] = useState(false);
  const [detail, setDetail] = useState<KulakRow | null>(null);
  const [toDelete, setToDelete] = useState<KulakRow | null>(null);

  // Create form line items
  const [rows, setRows] = useState<LineItem[]>([]);
  const [search, setSearch] = useState('');
  const [itemError, setItemError] = useState('');
  const keyCounter = useRef(0);

  const form = useForm<{
    supplier_id: string;
    date: string;
    items: { product_id: number | ''; jumlah: number; harga: number }[];
  }>({ supplier_id: '', date: '', items: [] });

  const productMap = useMemo(() => {
    const map = new Map<number, ProductOption>();
    products.forEach((p) => map.set(p.id, p));
    return map;
  }, [products]);

  const buildInitialRows = (): LineItem[] =>
    products.map((p) => ({
      key: keyCounter.current++,
      product_id: p.id,
      jumlah: '0',
      harga: String(p.harga),
      include: false,
    }));

  const subtotalOf = (r: LineItem) => (Number(r.jumlah) || 0) * (Number(r.harga) || 0);
  const formTotal = rows.filter((r) => r.include).reduce((sum, r) => sum + subtotalOf(r), 0);

  const applyFilter = () => {
    router.get(
      window.location.pathname,
      { from, to, supplier_id: supplierId },
      { preserveState: true, preserveScroll: true, replace: true },
    );
  };

  const openCreate = () => {
    setRows(buildInitialRows());
    setSearch('');
    setItemError('');
    form.reset();
    form.clearErrors();
    setOpen(true);
  };

  const updateRow = (key: number, patch: Partial<LineItem>) =>
    setRows((rs) => rs.map((r) => (r.key === key ? { ...r, ...patch } : r)));

  const onProductChange = (key: number, value: string) => {
    const id = value === '' ? '' : Number(value);
    const product = id === '' ? undefined : productMap.get(id);
    updateRow(key, { product_id: id, harga: product ? String(product.harga) : '0' });
  };

  const addRow = () =>
    setRows((rs) => [
      ...rs,
      { key: keyCounter.current++, product_id: '', jumlah: '0', harga: '0', include: true },
    ]);

  const removeRow = (key: number) => setRows((rs) => rs.filter((r) => r.key !== key));
  const removeAllRows = () => setRows([]);

  const submit = (e: FormEvent) => {
    e.preventDefault();
    const included = rows.filter((r) => r.include);
    if (included.length === 0) {
      setItemError('Tidak ada item yang dipilih!');
      return;
    }
    setItemError('');
    form.transform((data) => ({
      ...data,
      items: included.map((r) => ({
        product_id: r.product_id,
        jumlah: Number(r.jumlah) || 0,
        harga: Number(r.harga) || 0,
      })),
    }));
    form.post('/kulak', {
      preserveScroll: true,
      onSuccess: () => setOpen(false),
    });
  };

  const visibleRows = rows.filter((r) => {
    if (search.trim() === '') return true;
    const name = r.product_id === '' ? '' : productMap.get(r.product_id)?.name ?? '';
    return name.toLowerCase().includes(search.trim().toLowerCase());
  });

  const columns: Column<KulakRow>[] = [
    { key: 'no', label: '#', width: 56, render: (_r, i) => (kulaks.from ?? 1) + i },
    { key: 'supplier_name', label: 'Supplier' },
    { key: 'date', label: 'Tanggal Pembelian', render: (r) => formatDate(r.date) },
    { key: 'total', label: 'Total Nominal', align: 'right', render: (r) => formatRupiah(r.total) },
    { key: 'items_count', label: 'Total Produk', align: 'right', render: (r) => `${r.items_count} Produk` },
    {
      key: 'aksi',
      label: 'Aksi',
      align: 'right',
      render: (r) => (
        <Stack direction="row" spacing={0.5} justifyContent="flex-end">
          <IconButton color="success" onClick={() => setDetail(r)} aria-label="Rincian">
            <Eye size={16} />
          </IconButton>
          <IconButton color="error" onClick={() => setToDelete(r)} aria-label="Hapus">
            <Trash2 size={16} />
          </IconButton>
        </Stack>
      ),
    },
  ];

  const detailColumns: Column<KulakDetailItem>[] = [
    { key: 'no', label: 'No.', width: 56, render: (_r, i) => i + 1 },
    { key: 'product_name', label: 'Nama Produk' },
    { key: 'price', label: 'Harga', align: 'right', render: (r) => formatRupiah(r.price) },
    { key: 'rolls', label: 'Quantity (Roll)', align: 'right', render: (r) => r.rolls.toLocaleString('id-ID') },
    { key: 'subtotal', label: 'Subtotal', align: 'right', render: (r) => formatRupiah(r.subtotal) },
  ];

  return (
    <>
      <Head title="Pembelian Bahan" />
      <PageHeader
        title="Pembelian Bahan"
        actions={
          <Button startIcon={<Plus size={18} />} onClick={openCreate}>
            Tambah Pembelian Bahan
          </Button>
        }
      />

      <Card sx={{ mb: 3 }}>
        <Stack direction={{ xs: 'column', md: 'row' }} spacing={2} alignItems={{ md: 'flex-end' }}>
          <Select
            label="Supplier"
            value={supplierId}
            onChange={(e) => setSupplierId(e.target.value)}
            placeholder="Semua Supplier"
            options={suppliers.map((s) => ({ value: String(s.id), label: s.name }))}
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
          <Button startIcon={<Search size={18} />} onClick={applyFilter} sx={{ flexShrink: 0 }}>
            Filter
          </Button>
        </Stack>
      </Card>

      <Card disableContentPadding>
        <DataTable
          columns={columns}
          rows={kulaks.data}
          getRowId={(r) => r.id}
          emptyMessage="Belum ada data pembelian bahan."
        />
        <Divider />
        <Stack direction="row" justifyContent="space-between" sx={{ px: 2, py: 2 }}>
          <Typography variant="subtitle2">Grand Total</Typography>
          <Typography variant="subtitle2">{formatRupiah(total)}</Typography>
        </Stack>
      </Card>
      <Box sx={{ px: 1 }}>
        <Pagination paginator={kulaks} />
      </Box>

      {/* Detail modal */}
      <Dialog
        open={!!detail}
        onClose={() => setDetail(null)}
        title="Rincian Pembelian Bahan"
        maxWidth="md"
      >
        {detail && (
          <DataTable
            columns={detailColumns}
            rows={detail.items}
            getRowId={(r) => r.id}
            emptyMessage="Tidak ada item."
          />
        )}
      </Dialog>

      {/* Delete confirm */}
      <ConfirmDialog
        open={!!toDelete}
        title="Hapus Pembelian Bahan"
        message="Apakah anda yakin menghapus data pembelian bahan ini?"
        onClose={() => setToDelete(null)}
        onConfirm={() => {
          if (toDelete) {
            router.get(`/kulak/delete/${toDelete.id}`, {}, {
              preserveScroll: true,
              onFinish: () => setToDelete(null),
            });
          }
        }}
      />

      {/* Create modal */}
      <Dialog
        open={open}
        onClose={() => setOpen(false)}
        title="Tambah Pembelian Bahan"
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
          <Stack direction={{ xs: 'column', md: 'row' }} spacing={2} alignItems={{ md: 'flex-end' }}>
            <Select
              label="Pilih Supplier"
              value={form.data.supplier_id}
              onChange={(e) => form.setData('supplier_id', e.target.value)}
              placeholder="-- Pilih Supplier --"
              options={suppliers.map((s) => ({ value: String(s.id), label: s.name }))}
              error={!!form.errors.supplier_id}
              helperText={form.errors.supplier_id}
            />
            <TextField
              label="Tanggal Pembelian"
              type="date"
              value={form.data.date}
              onChange={(e) => form.setData('date', e.target.value)}
              InputLabelProps={{ shrink: true }}
              error={!!form.errors.date}
              helperText={form.errors.date}
            />
            <TextField
              label="Cari Produk"
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              placeholder="Cari produk..."
            />
            <Box sx={{ flexShrink: 0, whiteSpace: 'nowrap' }}>
              <Typography variant="caption" color="text.secondary">
                Total
              </Typography>
              <Typography variant="h6">{formatRupiah(formTotal)}</Typography>
            </Box>
          </Stack>

          {itemError && <Alert severity="error">{itemError}</Alert>}

          <Divider />

          {/* Header */}
          <Stack
            direction="row"
            spacing={1}
            alignItems="center"
            sx={{ px: 0.5, color: 'text.secondary', display: { xs: 'none', md: 'flex' } }}
          >
            <Box sx={{ width: 40 }} />
            <Typography variant="caption" sx={{ flex: 1, fontWeight: 600 }}>
              Produk
            </Typography>
            <Typography variant="caption" sx={{ width: 130, fontWeight: 600 }}>
              Quantity (Roll)
            </Typography>
            <Typography variant="caption" sx={{ width: 140, fontWeight: 600 }}>
              Harga
            </Typography>
            <Typography variant="caption" sx={{ width: 140, fontWeight: 600 }}>
              Subtotal
            </Typography>
            <Box sx={{ width: 40 }} />
          </Stack>

          <Box sx={{ maxHeight: '45vh', overflowY: 'auto', pr: 0.5 }}>
            <Stack spacing={1.5}>
              {visibleRows.length === 0 ? (
                <Typography variant="body2" color="text.secondary" sx={{ py: 2, textAlign: 'center' }}>
                  Tidak ada produk.
                </Typography>
              ) : (
                visibleRows.map((r) => (
                  <Stack
                    key={r.key}
                    direction={{ xs: 'column', md: 'row' }}
                    spacing={1}
                    alignItems={{ md: 'center' }}
                  >
                    <Checkbox
                      checked={r.include}
                      onChange={(e) => updateRow(r.key, { include: e.target.checked })}
                      sx={{ p: 0.5, width: 40 }}
                    />
                    <Box sx={{ flex: 1, width: '100%' }}>
                      <Select
                        value={r.product_id === '' ? '' : String(r.product_id)}
                        onChange={(e) => onProductChange(r.key, e.target.value)}
                        placeholder="-- Pilih Produk --"
                        options={products.map((p) => ({ value: String(p.id), label: p.name }))}
                      />
                    </Box>
                    <Box sx={{ width: { xs: '100%', md: 130 } }}>
                      <TextField
                        type="number"
                        value={r.jumlah}
                        onChange={(e) => updateRow(r.key, { jumlah: e.target.value })}
                        inputProps={{ step: 'any', inputMode: 'decimal' }}
                        placeholder="Jumlah"
                      />
                    </Box>
                    <Box sx={{ width: { xs: '100%', md: 140 } }}>
                      <TextField
                        type="number"
                        value={r.harga}
                        onChange={(e) => updateRow(r.key, { harga: e.target.value })}
                        inputProps={{ step: 'any', inputMode: 'decimal' }}
                        placeholder="Harga"
                      />
                    </Box>
                    <Typography variant="body2" sx={{ width: { xs: '100%', md: 140 } }}>
                      {formatRupiah(subtotalOf(r))}
                    </Typography>
                    <IconButton color="error" onClick={() => removeRow(r.key)} aria-label="Hapus produk">
                      <Trash2 size={16} />
                    </IconButton>
                  </Stack>
                ))
              )}
            </Stack>
          </Box>

          <Stack direction="row" spacing={1}>
            <Button variant="outlined" startIcon={<Plus size={18} />} onClick={addRow}>
              Tambah Produk
            </Button>
            <Button variant="outlined" color="error" startIcon={<Trash2 size={18} />} onClick={removeAllRows}>
              Hapus Semua Produk
            </Button>
          </Stack>
        </Stack>
      </Dialog>
    </>
  );
}
