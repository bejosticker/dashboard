import { useState, type MouseEvent } from 'react';
import {
  Avatar,
  Box,
  Divider,
  IconButton,
  ListItemIcon,
  Menu,
  MenuItem,
  Toolbar,
  Typography,
} from '@mui/material';
import { LogOut, Menu as MenuIcon } from 'lucide-react';
import { usePage } from '@inertiajs/react';
import type { SharedProps } from '@/types';

export function Navbar({ onMenuClick }: { onMenuClick: () => void }) {
  const { props } = usePage<SharedProps>();
  const user = props.auth?.user;
  const [anchor, setAnchor] = useState<null | HTMLElement>(null);

  const openMenu = (e: MouseEvent<HTMLElement>) => setAnchor(e.currentTarget);
  const closeMenu = () => setAnchor(null);

  return (
    <Toolbar sx={{ gap: 1 }}>
      <IconButton
        edge="start"
        onClick={onMenuClick}
        sx={{ display: { lg: 'none' } }}
        aria-label="Buka menu"
      >
        <MenuIcon size={22} />
      </IconButton>

      <Box sx={{ flexGrow: 1 }} />

      <IconButton onClick={openMenu} size="small" aria-label="Menu pengguna">
        <Avatar src="/assets/img/logo.png" sx={{ width: 36, height: 36 }} />
      </IconButton>

      <Menu
        anchorEl={anchor}
        open={Boolean(anchor)}
        onClose={closeMenu}
        anchorOrigin={{ vertical: 'bottom', horizontal: 'right' }}
        transformOrigin={{ vertical: 'top', horizontal: 'right' }}
      >
        <Box sx={{ px: 2, py: 1 }}>
          <Typography variant="subtitle2">{user?.name ?? 'Pengguna'}</Typography>
          <Typography variant="caption" color="text.secondary">
            {user?.level === 1 ? 'Super Admin' : 'Admin'}
          </Typography>
        </Box>
        <Divider />
        <MenuItem component="a" href="/auth/logout">
          <ListItemIcon>
            <LogOut size={18} />
          </ListItemIcon>
          Log Out
        </MenuItem>
      </Menu>
    </Toolbar>
  );
}
