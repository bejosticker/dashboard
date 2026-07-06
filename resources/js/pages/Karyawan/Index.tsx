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
  type Column,
} from '@/ui';
import { Plus, Pencil, Trash2, Search } from '@/icons';
import { formatRupiah } from '@/lib/format';
import type { Paginator } from '@/types';

interface TokoOption {
  id: number;
  name: string;
}

interface Karyawan extends Record<string, unknown> {
  id: number;
  name: string;
  month: string;
  year: number | string;
  gaji: number | string;
  toko_id: number | null;
  toko: TokoOption | null;
}

interface Props {
  karyawans: Paginator<Karyawan>;
  tokos: TokoOption[];
  filters: { search?: string };
}

const BULAN = [
  'Januari',
  'Februari',
  'Maret',
  'April',
  'Mei',
  'Juni',
  'Juli',
  'Agustus',
  'September',
  'Oktober',
  'November',
  'Desember',
];

export default function Index({ karyawans, tokos, filters }: Props) {
  const [open, setOpen] = useState(false);
  const [editing, setEditing] = useState<Karyawan | null>(null);
  const [toDelete, setToDelete] = useState<Karyawan | null>(null);
  const [search, setSearch] = useState(filters.search ?? '');

  const form = useForm({
    name: '',
    month: 'Januari',
    year: '',
    gaji: '',
    toko_id: '' as number | string,
  });

  const openCreate = () => {
    setEditing(null);
    form.reset();
    form.clearErrors();
    setOpen(true);
  };

  const openEdit = (k: Karyawan) => {
    setEditing(k);
    form.clearErrors();
    form.setData({
      name: k.name,
      month: k.month ?? 'Januari',
      year: String(k.year ?? ''),
      gaji: String(k.gaji ?? ''),
      toko_id: k.toko_id ?? '',
    });
    setOpen(true);
  };

  const submit = (e: FormEvent) => {
    e.preventDefault();
    const opts = { onSuccess: () => setOpen(false), preserveScroll: true };
    if (editing) form.post(`/karyawan/update/${editing.id}`, opts);
    else form.post('/karyawan', opts);
  };

  const submitSearch = (e: FormEvent) => {
    e.preventDefault();
    router.get('/karyawan', { search }, { preserveState: true, replace: true });
  };

  const columns: Column<Karyawan>[] = [
    { key: 'no', label: '#', width: 60, render: (_r, i) => karyawans.from ? karyawans.from + i : i + 1 },
    { key: 'name', label: 'Nama' },
    { key: 'month', label: 'Bulan Masuk' },
    { key: 'year', label: 'Tahun Masuk' },
    { key: 'gaji', label: 'Gaji', align: 'right', render: (r) => formatRupiah(Number(r.gaji)) },
    { key: 'toko', label: 'Toko', render: (r) => r.toko?.name ?? '-' },
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
      <Head title="Karyawan" />
      <PageHeader
        title="Karyawan"
        actions={
          <Button startIcon={<Plus size={18} />} onClick={openCreate}>
            Tambah Karyawan
          </Button>
        }
      />

      <Card sx={{ mb: 3 }}>
        <Stack component="form" direction="row" spacing={1} onSubmit={submitSearch}>
          <TextField
            placeholder="Cari karyawan..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
          />
          <Button type="submit" startIcon={<Search size={18} />}>
            Cari
          </Button>
        </Stack>
      </Card>

      <Card disableContentPadding>
        <DataTable
          columns={columns}
          rows={karyawans.data}
          getRowId={(r) => r.id}
          emptyMessage="Belum ada data karyawan."
        />
      </Card>
      <Box sx={{ px: 1 }}>
        <Pagination paginator={karyawans} />
      </Box>

      <Dialog
        open={open}
        onClose={() => setOpen(false)}
        title={editing ? 'Edit Karyawan' : 'Tambah Karyawan'}
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
            label="Nama Karyawan"
            value={form.data.name}
            onChange={(e) => form.setData('name', e.target.value)}
            error={!!form.errors.name}
            helperText={form.errors.name}
            autoFocus
          />
          <Select
            label="Bulan Masuk"
            value={form.data.month}
            onChange={(e) => form.setData('month', e.target.value)}
            error={!!form.errors.month}
            helperText={form.errors.month}
            options={BULAN.map((b) => ({ value: b, label: b }))}
          />
          <TextField
            label="Tahun Masuk"
            type="number"
            placeholder="2019"
            value={form.data.year}
            onChange={(e) => form.setData('year', e.target.value)}
            error={!!form.errors.year}
            helperText={form.errors.year}
          />
          <Select
            label="Toko"
            value={form.data.toko_id}
            onChange={(e) => form.setData('toko_id', e.target.value)}
            placeholder="Pilih Toko"
            error={!!form.errors.toko_id}
            helperText={form.errors.toko_id}
            options={tokos.map((t) => ({ value: t.id, label: t.name }))}
          />
          <TextField
            label="Gaji"
            type="number"
            placeholder="1000000"
            value={form.data.gaji}
            onChange={(e) => form.setData('gaji', e.target.value)}
            error={!!form.errors.gaji}
            helperText={form.errors.gaji}
          />
        </Stack>
      </Dialog>

      <ConfirmDialog
        open={!!toDelete}
        title="Hapus Karyawan"
        message={`Apakah anda yakin menghapus karyawan ${toDelete?.name}?`}
        onClose={() => setToDelete(null)}
        onConfirm={() => {
          if (toDelete)
            router.get(
              `/karyawan/delete/${toDelete.id}`,
              {},
              { preserveScroll: true, onFinish: () => setToDelete(null) }
            );
        }}
      />
    </>
  );
}
