import { useState, type FormEvent } from 'react';
import { Head, useForm, router } from '@inertiajs/react';
import {
  Alert,
  Box,
  Button,
  Card,
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
import { Plus, Eye, Trash2 } from '@/icons';
import { formatRupiah, formatDate } from '@/lib/format';
import type { Paginator } from '@/types';

interface GajiRow extends Record<string, unknown> {
  id: number;
  month: string;
  year: number | string;
  date: string;
  items_count: number;
  items_sum_amount: number | string | null;
}

interface Karyawan {
  id: number;
  name: string;
  gaji: number | string | null;
}

interface Props {
  gajis: Paginator<GajiRow>;
  karyawans: Karyawan[];
}

const MONTHS = [
  'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
  'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
];

export default function Index({ gajis, karyawans }: Props) {
  const [open, setOpen] = useState(false);
  const [toDelete, setToDelete] = useState<GajiRow | null>(null);

  const form = useForm<{
    month: string;
    year: string;
    date: string;
    karyawan_id: number[];
    gaji: (number | string)[];
  }>({
    month: 'Januari',
    year: '',
    date: '',
    karyawan_id: karyawans.map((k) => k.id),
    gaji: karyawans.map((k) => k.gaji ?? 0),
  });

  const openCreate = () => {
    form.setData({
      month: 'Januari',
      year: '',
      date: '',
      karyawan_id: karyawans.map((k) => k.id),
      gaji: karyawans.map((k) => k.gaji ?? 0),
    });
    form.clearErrors();
    setOpen(true);
  };

  const setGajiAt = (index: number, value: string) => {
    const next = [...form.data.gaji];
    next[index] = value;
    form.setData('gaji', next);
  };

  const submit = (e: FormEvent) => {
    e.preventDefault();
    form.post('/gaji', {
      preserveScroll: true,
      onSuccess: () => setOpen(false),
    });
  };

  const columns: Column<GajiRow>[] = [
    { key: 'month', label: 'Bulan' },
    { key: 'year', label: 'Tahun' },
    { key: 'date', label: 'Tanggal Gaji', render: (r) => formatDate(r.date) },
    { key: 'items_count', label: 'Karyawan', align: 'right' },
    {
      key: 'items_sum_amount',
      label: 'Total Gaji',
      align: 'right',
      render: (r) => formatRupiah(r.items_sum_amount ?? 0),
    },
    {
      key: 'aksi',
      label: '',
      align: 'right',
      render: (r) => (
        <Stack direction="row" spacing={0.5} justifyContent="flex-end">
          <IconButton onClick={() => router.get(`/gaji/detail/${r.id}`)} aria-label="Lihat detail">
            <Eye size={16} />
          </IconButton>
          <IconButton color="error" onClick={() => setToDelete(r)} aria-label="Hapus">
            <Trash2 size={16} />
          </IconButton>
        </Stack>
      ),
    },
  ];

  return (
    <>
      <Head title="Gaji" />
      <PageHeader
        title="Gaji"
        subtitle="Kelola dan hitung gaji karyawan per periode"
        actions={
          <Button startIcon={<Plus size={18} />} onClick={openCreate}>
            Tambah Gaji
          </Button>
        }
      />

      <Card disableContentPadding>
        <DataTable
          columns={columns}
          rows={gajis.data}
          getRowId={(r) => r.id}
          emptyMessage="Belum ada data gaji."
        />
      </Card>
      <Box sx={{ px: 1 }}>
        <Pagination paginator={gajis} />
      </Box>

      <Dialog
        open={open}
        onClose={() => setOpen(false)}
        title="Tambah Gaji"
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
            label="Bulan"
            value={form.data.month}
            onChange={(e) => form.setData('month', e.target.value)}
            options={MONTHS.map((m) => ({ value: m, label: m }))}
            error={!!form.errors.month}
            helperText={form.errors.month}
          />
          <TextField
            label="Tahun"
            type="number"
            placeholder="2026"
            value={form.data.year}
            onChange={(e) => form.setData('year', e.target.value)}
            error={!!form.errors.year}
            helperText={form.errors.year}
          />
          <TextField
            label="Tanggal Gaji"
            type="date"
            InputLabelProps={{ shrink: true }}
            value={form.data.date}
            onChange={(e) => form.setData('date', e.target.value)}
            error={!!form.errors.date}
            helperText={form.errors.date}
          />

          <Divider />

          {karyawans.length === 0 ? (
            <Alert severity="info">Belum ada karyawan.</Alert>
          ) : (
            <>
              <Typography variant="subtitle2">Gaji per Karyawan</Typography>
              {karyawans.map((k, i) => (
                <Stack
                  key={k.id}
                  direction="row"
                  spacing={2}
                  alignItems="center"
                  justifyContent="space-between"
                >
                  <Typography variant="body2" sx={{ flex: 1 }}>
                    {k.name}
                  </Typography>
                  <Box sx={{ width: 180 }}>
                    <TextField
                      type="number"
                      inputProps={{ step: 'any', inputMode: 'decimal' }}
                      value={form.data.gaji[i] ?? ''}
                      onChange={(e) => setGajiAt(i, e.target.value)}
                    />
                  </Box>
                </Stack>
              ))}
            </>
          )}
        </Stack>
      </Dialog>

      <ConfirmDialog
        open={!!toDelete}
        title="Hapus Gaji"
        message={
          toDelete
            ? `Hapus gaji periode ${toDelete.month} ${toDelete.year}?`
            : ''
        }
        onClose={() => setToDelete(null)}
        onConfirm={() => {
          if (toDelete) {
            router.get(`/gaji/delete/${toDelete.id}`, {}, {
              preserveScroll: true,
              onFinish: () => setToDelete(null),
            });
          }
        }}
      />
    </>
  );
}
