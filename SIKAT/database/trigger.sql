-- =====================================================================
-- TRIGGER
-- Butuh function fn_set_updated_at dan fn_audit_pesanan, serta tabel menu, pesanan, log_pesanan.
-- =====================================================================

DROP TRIGGER IF EXISTS trg_menu_updated_at ON public.menu;
CREATE TRIGGER trg_menu_updated_at BEFORE UPDATE ON public.menu FOR EACH ROW EXECUTE FUNCTION public.fn_set_updated_at();

DROP TRIGGER IF EXISTS trg_pesanan_audit_d ON public.pesanan;
CREATE TRIGGER trg_pesanan_audit_d BEFORE DELETE ON public.pesanan FOR EACH ROW EXECUTE FUNCTION public.fn_audit_pesanan();

DROP TRIGGER IF EXISTS trg_pesanan_audit_iu ON public.pesanan;
CREATE TRIGGER trg_pesanan_audit_iu AFTER INSERT OR UPDATE ON public.pesanan FOR EACH ROW EXECUTE FUNCTION public.fn_audit_pesanan();

DROP TRIGGER IF EXISTS trg_pesanan_updated_at ON public.pesanan;
CREATE TRIGGER trg_pesanan_updated_at BEFORE UPDATE ON public.pesanan FOR EACH ROW EXECUTE FUNCTION public.fn_set_updated_at();
