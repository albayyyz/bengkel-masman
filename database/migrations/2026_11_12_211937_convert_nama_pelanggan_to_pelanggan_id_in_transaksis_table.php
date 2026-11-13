
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Mengubah nama_pelanggan menjadi relasi pelanggan_id.
     */
    public function up(): void
    {
        if (!Schema::hasTable('transaksis')) {
            throw new RuntimeException('Tabel transaksis tidak ditemukan.');
        }

        if (!Schema::hasTable('pelanggans')) {
            throw new RuntimeException('Tabel pelanggans tidak ditemukan.');
        }

        $hasOldColumn = Schema::hasColumn('transaksis', 'nama_pelanggan');
        $hasNewColumn = Schema::hasColumn('transaksis', 'pelanggan_id');

        if (!$hasOldColumn && !$hasNewColumn) {
            throw new RuntimeException(
                'Kolom nama_pelanggan dan pelanggan_id tidak ditemukan.'
            );
        }

        // Buat kolom baru jika belum tersedia.
        if (!$hasNewColumn) {
            Schema::table('transaksis', function (Blueprint $table) {
                $table->unsignedBigInteger('pelanggan_id')->nullable();
            });
        }

        // Pindahkan relasi berdasarkan nama pelanggan.
        if ($hasOldColumn) {
            $transaksis = DB::table('transaksis')
                ->select('id', 'nama_pelanggan', 'pelanggan_id')
                ->get();

            $pelanggans = DB::table('pelanggans')
                ->select('id', 'nama_pelanggan')
                ->get();

            foreach ($transaksis as $transaksi) {
                $matches = $pelanggans->filter(
                    fn ($pelanggan) =>
                        $pelanggan->nama_pelanggan === $transaksi->nama_pelanggan
                );

                if ($matches->count() !== 1) {
                    throw new RuntimeException(
                        "Transaksi ID {$transaksi->id}: nama pelanggan "
                        . "tidak ditemukan atau tidak unik."
                    );
                }

                $expectedId = (int) $matches->first()->id;

                if (
                    $transaksi->pelanggan_id !== null &&
                    (int) $transaksi->pelanggan_id !== $expectedId
                ) {
                    throw new RuntimeException(
                        "Transaksi ID {$transaksi->id}: pelanggan_id "
                        . "tidak sesuai dengan nama pelanggan."
                    );
                }

                if ($transaksi->pelanggan_id === null) {
                    DB::table('transaksis')
                        ->where('id', $transaksi->id)
                        ->update(['pelanggan_id' => $expectedId]);
                }
            }
        }

        // Validasi data pelanggan.
        if (DB::table('transaksis')->whereNull('pelanggan_id')->exists()) {
            throw new RuntimeException(
                'Masih terdapat transaksi tanpa pelanggan_id.'
            );
        }

        $orphaned = DB::table('transaksis as t')
            ->leftJoin('pelanggans as p', 'p.id', '=', 't.pelanggan_id')
            ->whereNull('p.id')
            ->exists();

        if ($orphaned) {
            throw new RuntimeException(
                'Ditemukan pelanggan_id yang tidak memiliki data pelanggan.'
            );
        }

        // Pastikan pelanggan_id wajib diisi.
        $columns = collect(Schema::getColumns('transaksis'))
            ->keyBy('name');

        $column = $columns->get('pelanggan_id');

        if (!$column) {
            throw new RuntimeException(
                'Kolom pelanggan_id tidak ditemukan.'
            );
        }

        if ($column['nullable']) {
            Schema::table('transaksis', function (Blueprint $table) {
                $table->unsignedBigInteger('pelanggan_id')
                    ->nullable(false)
                    ->change();
            });
        }

        // Periksa foreign key yang sudah tersedia.
        $foreignKeys = Schema::getForeignKeys('transaksis');

        $foreignKey = collect($foreignKeys)->first(
            fn ($key) => in_array(
                'pelanggan_id',
                $key['columns'] ?? [],
                true
            )
        );

        $validForeignKey = $foreignKey
            && ($foreignKey['columns'] ?? []) === ['pelanggan_id']
            && ($foreignKey['foreign_table'] ?? null) === 'pelanggans'
            && ($foreignKey['foreign_columns'] ?? []) === ['id']
            && in_array(
                strtoupper($foreignKey['on_delete'] ?? ''),
                ['RESTRICT', 'NO ACTION'],
                true
            );

        // Hapus foreign key jika tidak sesuai.
        if ($foreignKey && !$validForeignKey) {
            Schema::table('transaksis', function (Blueprint $table) use ($foreignKey) {
                $table->dropForeign($foreignKey['name']);
            });
        }

        // Buat foreign key jika belum tersedia atau tidak sesuai.
        if (!$validForeignKey) {
            Schema::table('transaksis', function (Blueprint $table) {
                $table->foreign('pelanggan_id')
                    ->references('id')
                    ->on('pelanggans')
                    ->restrictOnDelete();
            });
        }

        // Hapus kolom lama setelah relasi baru siap.
        if ($hasOldColumn && Schema::hasColumn('transaksis', 'nama_pelanggan')) {
            Schema::table('transaksis', function (Blueprint $table) {
                $table->dropColumn('nama_pelanggan');
            });
        }
    }

    /**
     * Migration ini tidak dapat di-rollback otomatis.
     */
    public function down(): void
    {
        throw new RuntimeException(
            'Migration ini tidak dapat di-rollback otomatis. '
            . 'Pulihkan database dari backup jika ingin kembali '
            . 'ke struktur sebelumnya.'
        );
    }
};
