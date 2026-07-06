import { Box, Pagination as MuiPagination, Typography } from '@mui/material';
import { router } from '@inertiajs/react';
import type { Paginator } from '@/types';

export interface PaginationProps<T> {
  paginator: Paginator<T>;
  preserveScroll?: boolean;
}

/**
 * Paginasi yang mengonsumsi Laravel paginator (`->paginate()`).
 * Navigasi lewat Inertia dengan mempertahankan query string yang ada
 * (mis. filter pencarian).
 */
export function Pagination<T>({ paginator, preserveScroll = true }: PaginationProps<T>) {
  const handleChange = (_: React.ChangeEvent<unknown>, page: number) => {
    const url = new URL(window.location.href);
    url.searchParams.set('page', String(page));
    router.get(url.pathname + url.search, undefined, {
      preserveScroll,
      preserveState: true,
    });
  };

  return (
    <Box
      sx={{
        display: 'flex',
        flexWrap: 'wrap',
        gap: 1,
        alignItems: 'center',
        justifyContent: 'space-between',
        mt: 2,
      }}
    >
      <Typography variant="body2" color="text.secondary">
        {paginator.total > 0
          ? `Menampilkan ${paginator.from ?? 0}–${paginator.to ?? 0} dari ${paginator.total}`
          : 'Tidak ada data'}
      </Typography>
      {paginator.last_page > 1 && (
        <MuiPagination
          count={paginator.last_page}
          page={paginator.current_page}
          onChange={handleChange}
          color="primary"
          shape="rounded"
        />
      )}
    </Box>
  );
}
