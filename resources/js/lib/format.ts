/**
 * Format angka ke Rupiah, mis. 15000 -> "Rp 15.000".
 * Port dari helper `formatRupiah()` di app/Helpers.php.
 */
export function formatRupiah(value: number | string | null | undefined): string {
  const n = Number(value ?? 0);
  return 'Rp ' + (Number.isFinite(n) ? n : 0).toLocaleString('id-ID');
}

/** Format angka biasa dengan pemisah ribuan (id-ID). */
export function formatNumber(value: number | string | null | undefined): string {
  const n = Number(value ?? 0);
  return (Number.isFinite(n) ? n : 0).toLocaleString('id-ID');
}

/** Format tanggal ISO (YYYY-MM-DD) -> "6 Jul 2026". */
export function formatDate(value: string | null | undefined): string {
  if (!value) return '-';
  const d = new Date(value);
  if (Number.isNaN(d.getTime())) return value;
  return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
}
