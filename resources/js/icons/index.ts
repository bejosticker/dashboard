/**
 * Titik terpusat untuk ikon Lucide. Import ikon dari sini
 * (`import { Plus } from '@/icons'`) agar penggantian ikon terpusat.
 */
import {
  Home,
  LayoutGrid,
  Store,
  Globe,
  Truck,
  Package,
  Printer,
  CreditCard,
  Users,
  UserCog,
  History,
  ShoppingCart,
  Download,
  PackageOpen,
  ArchiveRestore,
  CloudLightning,
  Radar,
  Banknote,
  Upload,
  LineChart,
  BarChart3,
  type LucideIcon,
} from 'lucide-react';

export {
  Plus,
  Pencil,
  Trash2,
  X,
  Search,
  LogOut,
  Menu as MenuIcon,
  ChevronDown,
  ChevronRight,
  Download,
  FileSpreadsheet,
  KeyRound,
  Eye,
  Save,
} from 'lucide-react';

export type { LucideIcon };

/** Peta slug menu (verticalMenu.json) -> ikon Lucide. */
const MENU_ICONS: Record<string, LucideIcon> = {
  beranda: Home,
  layouts: LayoutGrid,
  toko: Store,
  'online-toko': Globe,
  suppliers: Truck,
  products: Package,
  'cetak-products': Printer,
  'metode-pembayaran': CreditCard,
  customers: Users,
  karyawan: UserCog,
  'gaji-history': History,
  users: Users,
  sales: ShoppingCart,
  'cetak-sales': Printer,
  kulak: Download,
  'pengambilan-bahan': PackageOpen,
  'toko-income': ArchiveRestore,
  'online-incomes': CloudLightning,
  'online-ads': Radar,
  gaji: Banknote,
  pengeluaran: Upload,
  reports: LineChart,
  'toko-reports': BarChart3,
  'online-reports': Radar,
};

/** Ambil ikon Lucide untuk sebuah slug menu; fallback ke titik. */
export function menuIcon(slug?: string): LucideIcon {
  return (slug && MENU_ICONS[slug]) || LayoutGrid;
}
