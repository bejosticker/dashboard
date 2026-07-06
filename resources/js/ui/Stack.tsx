import { forwardRef, type ElementType, type FormEventHandler } from 'react';
import { Stack as MuiStack, type StackProps as MuiStackProps } from '@mui/material';

/**
 * Stack standar dengan kompatibilitas prop flex (`justifyContent`, `alignItems`,
 * dll) yang di MUI v9 harus lewat `sx`. Wrapper ini menerima prop flex langsung
 * dan otomatis memindahkannya ke `sx`, serta mendukung `component`/`onSubmit`
 * agar bisa dipakai sebagai `<Stack component="form" onSubmit={...}>`.
 */
type FlexProps = {
  justifyContent?: unknown;
  alignItems?: unknown;
  alignContent?: unknown;
  alignSelf?: unknown;
  flexWrap?: unknown;
  flexGrow?: unknown;
  flexShrink?: unknown;
};

export type StackProps = MuiStackProps &
  FlexProps & {
    component?: ElementType;
    onSubmit?: FormEventHandler;
    htmlFor?: string;
  };

export const Stack = forwardRef<HTMLDivElement, StackProps>(function Stack(
  { justifyContent, alignItems, alignContent, alignSelf, flexWrap, flexGrow, flexShrink, sx, ...props },
  ref,
) {
  const flexSx = { justifyContent, alignItems, alignContent, alignSelf, flexWrap, flexGrow, flexShrink };
  const mergedSx = [flexSx, ...(Array.isArray(sx) ? sx : [sx])] as MuiStackProps['sx'];

  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  return <MuiStack ref={ref} sx={mergedSx} {...(props as any)} />;
});
