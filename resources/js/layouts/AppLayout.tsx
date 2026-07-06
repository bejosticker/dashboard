import { useEffect, useState, type ReactNode } from 'react';
import { AppBar, Box, Drawer } from '@mui/material';
import { usePage } from '@inertiajs/react';
import { Sidebar } from './Sidebar';
import { Navbar } from './Navbar';
import { FlashMessages } from './FlashMessages';
import type { SharedProps } from '@/types';

const DRAWER_WIDTH = 260;

export function AppLayout({ children }: { children: ReactNode }) {
  const [mobileOpen, setMobileOpen] = useState(false);
  const { url } = usePage<SharedProps>();

  // Tutup drawer mobile setiap kali pindah halaman.
  useEffect(() => {
    setMobileOpen(false);
  }, [url]);

  const drawer = <Sidebar onNavigate={() => setMobileOpen(false)} />;

  return (
    <Box sx={{ display: 'flex', minHeight: '100vh', bgcolor: 'background.default' }}>
      <Box
        component="nav"
        sx={{ width: { lg: DRAWER_WIDTH }, flexShrink: { lg: 0 } }}
        aria-label="Navigasi utama"
      >
        <Drawer
          variant="temporary"
          open={mobileOpen}
          onClose={() => setMobileOpen(false)}
          ModalProps={{ keepMounted: true }}
          sx={{
            display: { xs: 'block', lg: 'none' },
            '& .MuiDrawer-paper': { width: DRAWER_WIDTH, boxSizing: 'border-box' },
          }}
        >
          {drawer}
        </Drawer>
        <Drawer
          variant="permanent"
          open
          sx={{
            display: { xs: 'none', lg: 'block' },
            '& .MuiDrawer-paper': {
              width: DRAWER_WIDTH,
              boxSizing: 'border-box',
              borderRight: (t) => `1px solid ${t.palette.divider}`,
            },
          }}
        >
          {drawer}
        </Drawer>
      </Box>

      <Box sx={{ flexGrow: 1, width: { lg: `calc(100% - ${DRAWER_WIDTH}px)` }, minWidth: 0 }}>
        <AppBar
          position="sticky"
          color="inherit"
          elevation={0}
          sx={{ bgcolor: 'background.paper', borderBottom: (t) => `1px solid ${t.palette.divider}` }}
        >
          <Navbar onMenuClick={() => setMobileOpen((o) => !o)} />
        </AppBar>

        <Box component="main" sx={{ p: { xs: 2, md: 3 } }}>
          {children}
        </Box>
      </Box>

      <FlashMessages />
    </Box>
  );
}
