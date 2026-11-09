<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\RestockRequest;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RestockController extends Controller
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Submit a new restock requisition (Staff or Manager).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ingredient_id' => 'required|exists:ingredients,ingredient_id',
            'quantity_requested' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:500',
        ], [
            'ingredient_id.required' => 'Pilih bahan baku yang ingin diajukan restock.',
            'quantity_requested.required' => 'Masukkan jumlah kuantitas restock.',
            'quantity_requested.min' => 'Jumlah kuantitas minimal 0.01.',
        ]);

        try {
            $restock = $this->inventoryService->createRestockRequest($validated, auth()->id());

            return redirect()->route('inventory.index')->with(
                'success',
                "Pengajuan restock untuk {$restock->ingredient->item_name} (+{$restock->quantity_requested} {$restock->ingredient->unit}) berhasil diajukan dengan status PENDING."
            );
        } catch (\Exception $e) {
            return redirect()->route('inventory.index')->with('error', 'Gagal membuat pengajuan restock: '.$e->getMessage());
        }
    }

    /**
     * Manager approves a pending restock requisition.
     */
    public function approve(Request $request, int $id): RedirectResponse
    {
        if (! auth()->user()->isManager()) {
            abort(403, 'Akses ditolak: Hanya Cafe Manager yang memiliki wewenang untuk menyetujui pengajuan restock.');
        }

        try {
            $notes = $request->input('notes');
            $restock = $this->inventoryService->approveRestockRequest($id, auth()->id(), $notes);

            return redirect()->route('inventory.index')->with(
                'success',
                "Pengajuan restock #{$restock->request_id} ({$restock->ingredient->item_name}) berhasil DISETUJUI oleh ".auth()->user()->full_name.'. Pesanan siap dikirim ke supplier.'
            );
        } catch (\Exception $e) {
            return redirect()->route('inventory.index')->with('error', 'Gagal menyetujui pengajuan restock: '.$e->getMessage());
        }
    }

    /**
     * Manager rejects a pending restock requisition.
     */
    public function reject(Request $request, int $id): RedirectResponse
    {
        if (! auth()->user()->isManager()) {
            abort(403, 'Akses ditolak: Hanya Cafe Manager yang memiliki wewenang untuk menolak pengajuan restock.');
        }

        try {
            $notes = $request->input('notes');
            $restock = $this->inventoryService->rejectRestockRequest($id, auth()->id(), $notes);

            return redirect()->route('inventory.index')->with(
                'error',
                "Pengajuan restock #{$restock->request_id} ({$restock->ingredient->item_name}) DITOLAK oleh ".auth()->user()->full_name.'.'
            );
        } catch (\Exception $e) {
            return redirect()->route('inventory.index')->with('error', 'Gagal menolak pengajuan restock: '.$e->getMessage());
        }
    }

    /**
     * Manager bulk approves all pending requisitions.
     */
    public function approveAll(Request $request): RedirectResponse
    {
        if (! auth()->user()->isManager()) {
            abort(403, 'Akses ditolak: Hanya Cafe Manager yang berwenang menyetujui pengajuan restock massal.');
        }

        $pending = RestockRequest::where('status', 'PENDING')->get();
        $count = 0;

        foreach ($pending as $item) {
            $this->inventoryService->approveRestockRequest($item->request_id, auth()->id(), 'Bulk Approval by '.auth()->user()->full_name);
            $count++;
        }

        return redirect()->route('inventory.index')->with(
            'success',
            "{$count} pengajuan restock berhasil DISETUJUI secara serempak dan dialokasikan ke distributor."
        );
    }
}
