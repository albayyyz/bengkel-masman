
<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\Pelanggan;
use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BarangTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Pelanggan $pelanggan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->pelanggan = Pelanggan::create([
            'nama_pelanggan' => 'Pelanggan Pengujian',
            'no_telepon' => '081234567890',
            'alamat' => 'Alamat Pengujian',
        ]);
    }

    public function test_barang_dengan_harga_positif_dapat_disimpan(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('barang.store'), [
                'nama_barang' => 'Oli Pengujian',
                'jenis' => 'Barang',
                'stok' => 10,
                'harga_jual' => 50000,
            ]);

        $response->assertRedirect(route('barang.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('barangs', [
            'nama_barang' => 'Oli Pengujian',
            'jenis' => 'Barang',
            'stok' => 10,
            'harga_jual' => 50000,
        ]);
    }

    public function test_barang_dengan_harga_nol_dapat_disimpan(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('barang.store'), [
                'nama_barang' => 'Barang Gratis',
                'jenis' => 'Barang',
                'stok' => 5,
                'harga_jual' => 0,
            ]);

        $response->assertRedirect(route('barang.index'));

        $this->assertDatabaseHas('barangs', [
            'nama_barang' => 'Barang Gratis',
            'harga_jual' => 0,
        ]);
    }

    public function test_barang_dengan_harga_negatif_ditolak(): void
    {
        $response = $this->actingAs($this->user)
            ->from(route('barang.index'))
            ->post(route('barang.store'), [
                'nama_barang' => 'Barang Tidak Valid',
                'jenis' => 'Barang',
                'stok' => 5,
                'harga_jual' => -50000,
            ]);

        $response->assertRedirect(route('barang.index'));
        $response->assertSessionHasErrors('harga_jual');

        $this->assertDatabaseMissing('barangs', [
            'nama_barang' => 'Barang Tidak Valid',
        ]);
    }

    public function test_stok_jasa_selalu_nol(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('barang.store'), [
                'nama_barang' => 'Servis Ringan',
                'jenis' => 'Jasa',
                'stok' => 99,
                'harga_jual' => 75000,
            ]);

        $response->assertRedirect(route('barang.index'));

        $this->assertDatabaseHas('barangs', [
            'nama_barang' => 'Servis Ringan',
            'jenis' => 'Jasa',
            'stok' => 0,
            'harga_jual' => 75000,
        ]);
    }

    public function test_jenis_barang_yang_sudah_digunakan_tidak_dapat_diubah(): void
    {
        $barang = Barang::create([
            'nama_barang' => 'Oli Pengujian',
            'jenis' => 'Barang',
            'stok' => 10,
            'harga_jual' => 50000,
        ]);

        $transaksi = Transaksi::create([
            'pelanggan_id' => $this->pelanggan->id,
            'total_bayar' => 50000,
        ]);

        TransaksiDetail::create([
            'transaksi_id' => $transaksi->id,
            'barang_id' => $barang->id,
            'jumlah' => 1,
            'subtotal' => 50000,
        ]);

        $response = $this->actingAs($this->user)
            ->from(route('barang.edit', $barang->id))
            ->put(route('barang.update', $barang->id), [
                'nama_barang' => 'Oli Pengujian',
                'jenis' => 'Jasa',
                'stok' => 0,
                'harga_jual' => 50000,
            ]);

        $response->assertRedirect(route('barang.edit', $barang->id));
        $response->assertSessionHasErrors('jenis');

        $this->assertDatabaseHas('barangs', [
            'id' => $barang->id,
            'jenis' => 'Barang',
            'stok' => 10,
        ]);
    }

    public function test_jenis_barang_yang_belum_digunakan_dapat_diubah(): void
    {
        $barang = Barang::create([
            'nama_barang' => 'Item Pengujian',
            'jenis' => 'Barang',
            'stok' => 10,
            'harga_jual' => 50000,
        ]);

        $response = $this->actingAs($this->user)
            ->put(route('barang.update', $barang->id), [
                'nama_barang' => 'Servis Pengujian',
                'jenis' => 'Jasa',
                'stok' => 10,
                'harga_jual' => 75000,
            ]);

        $response->assertRedirect(route('barang.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('barangs', [
            'id' => $barang->id,
            'nama_barang' => 'Servis Pengujian',
            'jenis' => 'Jasa',
            'stok' => 0,
            'harga_jual' => 75000,
        ]);
    }

    public function test_barang_yang_sudah_digunakan_tidak_dapat_dihapus(): void
    {
        $barang = Barang::create([
            'nama_barang' => 'Oli Pengujian',
            'jenis' => 'Barang',
            'stok' => 10,
            'harga_jual' => 50000,
        ]);

        $transaksi = Transaksi::create([
            'pelanggan_id' => $this->pelanggan->id,
            'total_bayar' => 50000,
        ]);

        TransaksiDetail::create([
            'transaksi_id' => $transaksi->id,
            'barang_id' => $barang->id,
            'jumlah' => 1,
            'subtotal' => 50000,
        ]);

        $response = $this->actingAs($this->user)
            ->from(route('barang.index'))
            ->delete(route('barang.destroy', $barang->id));

        $response->assertRedirect(route('barang.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('barangs', [
            'id' => $barang->id,
        ]);
    }
}
