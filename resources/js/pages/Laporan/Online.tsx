import { useState, type FormEvent } from 'react';
import { Head, router } from '@inertiajs/react';
import {
  Box,
  Button,
  Card,
  DataTable,
  PageHeader,
  StatCard,
  Stack,
  TextField,
  Typography,
  type Column,
} from '@/ui';
import { Search } from '@/icons';
import { formatRupiah } from '@/lib/format';

interface OnlineEntry {
  amount: number | string;
  date: string;
  type: 'credit' | 'debit';
}

interface Vendor {
  vendor: string;
  reports: OnlineEntry[];
}

interface OnlineReport {
  name: string;
  vendors: Vendor[];
}

interface VendorRow extends Record<string, unknown> {
  vendor: string;
  kredit: number;
  iklan: number;
}

interface Props {
  reports: OnlineReport[];
  filters: { from: string | null; to: string | null };
}

const toNum = (v: unknown) => {
  const n = Number(v ?? 0);
  return Number.isFinite(n) ? n : 0;
};

const sumBy = (entries: OnlineEntry[], type: 'credit' | 'debit') =>
  entries.filter((e) => e.type === type).reduce((acc, e) => acc + toNum(e.amount), 0);

export default function Online({ reports, filters }: Props) {
  const [from, setFrom] = useState(filters.from ?? '');
  const [to, setTo] = useState(filters.to ?? '');

  const submit = (e: FormEvent) => {
    e.preventDefault();
    router.get('/online-reports', { from, to }, { preserveState: true, preserveScroll: true, replace: true });
  };

  let grandKredit = 0;
  let grandIklan = 0;
  for (const report of reports) {
    for (const v of report.vendors) {
      grandKredit += sumBy(v.reports, 'credit');
      grandIklan += sumBy(v.reports, 'debit');
    }
  }

  const columns: Column<VendorRow>[] = [
    { key: 'vendor', label: 'Market' },
    {
      key: 'kredit',
      label: 'Total Kredit',
      align: 'right',
      render: (r) => (
        <Typography component="span" variant="body2" color="success.main">
          {formatRupiah(r.kredit)}
        </Typography>
      ),
    },
    {
      key: 'iklan',
      label: 'Iklan',
      align: 'right',
      render: (r) => (
        <Typography component="span" variant="body2" color="error.main">
          {formatRupiah(r.iklan)}
        </Typography>
      ),
    },
  ];

  return (
    <>
      <Head title="Laporan Market Online" />
      <PageHeader title="Laporan Market Online" subtitle="Ringkasan pemasukan & iklan per market online" />

      <Card sx={{ mb: 3 }}>
        <Stack
          component="form"
          direction={{ xs: 'column', sm: 'row' }}
          spacing={2}
          alignItems={{ sm: 'flex-end' }}
          onSubmit={submit}
        >
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
        <StatCard label="Total Pemasukan Online" value={formatRupiah(grandKredit)} color="success" />
        <StatCard label="Total Iklan" value={formatRupiah(grandIklan)} color="error" />
        <StatCard
          label="Selisih (Net)"
          value={formatRupiah(grandKredit - grandIklan)}
          color={grandKredit - grandIklan >= 0 ? 'primary' : 'error'}
        />
      </Box>

      {reports.length === 0 ? (
        <Card>
          <Typography variant="body2" color="text.secondary" sx={{ textAlign: 'center', py: 2 }}>
            Tidak ada data laporan.
          </Typography>
        </Card>
      ) : (
        <Stack spacing={3}>
          {reports.map((report) => {
            const rows: VendorRow[] = report.vendors.map((v) => ({
              vendor: v.vendor,
              kredit: sumBy(v.reports, 'credit'),
              iklan: sumBy(v.reports, 'debit'),
            }));
            return (
              <Card key={report.name} title={report.name} disableContentPadding>
                <DataTable
                  columns={columns}
                  rows={rows}
                  getRowId={(r) => r.vendor}
                  emptyMessage="Tidak ada vendor."
                />
              </Card>
            );
          })}
        </Stack>
      )}
    </>
  );
}
