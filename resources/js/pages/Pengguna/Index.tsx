import { useState, type FormEvent } from 'react';
import { Head, useForm, usePage, router } from '@inertiajs/react';
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
  Tooltip,
  type Column,
} from '@/ui';
import { Plus, Pencil, Trash2, KeyRound } from '@/icons';
import type { SharedProps } from '@/types';

interface UserRow extends Record<string, unknown> {
  id: number;
  name: string;
  username: string;
  level: number;
}

interface Props {
  users: UserRow[];
}

const LEVEL_OPTIONS = [
  { value: 1, label: 'Super Admin' },
  { value: 2, label: 'Admin' },
];

export default function Index({ users }: Props) {
  const { auth } = usePage<SharedProps>().props;

  const [open, setOpen] = useState(false);
  const [editing, setEditing] = useState<UserRow | null>(null);
  const [pwUser, setPwUser] = useState<UserRow | null>(null);
  const [toDelete, setToDelete] = useState<UserRow | null>(null);

  const form = useForm<{
    name: string;
    username: string;
    level: number | '';
    password: string;
    password_confirmation: string;
  }>({
    name: '',
    username: '',
    level: '',
    password: '',
    password_confirmation: '',
  });

  const pwForm = useForm({ password: '', password_confirmation: '' });

  const openCreate = () => {
    setEditing(null);
    form.reset();
    form.clearErrors();
    setOpen(true);
  };

  const openEdit = (u: UserRow) => {
    setEditing(u);
    form.clearErrors();
    form.setData({
      name: u.name,
      username: u.username,
      level: u.level,
      password: '',
      password_confirmation: '',
    });
    setOpen(true);
  };

  const submit = (e: FormEvent) => {
    e.preventDefault();
    const opts = {
      preserveScroll: true,
      onSuccess: () => {
        setOpen(false);
        form.reset();
      },
    };
    if (editing) form.post(`/users/update/${editing.id}`, opts);
    else form.post('/users', opts);
  };

  const openPassword = (u: UserRow) => {
    setPwUser(u);
    pwForm.reset();
    pwForm.clearErrors();
  };

  const submitPassword = (e: FormEvent) => {
    e.preventDefault();
    if (!pwUser) return;
    pwForm.post(`/users/update-password/${pwUser.id}`, {
      preserveScroll: true,
      onSuccess: () => {
        setPwUser(null);
        pwForm.reset();
      },
    });
  };

  const columns: Column<UserRow>[] = [
    { key: 'no', label: 'No', width: 60, render: (_r, i) => i + 1 },
    { key: 'name', label: 'Nama' },
    { key: 'username', label: 'Username' },
    {
      key: 'level',
      label: 'Level',
      render: (u) =>
        u.level === 1 ? (
          <StatusChip label="Super Admin" color="primary" />
        ) : (
          <StatusChip label="Admin" color="default" />
        ),
    },
    {
      key: 'aksi',
      label: '',
      align: 'right',
      render: (u) => (
        <Stack direction="row" spacing={0.5} justifyContent="flex-end">
          <Tooltip title="Edit">
            <IconButton onClick={() => openEdit(u)}>
              <Pencil size={16} />
            </IconButton>
          </Tooltip>
          <Tooltip title="Ubah Password">
            <IconButton color="info" onClick={() => openPassword(u)}>
              <KeyRound size={16} />
            </IconButton>
          </Tooltip>
          {auth.user?.username !== u.username && (
            <Tooltip title="Hapus">
              <IconButton color="error" onClick={() => setToDelete(u)}>
                <Trash2 size={16} />
              </IconButton>
            </Tooltip>
          )}
        </Stack>
      ),
    },
  ];

  return (
    <>
      <Head title="Pengguna" />
      <PageHeader
        title="Pengguna"
        actions={
          <Button startIcon={<Plus size={18} />} onClick={openCreate}>
            Tambah Pengguna
          </Button>
        }
      />

      <Card disableContentPadding>
        <DataTable
          columns={columns}
          rows={users}
          getRowId={(u) => u.id}
          emptyMessage="Belum ada pengguna."
        />
      </Card>

      {/* Modal tambah / edit */}
      <Dialog
        open={open}
        onClose={() => setOpen(false)}
        title={editing ? 'Edit Pengguna' : 'Tambah Pengguna'}
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
          <TextField
            label="Nama Pengguna"
            value={form.data.name}
            onChange={(e) => form.setData('name', e.target.value)}
            error={!!form.errors.name}
            helperText={form.errors.name}
            autoFocus
          />
          <TextField
            label="Username"
            value={form.data.username}
            onChange={(e) => form.setData('username', e.target.value)}
            error={!!form.errors.username}
            helperText={form.errors.username}
          />
          <Select
            label="Level"
            placeholder="Pilih Level"
            value={form.data.level}
            onChange={(e) =>
              form.setData('level', e.target.value === '' ? '' : Number(e.target.value))
            }
            options={LEVEL_OPTIONS}
            error={!!form.errors.level}
            helperText={form.errors.level}
          />
          {!editing && (
            <>
              <TextField
                label="Password"
                type="password"
                value={form.data.password}
                onChange={(e) => form.setData('password', e.target.value)}
                error={!!form.errors.password}
                helperText={form.errors.password}
              />
              <TextField
                label="Konfirmasi Password"
                type="password"
                value={form.data.password_confirmation}
                onChange={(e) => form.setData('password_confirmation', e.target.value)}
              />
            </>
          )}
        </Stack>
      </Dialog>

      {/* Modal ubah password */}
      <Dialog
        open={!!pwUser}
        onClose={() => setPwUser(null)}
        title="Ubah Password"
        actions={
          <>
            <Button variant="text" color="secondary" onClick={() => setPwUser(null)}>
              Batal
            </Button>
            <Button onClick={submitPassword} disabled={pwForm.processing}>
              Simpan
            </Button>
          </>
        }
      >
        <Stack component="form" spacing={2} onSubmit={submitPassword} sx={{ pt: 1 }}>
          <TextField
            label="Password Baru"
            type="password"
            value={pwForm.data.password}
            onChange={(e) => pwForm.setData('password', e.target.value)}
            error={!!pwForm.errors.password}
            helperText={pwForm.errors.password}
            autoFocus
          />
          <TextField
            label="Konfirmasi Password Baru"
            type="password"
            value={pwForm.data.password_confirmation}
            onChange={(e) => pwForm.setData('password_confirmation', e.target.value)}
          />
        </Stack>
      </Dialog>

      <ConfirmDialog
        open={!!toDelete}
        title="Hapus Pengguna"
        message={`Apakah anda yakin menghapus pengguna "${toDelete?.name}"?`}
        onClose={() => setToDelete(null)}
        onConfirm={() => {
          if (toDelete)
            router.get(
              `/users/delete/${toDelete.id}`,
              {},
              { preserveScroll: true, onFinish: () => setToDelete(null) },
            );
        }}
      />
    </>
  );
}
