import { useState, type FormEvent } from 'react';
import { Head, router } from '@inertiajs/react';
import {
  Box,
  Button,
  Card,
  DataTable,
  PageHeader,
  Select,
  StatCard,
  Stack,
  TextField,
  Typography,
  type Column,
} from '@/ui';
import { Search } from '@/icons';
import { formatRupiah, formatDate } from '@/lib/format';

interface TokoOption {
  id: number;
  name: string;
}

interface ReportRow extends Record<string, unknown> {
  name: string;
  description: string;
  date: string;
  source: string;
  amount: number | string;
  type: 'debit' | 'credit';
}

interface Props {
  tokos: TokoOption[];
  results: ReportRow[];
  totalKredit: number;
  totalDebit: number;
  filters: { from: string | null; to: string | null; toko_id: number | string | null };
}

export default function Toko({ tokos, results, totalKredit, totalDebit, filters }: Props) {
  const [tokoId, setTokoId] = useState(filters.toko_id != null ? String(filters.toko_id) : '');
  const [from, setFrom] = useState(filters.from ?? '');
  const [to, setTo] = useState(filters.to ?? '');
  const net = totalKredit - totalDebit;

  const submit = (e: FormEvent) => {
    e.preventDefault();
    router.get(
      '/toko-reports',
      { toko_id: tokoId, from, to },
      { preserveState: true, preserveScroll: true, replace: true },
    );
  };

  const columns: Column<ReportRow>[] = [
    { key: 'no', label: 'No.', width: 60, render: (_r, i) => i + 1 },
    { key: 'name', label: 'Nama' },
    { key: 'description', label: 'Keterangan' },
    { key: 'date', label: 'Tanggal', render: (r) => formatDate(r.date) },
    { key: 'source', label: 'Sumber' },
    {
      key: 'debit',
      label: 'Debit',
      align: 'right',
      render: (r) => (
        <Typography component="span" variant="body2" color="error.main">
          {formatRupiah(r.type === 'debit' ? r.amount : 0)}
        </Typography>
      ),
    },
    {
      key: 'credit',
      label: 'Credit',
      align: 'right',
      render: (r) => (
        <Typography component="span" variant="body2" color="success.main">
          {formatRupiah(r.type === 'credit' ? r.amount : 0)}
        </Typography>
      ),
    },
  ];

  return (
    <>
      <Head title="Laporan Toko" />
      <PageHeader title="Laporan Toko" subtitle="Ringkasan pemasukan & pengeluaran per toko" />

      <Card sx={{ mb: 3 }}>
        <Stack
          component="form"
          direction={{ xs: 'column', sm: 'row' }}
          spacing={2}
          alignItems={{ sm: 'flex-end' }}
          onSubmit={submit}
        >
          <Select
            label="Toko"
            value={tokoId}
            onChange={(e) => setTokoId(e.target.value)}
            placeholder="Pilih Toko"
            options={tokos.map((t) => ({ value: String(t.id), label: t.name }))}
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
            Filter Laporan
          </Button>
        </Stack>
      </Card>

      <Box
        sx={{
          display: 'grid',
          gap: 2,
          gridTemplateColumns: { xs: '1fr', sm: 'repeat(3, 1fr)' },
          mb: 3,
        }}
      >
        <StatCard label="Total Pemasukan" value={formatRupiah(totalKredit)} color="success" />
        <StatCard label="Total Pengeluaran" value={formatRupiah(totalDebit)} color="error" />
        <StatCard label="Selisih (Net)" value={formatRupiah(net)} color={net >= 0 ? 'primary' : 'error'} />
      </Box>

      <Card title="Rincian Transaksi" disableContentPadding>
        <DataTable
          columns={columns}
          rows={results}
          getRowId={(_r, i) => i}
          emptyMessage="Tidak ada data laporan."
        />
      </Card>
    </>
  );
}
