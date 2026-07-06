import { Head, router } from '@inertiajs/react';
import {
  Alert,
  Box,
  Button,
  Card,
  DataTable,
  PageHeader,
  StatCard,
  type Column,
} from '@/ui';
import { ChevronLeft } from '@/icons';
import { formatRupiah, formatDate } from '@/lib/format';

interface GajiItem extends Record<string, unknown> {
  id: number;
  amount: number | string;
  karyawan: { id: number; name: string } | null;
}

interface GajiDetail {
  id: number;
  month: string;
  year: number | string;
  date: string;
  items_count: number;
  items_sum_amount: number | string | null;
  items: GajiItem[];
}

interface Props {
  gaji: GajiDetail | null;
}

export default function Detail({ gaji }: Props) {
  if (!gaji) {
    return (
      <>
        <Head title="Detail Gaji" />
        <PageHeader
          title="Detail Gaji"
          actions={
            <Button variant="text" onClick={() => router.get('/gaji')}>
              Kembali
            </Button>
          }
        />
        <Alert severity="error">Data gaji tidak ditemukan.</Alert>
      </>
    );
  }

  const columns: Column<GajiItem>[] = [
    { key: 'no', label: 'No.', width: 60, render: (_r, i) => i + 1 },
    { key: 'name', label: 'Nama', render: (r) => r.karyawan?.name ?? '-' },
    { key: 'amount', label: 'Gaji', align: 'right', render: (r) => formatRupiah(r.amount) },
  ];

  return (
    <>
      <Head title={`Detail Gaji ${gaji.month} ${gaji.year}`} />
      <PageHeader
        title={`Gaji ${gaji.month} ${gaji.year}`}
        subtitle={formatDate(gaji.date)}
        actions={
          <Button
            variant="text"
            startIcon={<ChevronLeft size={18} />}
            onClick={() => router.get('/gaji')}
          >
            Kembali
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
        <StatCard label="Jumlah Karyawan" value={gaji.items_count} />
        <StatCard
          label="Total Gaji"
          value={formatRupiah(gaji.items_sum_amount ?? 0)}
          color="success"
        />
      </Box>

      <Card disableContentPadding>
        <DataTable
          columns={columns}
          rows={gaji.items}
          getRowId={(r) => r.id}
          emptyMessage="Belum ada rincian gaji."
        />
      </Card>
    </>
  );
}
