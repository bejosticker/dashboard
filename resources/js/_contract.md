# Kontrak Revamp Inertia + React + TS + MUI (WAJIB dibaca tiap agent)

Kamu memigrasikan SATU fitur dashms dari Blade/Livewire ke Inertia React + TypeScript.
Fondasi sudah jadi & terverifikasi. IKUTI kontrak ini persis agar semua halaman konsisten.

## Aturan mutlak
1. Halaman **HANYA** boleh import komponen UI dari `@/ui`, ikon dari `@/icons`, util dari `@/lib/format`, tipe dari `@/types`. **DILARANG** import langsung dari `@mui/material` atau `lucide-react` di file halaman (ESLint akan menolak).
2. **JANGAN** mengubah `routes/web.php`, `resources/js/types/index.ts`, `resources/js/ui/**`, `resources/js/theme/**`, `resources/js/layouts/**`, `resources/js/app.tsx`. Route yang diperlukan sudah ada.
3. Definisikan tipe khusus halaman **secara lokal di file halaman** (interface di atas komponen). Hanya `Paginator`, `PaginationLink`, `SharedProps`, `AuthUser`, `MenuItem` yang diimpor dari `@/types`.
4. Semua teks UI Bahasa Indonesia. Format uang pakai `formatRupiah()` dari `@/lib/format`.
5. Setelah selesai, jangan jalankan `tsc`/build (integrasi dilakukan terpusat). Pastikan saja kode konsisten dgn contoh.

## Referensi WAJIB dibaca sebelum menulis
- `resources/js/pages/Beranda.tsx` — contoh halaman jadi.
- `resources/js/ui/index.ts` dan tiap file di `resources/js/ui/` — API pasti (props).
- File fitur yang kamu pegang: `app/Http/Controllers/XController.php`, blade di `resources/views/**`, dan (bila ada) komponen Livewire `app/Livewire/*.php` + blade-nya.

## API `@/ui` (props inti)
- `Button` — extends MUI Button. default `variant="contained"`. `color`, `startIcon`, `onClick`, `type`, `disabled`.
- `IconButton` — untuk aksi ikon (edit/hapus di tabel).
- `TextField` — default `size="small"`, `fullWidth`. Untuk angka desimal: `type="number" inputProps={{ step: 'any', inputMode: 'decimal' }}`. Props error: `error`, `helperText`.
- `Select` — `options: {value,label}[]`, `placeholder?`, `label`, `value`, `onChange`. (berbasis TextField select)
- `Card` — `title?`, `action?`, `disableContentPadding?` (untuk tabel full-bleed).
- `Dialog` — modal: `open`, `onClose`, `title`, `actions` (ReactNode tombol), `maxWidth?`. Isi = children (form).
- `ConfirmDialog` — `open`, `onConfirm`, `onClose`, `title?`, `message?`, `confirmColor?` (default 'error'), `loading?`.
- `DataTable<T>` — `columns: Column<T>[]`, `rows: T[]`, `getRowId?`, `emptyMessage?`. `Column = { key, label, align?, render?(row,i), width? }`.
- `Pagination` — `paginator: Paginator<T>` (dari `->paginate()`), auto-navigate Inertia + pertahankan query.
- `PageHeader` — `title`, `subtitle?`, `actions?`.
- `StatCard` — `label`, `value`, `icon?`, `color?`.
- `StatusChip` — `label`, `color?`.
- Primitif: `Box`, `Stack`, `Typography`, `Divider`, `Grid`, `Chip`, `Alert`, `MenuItem`, `InputAdornment`, `Tooltip`, `Checkbox`, `FormControlLabel`, dll (dari `@/ui`). **Untuk layout PREFER `Stack` & `Box`** (hindari `Grid` bila bisa).

## `@/icons`
Ikon Lucide: `Plus, Pencil, Trash2, X, Search, Save, Download, FileSpreadsheet, KeyRound, Eye` dll. Import: `import { Plus, Pencil, Trash2 } from '@/icons'`. Ukuran: `<Plus size={18} />`.

## Konversi controller (CRUD sederhana)
- Ubah HANYA method `index()`: `return view(...)` → `return Inertia::render('Namahalaman', [...props...])`. Tambahkan `use Inertia\Inertia;`.
- Sertakan SEMUA data yang dulu dipakai blade: paginator utama + opsi dropdown (mis. daftar toko/produk untuk select di modal) + agregat.
- **JANGAN** ubah `store()`, `update()`, `destroy()` untuk CRUD sederhana — mereka sudah `redirect()->back()->with('success', ...)` yang kompatibel dgn Inertia (props & flash otomatis ter-refresh). Validasi controller yang gagal otomatis muncul sebagai `errors` di `useForm`.
- Nama komponen Inertia = path di `resources/js/pages`. Contoh `Inertia::render('Toko/Index', ...)` → file `resources/js/pages/Toko/Index.tsx`.

## Pola halaman CRUD (tabel + modal + hapus)
Skeleton (adaptasi sesuai field fitur):

```tsx
import { useState, type FormEvent } from 'react';
import { Head, useForm, router } from '@inertiajs/react';
import {
  Box, Button, Card, ConfirmDialog, DataTable, Dialog, IconButton, PageHeader,
  Pagination, Stack, TextField, type Column,
} from '@/ui';
import { Plus, Pencil, Trash2, Search } from '@/icons';
import type { Paginator } from '@/types';

interface Toko extends Record<string, unknown> { id: number; name: string; }
interface Props { tokos: Paginator<Toko>; filters: { search?: string }; }

export default function Index({ tokos, filters }: Props) {
  const [open, setOpen] = useState(false);
  const [editing, setEditing] = useState<Toko | null>(null);
  const [toDelete, setToDelete] = useState<Toko | null>(null);
  const form = useForm({ name: '' });

  const openCreate = () => { setEditing(null); form.reset(); form.clearErrors(); setOpen(true); };
  const openEdit = (t: Toko) => { setEditing(t); form.setData({ name: t.name }); setOpen(true); };

  const submit = (e: FormEvent) => {
    e.preventDefault();
    const opts = { onSuccess: () => setOpen(false), preserveScroll: true };
    if (editing) form.post(`/toko/update/${editing.id}`, opts);
    else form.post('/toko', opts);
  };

  const columns: Column<Toko>[] = [
    { key: 'name', label: 'Nama' },
    { key: 'aksi', label: '', align: 'right', render: (t) => (
      <Stack direction="row" spacing={0.5} justifyContent="flex-end">
        <IconButton onClick={() => openEdit(t)}><Pencil size={16} /></IconButton>
        <IconButton color="error" onClick={() => setToDelete(t)}><Trash2 size={16} /></IconButton>
      </Stack>
    ) },
  ];

  return (
    <>
      <Head title="Toko" />
      <PageHeader title="Toko" actions={<Button startIcon={<Plus size={18} />} onClick={openCreate}>Tambah</Button>} />
      <Card disableContentPadding>
        <DataTable columns={columns} rows={tokos.data} getRowId={(t) => t.id} />
      </Card>
      <Box sx={{ px: 1 }}><Pagination paginator={tokos} /></Box>

      <Dialog open={open} onClose={() => setOpen(false)} title={editing ? 'Edit Toko' : 'Tambah Toko'}
        actions={<>
          <Button variant="text" color="secondary" onClick={() => setOpen(false)}>Batal</Button>
          <Button onClick={submit} disabled={form.processing}>Simpan</Button>
        </>}>
        <Stack component="form" spacing={2} onSubmit={submit} sx={{ pt: 1 }}>
          <TextField label="Nama" value={form.data.name} onChange={(e) => form.setData('name', e.target.value)}
            error={!!form.errors.name} helperText={form.errors.name} autoFocus />
        </Stack>
      </Dialog>

      <ConfirmDialog open={!!toDelete} title="Hapus Toko" message={`Hapus "${toDelete?.name}"?`}
        onClose={() => setToDelete(null)}
        onConfirm={() => { if (toDelete) router.get(`/toko/delete/${toDelete.id}`, {}, { preserveScroll: true, onFinish: () => setToDelete(null) }); }} />
    </>
  );
}
```

## Pencarian (bila fitur punya search)
Debounce sederhana + `router.get(window.location.pathname, { search }, { preserveState: true, replace: true })`. Atau field `defaultValue={filters.search}` + submit on Enter. Pertahankan query lewat `Pagination` (sudah otomatis).

## Hapus
Route hapus adalah **GET** `/x/delete/{id}` (jangan diubah). Panggil via `router.get(...)` setelah `ConfirmDialog`.

## Form kompleks (Sales, CetakSales, Kulak, PengambilanBahan, OnlineAd)
Ini menggantikan komponen Livewire (mis. `app/Livewire/SalesForm.php` + blade-nya). Langkah:
1. Baca komponen Livewire terkait untuk memahami: field, perhitungan subtotal/total, dan logika `save()`.
2. **Port logika `save()` Livewire ke method `store()` di controller** (bila belum ada). Route POST sudah disediakan. Bungkus validasi (gunakan `gt:0` untuk jumlah/quantity agar desimal spt 0.5 diterima; jangan `min:1`).
3. Halaman React: state baris item (array), hitung subtotal/total di client (mirror logika Livewire), submit via `useForm().post('/sales', ...)`. Input jumlah desimal WAJIB pakai `inputProps={{ step: 'any', inputMode: 'decimal' }}`.
4. Kolom kuantitas di beberapa tabel sudah `decimal` (sales_items, kulak_item, dst) — dukung desimal.

## Output
Ubah/utuhkan: (a) controller `index()` (+ `store()` bila form kompleks), (b) file `resources/js/pages/<Fitur>/*.tsx`, (c) `app/Http/Requests/*` bila kamu pakai Form Request (opsional; boleh validasi inline di controller). Laporkan ringkas file yang dibuat/diubah.
```
