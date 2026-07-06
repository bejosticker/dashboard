import { forwardRef } from 'react';
import { IconButton as MuiIconButton, type IconButtonProps } from '@mui/material';

export type { IconButtonProps };

export const IconButton = forwardRef<HTMLButtonElement, IconButtonProps>(function IconButton(
  props,
  ref,
) {
  return <MuiIconButton ref={ref} size="small" {...props} />;
});
