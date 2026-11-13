<?php

namespace Tests\Feature;

use App\Models\Pelanggan;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PelangganTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    /**
     * Pelanggan yang memiliki transaksi tidak boleh dihapus.
     */
    public function test_pelanggan_yang_memiliki_transaksi_tidak_dapat_dihapus(): void
    {
        $pelanggan = Pelanggan::create([
            'nama_pelanggan' => 'Pelanggan Pengujian',
            'no_telepon' => '081234567890',
            'alamat' => 'Alamat Pengujian',
        ]);

        Transaksi::create([
            'pelanggan_id' => $pelanggan->id,
            'total_bayar' => 200000,
        ]);

        $response = $this->actingAs($this->user)
            ->from(route('pelanggan.index'))
            ->delete(route('pelanggan.destroy', $pelanggan->id));

        $response->assertRedirect(route('pelanggan.index'));

        $response->assertSessionHas('error');

        $this->assertDatabaseHas('pelanggans', [
            'id' => $pelanggan->id,
            'nama_pelanggan' => 'Pelanggan Pengujian',
        ]);

        $this->assertDatabaseHas('transaksis', [
            'pelanggan_id' => $pelanggan->id,
        ]);
    }

    /**
     * Pelanggan tanpa transaksi boleh dihapus.
     */
    public function test_pelanggan_tanpa_transaksi_dapat_dihapus(): void
    {
        $pelanggan = Pelanggan::create([
            'nama_pelanggan' => 'Pelanggan Tanpa Transaksi',
            'no_telepon' => '081234567890',
            'alamat' => 'Alamat Pengujian',
        ]);

        $response = $this->actingAs($this->user)
            ->from(route('pelanggan.index'))
            ->delete(route('pelanggan.destroy', $pelanggan->id));

        $response->assertRedirect(route('pelanggan.index'));

        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('pelanggans', [
            'id' => $pelanggan->id,
        ]);
    }
}
