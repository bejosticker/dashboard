import { Head, router } from '@inertiajs/react';
import {
  Box,
  Card,
  DataTable,
  PageHeader,
  Pagination,
  Select,
  type Column,
} from '@/ui';
import { formatRupiah, formatDate } from '@/lib/format';
import type { Paginator } from '@/types';

interface GajiItemRow extends Record<string, unknown> {
  id: number;
  amount: number | string;
  karyawan: { id: number; name: string } | null;
  gaji: { id: number; month: string; year: number | string; date: string } | null;
}

interface Karyawan {
  id: number;
  name: string;
}

interface Props {
  gajis: Paginator<GajiItemRow>;
  karyawans: Karyawan[];
  filters: { karyawan_id: number };
}

export default function Riwayat({ gajis, karyawans, filters }: Props) {
  const changeKaryawan = (value: string) => {
    router.get('/gaji-history', { karyawan_id: value }, {
      preserveState: true,
      replace: true,
    });
  };

  const columns: Column<GajiItemRow>[] = [
    { key: 'month', label: 'Bulan', render: (r) => r.gaji?.month ?? '-' },
    { key: 'year', label: 'Tahun', render: (r) => r.gaji?.year ?? '-' },
    { key: 'date', label: 'Tanggal Gaji', render: (r) => formatDate(r.gaji?.date) },
    { key: 'karyawan', label: 'Karyawan', render: (r) => r.karyawan?.name ?? '-' },
    { key: 'amount', label: 'Gaji', align: 'right', render: (r) => formatRupiah(r.amount) },
  ];

  return (
    <>
      <Head title="Riwayat Gaji" />
      <PageHeader title="Riwayat Gaji" subtitle="Riwayat gaji per karyawan" />

      <Card sx={{ mb: 3 }}>
        <Box sx={{ maxWidth: 360 }}>
          <Select
            label="Karyawan"
            value={filters.karyawan_id || ''}
            onChange={(e) => changeKaryawan(e.target.value)}
            options={karyawans.map((k) => ({ value: k.id, label: k.name }))}
            placeholder="Pilih Karyawan"
          />
        </Box>
      </Card>

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
    </>
  );
}
