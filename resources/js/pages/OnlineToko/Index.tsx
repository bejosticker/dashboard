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
  Select,
  Stack,
  StatusChip,
  TextField,
  type Column,
} from '@/ui';
import { Plus, Pencil, Trash2 } from '@/icons';

interface OnlineTokoRow extends Record<string, unknown> {
  id: number;
  name: string;
  toko_id: number;
  toko_name: string | null;
  description: string | null;
  vendor: string;
}

interface TokoOption {
  id: number;
  name: string;
}

interface Props {
  onlineTokos: OnlineTokoRow[];
  tokos: TokoOption[];
}

const VENDOR_OPTIONS = [
  { value: 'Tiktok', label: 'Tiktok' },
  { value: 'Shopee', label: 'Shopee' },
  { value: 'Lazada', label: 'Lazada' },
  { value: 'Youtube', label: 'Youtube' },
  { value: 'Blibli', label: 'Blibli' },
];

export default function Index({ onlineTokos, tokos }: Props) {
  const [open, setOpen] = useState(false);
  const [editing, setEditing] = useState<OnlineTokoRow | null>(null);
  const [toDelete, setToDelete] = useState<OnlineTokoRow | null>(null);

  const form = useForm({
    name: '',
    toko_id: '' as number | '',
    description: '',
    vendor: 'Tiktok',
  });

  const openCreate = () => {
    setEditing(null);
    form.reset();
    form.clearErrors();
    setOpen(true);
  };

  const openEdit = (o: OnlineTokoRow) => {
    setEditing(o);
    form.clearErrors();
    form.setData({
      name: o.name,
      toko_id: o.toko_id,
      description: o.description ?? '',
      vendor: o.vendor,
    });
    setOpen(true);
  };

  const submit = (e: FormEvent) => {
    e.preventDefault();
    const opts = { onSuccess: () => setOpen(false), preserveScroll: true };
    if (editing) form.post(`/online-toko/update/${editing.id}`, opts);
    else form.post('/online-toko', opts);
  };

  const columns: Column<OnlineTokoRow>[] = [
    { key: 'name', label: 'Nama' },
    { key: 'toko_name', label: 'Nama Toko', render: (o) => o.toko_name ?? '-' },
    { key: 'description', label: 'Keterangan', render: (o) => o.description || '-' },
    { key: 'vendor', label: 'Vendor', render: (o) => <StatusChip label={o.vendor} color="info" /> },
    {
      key: 'aksi',
      label: '',
      align: 'right',
      render: (o) => (
        <Stack direction="row" spacing={0.5} justifyContent="flex-end">
          <IconButton onClick={() => openEdit(o)}>
            <Pencil size={16} />
          </IconButton>
          <IconButton color="error" onClick={() => setToDelete(o)}>
            <Trash2 size={16} />
          </IconButton>
        </Stack>
      ),
    },
  ];

  return (
    <>
      <Head title="Toko Market Online" />
      <PageHeader
        title="Toko Market Online"
        actions={
          <Button startIcon={<Plus size={18} />} onClick={openCreate}>
            Tambah Toko
          </Button>
        }
      />

      <Card disableContentPadding>
        <DataTable
          columns={columns}
          rows={onlineTokos}
          getRowId={(o) => o.id}
          emptyMessage="Belum ada data toko market online."
        />
      </Card>

      <Dialog
        open={open}
        onClose={() => setOpen(false)}
        title={editing ? 'Edit Toko' : 'Tambah Toko Market Online'}
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
            label="Toko"
            placeholder="Pilih Toko"
            value={form.data.toko_id}
            onChange={(e) => form.setData('toko_id', e.target.value === '' ? '' : Number(e.target.value))}
            options={tokos.map((t) => ({ value: t.id, label: t.name }))}
            error={!!form.errors.toko_id}
            helperText={form.errors.toko_id}
          />
          <TextField
            label="Nama Toko"
            placeholder="Masukkan nama..."
            value={form.data.name}
            onChange={(e) => form.setData('name', e.target.value)}
            error={!!form.errors.name}
            helperText={form.errors.name}
            autoFocus
          />
          <TextField
            label="Keterangan (opsional)"
            placeholder="Keterangan..."
            value={form.data.description}
            onChange={(e) => form.setData('description', e.target.value)}
            error={!!form.errors.description}
            helperText={form.errors.description}
          />
          <Select
            label="Vendor"
            value={form.data.vendor}
            onChange={(e) => form.setData('vendor', e.target.value)}
            options={VENDOR_OPTIONS}
            error={!!form.errors.vendor}
            helperText={form.errors.vendor}
          />
        </Stack>
      </Dialog>

      <ConfirmDialog
        open={!!toDelete}
        title="Hapus Toko"
        message={`Apakah anda yakin menghapus toko "${toDelete?.name}"?`}
        onClose={() => setToDelete(null)}
        onConfirm={() => {
          if (toDelete)
            router.get(
              `/online-toko/delete/${toDelete.id}`,
              {},
              { preserveScroll: true, onFinish: () => setToDelete(null) },
            );
        }}
      />
    </>
  );
}
