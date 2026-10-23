<?php

namespace Tests\Feature;

use App\Models\CashFlow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashFlowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test recording cash inflow and outflow and calculating net balance.
     */
    public function test_cash_flow_recording_and_balance_calculation(): void
    {
        // Arrange
        CashFlow::create([
            'date' => '2026-10-01',
            'type' => 'pemasukan',
            'category' => 'Infaq Jumat',
            'item_details' => 'Kotak amal shalat jumat',
            'amount' => 5000000,
            'description' => 'Infaq jumat pekan ke-1',
        ]);

        CashFlow::create([
            'date' => '2026-10-02',
            'type' => 'pemasukan',
            'category' => 'Donasi Renovasi',
            'item_details' => 'Transfer hamba Allah',
            'amount' => 3000000,
            'description' => 'Dana renovasi tempat wudhu',
        ]);

        CashFlow::create([
            'date' => '2026-10-05',
            'type' => 'pengeluaran',
            'category' => 'Operasional',
            'item_details' => 'Tagihan listrik & air',
            'amount' => 1500000,
            'description' => 'Bulan September',
        ]);

        // Act
        $totalPemasukan = CashFlow::where('type', 'pemasukan')->sum('amount');
        $totalPengeluaran = CashFlow::where('type', 'pengeluaran')->sum('amount');
        $netSaldo = $totalPemasukan - $totalPengeluaran;

        // Assert
        $this->assertEquals(8000000, $totalPemasukan);
        $this->assertEquals(1500000, $totalPengeluaran);
        $this->assertEquals(6500000, $netSaldo);
        $this->assertDatabaseHas('cash_flows', [
            'category' => 'Infaq Jumat',
            'amount' => 5000000,
        ]);
    }

    /**
     * Test soft deletes preserve record in database without permanent deletion.
     */
    public function test_cash_flow_soft_deletes_preserves_audit_trail(): void
    {
        // Arrange
        $flow = CashFlow::create([
            'date' => '2026-10-10',
            'type' => 'pengeluaran',
            'category' => 'Kebersihan',
            'item_details' => 'Alat pel dan pembersih lantai',
            'amount' => 250000,
            'description' => 'Beli di toko berkah',
        ]);

        // Act
        $flow->delete();

        // Assert
        $this->assertSoftDeleted('cash_flows', [
            'id' => $flow->id,
        ]);
        $this->assertEquals(0, CashFlow::count());
        $this->assertEquals(1, CashFlow::withTrashed()->count());
    }
}
