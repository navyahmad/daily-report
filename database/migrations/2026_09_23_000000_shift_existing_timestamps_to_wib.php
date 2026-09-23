<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Aplikasi sebelumnya berjalan dengan timezone UTC, sehingga waktu yang tersimpan
 * tertinggal 7 jam dari WIB. Migration ini menggeser data lama ke WIB (UTC+7)
 * agar konsisten dengan timezone aplikasi yang baru (Asia/Jakarta).
 */
return new class extends Migration
{
    /**
     * @var array<string, array<int, string>>
     */
    private array $columns = [
        'daily_reports' => ['submitted_at', 'created_at', 'updated_at'],
        'employees' => ['created_at', 'updated_at'],
        'divisions' => ['created_at', 'updated_at'],
        'admin_hrd_users' => ['last_login_at', 'created_at', 'updated_at'],
    ];

    public function up(): void
    {
        $this->shift(7);
    }

    public function down(): void
    {
        $this->shift(-7);
    }

    private function shift(int $hours): void
    {
        foreach ($this->columns as $table => $columns) {
            DB::table($table)->update(collect($columns)
                ->mapWithKeys(fn (string $column) => [$column => DB::raw($this->addHours($column, $hours))])
                ->all());
        }
    }

    private function addHours(string $column, int $hours): string
    {
        $column = DB::getQueryGrammar()->wrap($column);

        return match (DB::getDriverName()) {
            'sqlite' => sprintf("datetime(%s, '%+d hours')", $column, $hours),
            'pgsql' => sprintf("%s + interval '%d hours'", $column, $hours),
            default => sprintf('DATE_ADD(%s, INTERVAL %d HOUR)', $column, $hours),
        };
    }
};
