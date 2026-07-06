import type { ReactNode } from 'react';
import {
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Box,
  Typography,
} from '@mui/material';

export interface Column<T> {
  /** Kunci unik kolom. */
  key: string;
  /** Judul header kolom. */
  label: ReactNode;
  /** Perataan sel. */
  align?: 'left' | 'right' | 'center';
  /** Render kustom; bila kosong, tampilkan row[key]. */
  render?: (row: T, index: number) => ReactNode;
  width?: number | string;
}

export interface DataTableProps<T> {
  columns: Column<T>[];
  rows: T[];
  getRowId?: (row: T, index: number) => string | number;
  emptyMessage?: string;
}

/**
 * Tabel data generik. Definisikan kolom + baris; render kustom lewat `render`.
 * Contoh:
 *   <DataTable
 *     columns={[
 *       { key: 'name', label: 'Nama' },
 *       { key: 'total', label: 'Total', align: 'right', render: r => formatRupiah(r.total) },
 *       { key: 'aksi', label: '', align: 'right', render: r => <IconButton>...</IconButton> },
 *     ]}
 *     rows={items.data}
 *   />
 */
export function DataTable<T extends Record<string, unknown>>({
  columns,
  rows,
  getRowId,
  emptyMessage = 'Tidak ada data.',
}: DataTableProps<T>) {
  return (
    <TableContainer sx={{ overflowX: 'auto' }}>
      <Table>
        <TableHead>
          <TableRow>
            {columns.map((col) => (
              <TableCell key={col.key} align={col.align} sx={{ width: col.width }}>
                {col.label}
              </TableCell>
            ))}
          </TableRow>
        </TableHead>
        <TableBody>
          {rows.length === 0 ? (
            <TableRow>
              <TableCell colSpan={columns.length}>
                <Box sx={{ py: 4, textAlign: 'center' }}>
                  <Typography variant="body2" color="text.secondary">
                    {emptyMessage}
                  </Typography>
                </Box>
              </TableCell>
            </TableRow>
          ) : (
            rows.map((row, index) => (
              <TableRow key={getRowId ? getRowId(row, index) : index} hover>
                {columns.map((col) => (
                  <TableCell key={col.key} align={col.align}>
                    {col.render ? col.render(row, index) : (row[col.key] as ReactNode)}
                  </TableCell>
                ))}
              </TableRow>
            ))
          )}
        </TableBody>
      </Table>
    </TableContainer>
  );
}
