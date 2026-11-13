<?php

namespace Tests\Feature;

use App\Models\AuditTrail;
use App\Models\Barang;
use App\Models\Pelanggan;
use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransaksiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Pelanggan $pelanggan;
    private Barang $barang;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->pelanggan = Pelanggan::create([
            'nama_pelanggan' => 'Pelanggan Pengujian',
            'no_telepon' => '081234567890',
            'alamat' => 'Alamat Dummy',
        ]);

        $this->barang = Barang::create([
            'nama_barang' => 'Oli Pengujian',
            'jenis' => 'Barang',
            'stok' => 10,
            'harga_jual' => 100000,
        ]);
    }

    /**
     * Menguji penyimpanan transaksi dan pengurangan stok.
     */
    public function test_transaksi_berhasil_disimpan_dan_stok_berkurang(): void
    {
        $response = $this->actingAs($this->user)
            ->from(route('transaksi.create'))
            ->post(route('transaksi.store'), [
                'pelanggan_id' => $this->pelanggan->id,
                'items' => [
                    [
                        'barang_id' => $this->barang->id,
                        'jumlah' => 2,
                    ],
                ],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('transaksis', [
            'pelanggan_id' => $this->pelanggan->id,
            'total_bayar' => 200000,
        ]);

        $this->assertDatabaseHas('transaksi_details', [
            'barang_id' => $this->barang->id,
            'jumlah' => 2,
            'subtotal' => 200000,
        ]);

        $this->assertDatabaseHas('barangs', [
            'id' => $this->barang->id,
            'stok' => 8,
        ]);

        $this->assertDatabaseHas('audit_trails', [
            'aksi' => 'CREATE',
            'fitur' => 'Transaksi',
        ]);
    }

    /**
     * Menguji penolakan transaksi ketika stok tidak mencukupi.
     */
    public function test_transaksi_ditolak_jika_stok_tidak_mencukupi(): void
    {
        $response = $this->actingAs($this->user)
            ->from(route('transaksi.create'))
            ->post(route('transaksi.store'), [
                'pelanggan_id' => $this->pelanggan->id,
                'items' => [
                    [
                        'barang_id' => $this->barang->id,
                        'jumlah' => 15,
                    ],
                ],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertDatabaseCount('transaksis', 0);

        $this->assertDatabaseHas('barangs', [
            'id' => $this->barang->id,
            'stok' => 10,
        ]);

        $this->assertDatabaseCount('audit_trails', 0);
    }

    /**
     * Menguji validasi transaksi tanpa item.
     */
    public function test_transaksi_tanpa_item_ditolak(): void
    {
        $response = $this->actingAs($this->user)
            ->from(route('transaksi.create'))
            ->post(route('transaksi.store'), [
                'pelanggan_id' => $this->pelanggan->id,
                'items' => [],
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('items');

        $this->assertDatabaseCount('transaksis', 0);
    }

    /**
     * Menguji pembaruan transaksi dan penyesuaian stok.
     */
    public function test_update_transaksi_menyesuaikan_stok(): void
    {
        $this->barang->update([
            'stok' => 8,
        ]);

        $transaksi = Transaksi::create([
            'pelanggan_id' => $this->pelanggan->id,
            'total_bayar' => 200000,
        ]);

        TransaksiDetail::create([
            'transaksi_id' => $transaksi->id,
            'barang_id' => $this->barang->id,
            'jumlah' => 2,
            'subtotal' => 200000,
        ]);

        $response = $this->actingAs($this->user)
            ->put(route('transaksi.update', $transaksi->id), [
                'pelanggan_id' => $this->pelanggan->id,
                'items' => [
                    [
                        'barang_id' => $this->barang->id,
                        'jumlah' => 3,
                    ],
                ],
            ]);

        $response->assertRedirect(route('transaksi.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('barangs', [
            'id' => $this->barang->id,
            'stok' => 7,
        ]);

        $this->assertDatabaseHas('transaksis', [
            'id' => $transaksi->id,
            'total_bayar' => 300000,
        ]);

        $this->assertDatabaseHas('transaksi_details', [
            'transaksi_id' => $transaksi->id,
            'barang_id' => $this->barang->id,
            'jumlah' => 3,
            'subtotal' => 300000,
        ]);

        $this->assertDatabaseHas('audit_trails', [
            'aksi' => 'UPDATE',
            'fitur' => 'Transaksi',
        ]);
    }

    /**
     * Menguji penghapusan transaksi dan pengembalian stok.
     */
    public function test_hapus_transaksi_mengembalikan_stok(): void
    {
        $this->barang->update([
            'stok' => 8,
        ]);

        $transaksi = Transaksi::create([
            'pelanggan_id' => $this->pelanggan->id,
            'total_bayar' => 200000,
        ]);

        TransaksiDetail::create([
            'transaksi_id' => $transaksi->id,
            'barang_id' => $this->barang->id,
            'jumlah' => 2,
            'subtotal' => 200000,
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('transaksi.destroy', $transaksi->id));

        $response->assertRedirect(route('transaksi.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('transaksis', [
            'id' => $transaksi->id,
        ]);

        $this->assertDatabaseMissing('transaksi_details', [
            'transaksi_id' => $transaksi->id,
        ]);

        $this->assertDatabaseHas('barangs', [
            'id' => $this->barang->id,
            'stok' => 10,
        ]);

        $this->assertDatabaseHas('audit_trails', [
            'aksi' => 'DELETE',
            'fitur' => 'Transaksi',
        ]);
    }
}
