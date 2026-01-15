<?php

namespace App\Http\Controllers;

use App\Domain\Inventory\Events\OrderPackedOrShipped;
use App\Models\FinancialCategory;
use App\Models\LedgerEntry;
use App\Models\Location;
use App\Models\Order;
use App\Models\Shipment;
use App\Services\ShippingTrackingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ShipmentController extends Controller
{
    public function index()
    {
        $shipments = Shipment::with('order')->latest()->paginate(15);

        return view('shipments.index', compact('shipments'));
    }

    public function create(Request $request)
    {
        $orderId = $request->get('order_id');
        $order = Order::findOrFail($orderId);
        $couriers = config('rajaongkir.couriers', []);

        // Check if shipment already exists
        if ($order->shipment) {
            return redirect()->route('shipments.show', $order->shipment)->with('info', 'Pengiriman untuk order ini sudah ada');
        }

        return view('shipments.create', compact('order', 'couriers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'recipient_name' => 'nullable|string',
            'recipient_address' => 'nullable|string',
            'courier' => 'nullable|string',
            'tracking_number' => 'nullable|string',
            'tracking_media_id' => 'nullable|exists:media,id',
            'shipping_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if (! empty($validated['tracking_media_id'])) {
            $media = \App\Models\Media::find($validated['tracking_media_id']);
            if (! $media || $media->type !== 'shipment_proof') {
                return redirect()->back()->withErrors([
                    'tracking_media_id' => 'Lampiran resi tidak valid.',
                ])->withInput();
            }
            $media->metadata = array_merge($media->metadata ?? [], [
                'order_id' => $validated['order_id'],
            ]);
            $media->save();
        }

        $shipment = Shipment::create($validated);

        // Update order status to packed if not already
        $order = $shipment->order;
        if ($order->status === 'paid') {
            $order->update(['status' => 'packed']);
        }

        return redirect()->route('shipments.show', $shipment)->with('success', 'Pengiriman berhasil dibuat');
    }

    public function show(Shipment $shipment)
    {
        $shipment->load('order.customer', 'order.items.product', 'order.items.productVariant', 'trackingMedia');
        $locations = Location::where('is_active', true)->with('warehouse')->get();
        $couriers = config('rajaongkir.couriers', []);
        $shipMovements = \App\Models\StockMovement::with(['user', 'fromLocation', 'product', 'productVariant'])
            ->where('movement_type', 'ship')
            ->where('reference_type', 'order')
            ->where('reference_id', $shipment->order_id)
            ->orderByDesc('movement_date')
            ->get();

        return view('shipments.show', compact('shipment', 'locations', 'couriers', 'shipMovements'));
    }

    public function updateStatus(Request $request, Shipment $shipment)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,packed,shipped,delivered',
            'tracking_number' => 'nullable|string',
            'tracking_media_id' => 'nullable|exists:media,id',
            'shipping_cost' => 'nullable|numeric|min:0',
        ]);

        $trackingNumber = $validated['tracking_number'] ?? $shipment->tracking_number;

        if ($validated['status'] === 'shipped' && empty($trackingNumber)) {
            return back()->with('error', 'No. resi wajib diisi sebelum status Shipped.');
        }

        $shipment->update([
            'status' => $validated['status'],
            'tracking_number' => $trackingNumber,
            'tracking_media_id' => $validated['tracking_media_id'] ?? $shipment->tracking_media_id,
            'shipping_cost' => $validated['shipping_cost'] ?? $shipment->shipping_cost,
        ]);

        // Update order status based on shipment status
        if ($validated['status'] === 'shipped') {
            $shipment->order->update(['status' => 'shipped', 'shipped_at' => now()]);
            $this->createLedgerForShipment($shipment);
        } elseif ($validated['status'] === 'delivered') {
            $shipment->order->update(['status' => 'done']);
        }

        return back()->with('success', 'Status pengiriman berhasil diperbarui');
    }

    public function track(Request $request, Shipment $shipment, ShippingTrackingService $trackingService)
    {
        $request->validate([
            'courier' => 'required|string',
            'awb' => 'required|string',
        ]);

        $result = $trackingService->track($request->input('courier'), $request->input('awb'));
        if (! $result['ok']) {
            return response()->json([
                'message' => $result['message'] ?? 'Gagal mengambil status pengiriman.',
                'status' => $result['status'] ?? 500,
                'response' => $result['response'] ?? null,
                'body' => $result['body'] ?? null,
                'error' => $result['error'] ?? null,
            ], $result['status'] ?? 500);
        }

        $shipment->tracking_payload = $result['payload'] ?? null;
        $shipment->tracking_status = $result['tracking_status'] ?? null;
        $shipment->tracked_at = now();
        if (! empty($result['delivered_at'])) {
            $shipment->delivered_at = $result['delivered_at'];
        }
        if (! empty($result['received_by'])) {
            $shipment->received_by = $result['received_by'];
        }

        if (! empty($result['delivered'])) {
            $shipment->status = 'delivered';
            if ($shipment->order && $shipment->order->status !== 'done' && $shipment->order->status !== 'cancelled') {
                $shipment->order->update(['status' => 'done']);
            }
        }

        $shipment->save();

        return response()->json($result['payload']);
    }

    public function pickAndShip(Request $request, Shipment $shipment)
    {
        $validated = $request->validate([
            'location_id' => 'required|exists:locations,id',
        ]);

        if (empty($shipment->tracking_number)) {
            return back()->with('error', 'No. resi wajib diisi sebelum status Shipped.');
        }

        $user = auth()->user();
        $alreadyShipped = \App\Models\StockMovement::query()
            ->where('movement_type', 'ship')
            ->where('reference_type', 'order')
            ->where('reference_id', $shipment->order_id)
            ->exists();

        if ($alreadyShipped && (! $user || ! $user->hasRole('Super Admin'))) {
            return back()->with('error', 'Stok untuk order ini sudah dikurangi.');
        }

        $shipment->load('order.items');
        $items = $shipment->order->items->map(function ($item) use ($validated) {
            return [
                'product_id' => $item->product_id,
                'location_id' => $validated['location_id'],
                'qty' => $item->quantity,
                'movement_date' => now(),
            ];
        })->toArray();

        DB::transaction(function () use ($shipment, $items) {
            event(new OrderPackedOrShipped($shipment->order_id, $items));
            $shipment->update(['status' => 'shipped', 'shipped_at' => now()]);
            $shipment->order->update(['status' => 'shipped']);
            $this->createLedgerForShipment($shipment);
        });

        return back()->with('success', 'Stok dikurangi dan status pengiriman diperbarui.');
    }

    private function createLedgerForShipment(Shipment $shipment): void
    {
        if (($shipment->shipping_cost ?? 0) <= 0) {
            return;
        }

        $category = FinancialCategory::firstOrCreate(
            ['name' => 'Ongkos Kirim', 'type' => 'expense'],
            ['is_active' => true]
        );

        $entry = LedgerEntry::where('source_type', 'shipment')
            ->where('source_id', $shipment->id)
            ->first();

        if ($entry) {
            $entry->update([
                'category_id' => $category->id,
                'description' => 'Ongkir shipment #'.($shipment->shipment_number ?? $shipment->id),
                'amount' => $shipment->shipping_cost,
            ]);

            return;
        }

        LedgerEntry::create([
            'entry_date' => now()->toDateString(),
            'type' => 'expense',
            'category_id' => $category->id,
            'description' => 'Ongkir shipment #'.($shipment->shipment_number ?? $shipment->id),
            'amount' => $shipment->shipping_cost,
            'reference_id' => $shipment->order_id,
            'reference_type' => 'order',
            'source_type' => 'shipment',
            'source_id' => $shipment->id,
            'created_by' => Auth::id(),
        ]);
    }

    public function label(Shipment $shipment)
    {
        $shipment->load(
            'order.customer',
            'order.items.product',
            'order.shippingDistrict',
            'order.shippingCity',
            'order.shippingProvince'
        );

        $pdf = Pdf::loadView('shipments.label-pdf', compact('shipment'))
            ->setPaper('a5', 'portrait')
            ->setOption('margin-top', 0)
            ->setOption('margin-bottom', 0)
            ->setOption('margin-left', 0)
            ->setOption('margin-right', 0);

        return $pdf->download('Label-'.$shipment->id.'.pdf');
    }

    public function printLabel(Shipment $shipment)
    {
        $shipment->load(
            'order.customer',
            'order.items.product',
            'order.shippingDistrict',
            'order.shippingCity',
            'order.shippingProvince'
        );

        return view('shipments.label-print', compact('shipment'));
    }
}
