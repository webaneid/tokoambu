# Warehouse & Inventory Module Checklist

Sumber acuan: `02-warehouse_inventory_system_blueprint.md`. Status awal di-set `pending` dan akan diperbarui tiap batch implementasi.

## Prinsip & Guardrail
- [ ] Semua perubahan stok lewat `stock_movements`, tidak ada update langsung produk.
- [ ] Pengurangan stok hanya saat order `packed`/`shipped`.
- [ ] Semua movement punya `reference_type` + `reference_id`.
- [ ] Validasi stok cukup sebelum pengurangan; transaksi + locking; idempotensi movement per reference.

## Schema & Model
- [x] Tabel `warehouses` (code unique, name, address, is_active).
- [x] Tabel `locations` (warehouse_id, code, zone/rack/bin optional, is_active).
- [x] Tabel `inventory_balances` (product_id, location_id, qty_on_hand, qty_reserved).
- [x] Tabel `stock_movements` (movement_date, product_id, from_location_id, to_location_id, qty, movement_type, reference_type/id, reason/notes, created_by).

## Inventory Engine (Event/Listener)
- [x] PurchaseReceived → movement `receive` + tambah balance.
- [x] OrderPackedOrShipped → movement `ship` + kurangi balance.
- [x] StockAdjusted (manual stock out reasons) → movement `adjust` + kurangi balance.
- [x] StockTransferred → movement `transfer` (asal berkurang, tujuan bertambah).
- [x] StockOpnameConfirmed → movement `adjust` reason `stock_opname` + koreksi balance.
- [ ] (Opsional) StockPutAway jika receiving & putaway dipisah.

## Forms & UI Flows
- [x] Receiving + Putaway (pilih purchase, qty_received per item, lokasi simpan).
- [x] Transfer stok (produk, lokasi asal/tujuan, qty, catatan).
- [x] Stock Out / Adjustment (tanggal, produk, lokasi asal, qty, reason dropdown, catatan, lampiran opsional).
- [x] Stock Opname (tampilkan stok sistem per lokasi, input stok fisik, konfirmasi selisih).

## Laporan & Analitik
- [x] Report pengeluaran stok by reason (filter periode/produk/lokasi/reason/user).
- [x] Dead stock analytics: per product/location last_out_date, status active/slow/dead berdasarkan threshold setting.
- [x] Dashboard gudang: widget total SKU, total on hand, active/slow/dead; tabel ringkasan stok (lokasi, qty, last out, status, rekomendasi).
- [x] Alert slow/dead stock sesuai threshold (setting).

## Integrasi & RBAC
- [x] Integrasi purchase status `received` → receive movement.
- [x] Integrasi order status `packed/shipped` → ship movement.
- [ ] Permission: receiving/transfer/stock_adjustment/opname; Super Admin full, Operator gudang allowed, Finance tidak. (sementara middleware di-nonaktifkan untuk debugging akses)

## Definition of Done (konfirmasi)
- [ ] Bisa jawab stok produk X ada di lokasi mana (via balances).
- [ ] Bisa trace stok berkurang/bertambah karena apa (via movements).
- [ ] Tidak ada selisih stok tanpa histori movement.
