import { Head } from '@inertiajs/react';
import {
  Box,
  Card,
  DataTable,
  PageHeader,
  StatCard,
  StatusChip,
  Typography,
  type Column,
} from '@/ui';
import { Banknote, Upload, Download, Wallet } from '@/icons';
import { formatRupiah, formatDate } from '@/lib/format';

interface TokoRow {
  id: number;
  name: string;
  credit: number;
  debit: number;
  online: number;
}

interface ProductRow extends Record<string, unknown> {
  id: number;
  name: string;
  stock_cm: number;
  minimum_stock_cm: number;
}

interface BerandaProps {
  tokos: TokoRow[];
  report: { credit: number; debit: number; products: ProductRow[] };
  kulak: number;
  period: { from: string; to: string };
}

export default function Beranda({ tokos, report, kulak, period }: BerandaProps) {
  const net = report.credit - report.debit;

  const tokoColumns: Column<TokoRow & Record<string, unknown>>[] = [
    { key: 'name', label: 'Toko' },
    { key: 'credit', label: 'Pemasukan', align: 'right', render: (r) => formatRupiah(r.credit) },
    { key: 'debit', label: 'Pengeluaran', align: 'right', render: (r) => formatRupiah(r.debit) },
    { key: 'online', label: 'Online', align: 'right', render: (r) => formatRupiah(r.online) },
  ];

  const productColumns: Column<ProductRow>[] = [
    { key: 'name', label: 'Produk' },
    { key: 'stock_cm', label: 'Stok (cm)', align: 'right', render: (r) => r.stock_cm.toLocaleString('id-ID') },
    {
      key: 'status',
      label: 'Status',
      align: 'right',
      render: () => <StatusChip label="Stok Menipis" color="warning" />,
    },
  ];

  return (
    <>
      <Head title="Beranda" />
      <PageHeader
        title="Beranda"
        subtitle={`Periode ${formatDate(period.from)} – ${formatDate(period.to)}`}
      />

      <Box
        sx={{
          display: 'grid',
          gap: 2,
          gridTemplateColumns: { xs: '1fr', sm: '1fr 1fr', lg: 'repeat(4, 1fr)' },
          mb: 3,
        }}
      >
        <StatCard label="Pemasukan" value={formatRupiah(report.credit)} color="success" icon={<Banknote size={22} />} />
        <StatCard label="Pengeluaran" value={formatRupiah(report.debit)} color="error" icon={<Upload size={22} />} />
        <StatCard label="Pembelian Bahan" value={formatRupiah(kulak)} color="warning" icon={<Download size={22} />} />
        <StatCard label="Selisih (Net)" value={formatRupiah(net)} color={net >= 0 ? 'primary' : 'error'} icon={<Wallet size={22} />} />
      </Box>

      <Box sx={{ display: 'grid', gap: 3, gridTemplateColumns: { xs: '1fr', lg: '1fr 1fr' } }}>
        <Card title="Ringkasan Toko" disableContentPadding>
          <DataTable
            columns={tokoColumns}
            rows={tokos as (TokoRow & Record<string, unknown>)[]}
            getRowId={(r) => r.id}
            emptyMessage="Belum ada toko."
          />
        </Card>

        <Card title="Stok Menipis" disableContentPadding>
          <DataTable
            columns={productColumns}
            rows={report.products}
            getRowId={(r) => r.id}
            emptyMessage="Semua stok aman."
          />
        </Card>
      </Box>

      {report.products.length > 0 && (
        <Typography variant="caption" color="text.secondary" sx={{ display: 'block', mt: 1 }}>
          {report.products.length} produk di bawah stok minimum.
        </Typography>
      )}
    </>
  );
}
