import { useState } from 'react';
import { Link, usePage } from '@inertiajs/react';
import {
  Box,
  Collapse,
  List,
  ListItemButton,
  ListItemIcon,
  ListItemText,
  ListSubheader,
  Typography,
} from '@mui/material';
import { ChevronDown, ChevronRight } from 'lucide-react';
import { menuIcon } from '@/icons';
import type { MenuItem, SharedProps } from '@/types';

const BRAND = 'BejoSticker';

function normalize(url?: string): string {
  if (!url) return '';
  return '/' + url.replace(/^\/+/, '');
}

export function Sidebar({ onNavigate }: { onNavigate?: () => void }) {
  const { props, url } = usePage<SharedProps>();
  const menu = props.menu ?? [];
  const currentPath = url.split('?')[0];

  const isActive = (itemUrl?: string) => {
    const target = normalize(itemUrl);
    if (target === '/') return currentPath === '/';
    return currentPath === target || currentPath.startsWith(target + '/');
  };

  return (
    <Box sx={{ height: '100%', display: 'flex', flexDirection: 'column' }}>
      <Box sx={{ px: 3, py: 2.5, display: 'flex', alignItems: 'center', gap: 1.5 }}>
        <Box
          component="img"
          src="/assets/img/logo.png"
          alt={BRAND}
          sx={{ width: 32, height: 32 }}
        />
        <Typography variant="h6" sx={{ fontWeight: 700 }}>
          {BRAND}
        </Typography>
      </Box>

      <List sx={{ px: 1.5, pb: 3, overflowY: 'auto', flexGrow: 1 }} component="nav">
        {menu.map((item, i) => (
          <MenuNode key={i} item={item} isActive={isActive} onNavigate={onNavigate} />
        ))}
      </List>
    </Box>
  );
}

function MenuNode({
  item,
  isActive,
  onNavigate,
}: {
  item: MenuItem;
  isActive: (url?: string) => boolean;
  onNavigate?: () => void;
}) {
  if (item.menuHeader) {
    return (
      <ListSubheader
        disableSticky
        sx={{ bgcolor: 'transparent', textTransform: 'uppercase', fontSize: 11, letterSpacing: 0.5, mt: 1 }}
      >
        {item.menuHeader}
      </ListSubheader>
    );
  }

  if (item.submenu && item.submenu.length > 0) {
    return <SubMenu item={item} isActive={isActive} onNavigate={onNavigate} />;
  }

  const Icon = menuIcon(item.slug);
  const active = isActive(item.url);

  return (
    <ListItemButton
      component={Link}
      href={normalize(item.url)}
      selected={active}
      onClick={onNavigate}
      sx={{ borderRadius: 1.5, mb: 0.25 }}
    >
      <ListItemIcon sx={{ minWidth: 38 }}>
        <Icon size={20} />
      </ListItemIcon>
      <ListItemText primary={item.name} slotProps={{ primary: { sx: { fontSize: 14 } } }} />
    </ListItemButton>
  );
}

function SubMenu({
  item,
  isActive,
  onNavigate,
}: {
  item: MenuItem;
  isActive: (url?: string) => boolean;
  onNavigate?: () => void;
}) {
  const childActive = (item.submenu ?? []).some((c) => isActive(c.url));
  const [open, setOpen] = useState(childActive);
  const Icon = menuIcon(item.slug);

  return (
    <>
      <ListItemButton onClick={() => setOpen((o) => !o)} sx={{ borderRadius: 1.5, mb: 0.25 }}>
        <ListItemIcon sx={{ minWidth: 38 }}>
          <Icon size={20} />
        </ListItemIcon>
        <ListItemText primary={item.name} slotProps={{ primary: { sx: { fontSize: 14 } } }} />
        {open ? <ChevronDown size={16} /> : <ChevronRight size={16} />}
      </ListItemButton>
      <Collapse in={open} timeout="auto" unmountOnExit>
        <List component="div" disablePadding sx={{ pl: 2 }}>
          {(item.submenu ?? []).map((child, i) => (
            <ListItemButton
              key={i}
              component={Link}
              href={normalize(child.url)}
              selected={isActive(child.url)}
              onClick={onNavigate}
              sx={{ borderRadius: 1.5, mb: 0.25 }}
            >
              <ListItemText inset primary={child.name} slotProps={{ primary: { sx: { fontSize: 13.5 } } }} />
            </ListItemButton>
          ))}
        </List>
      </Collapse>
    </>
  );
}
