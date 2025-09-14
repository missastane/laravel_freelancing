<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TruncateTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:truncate {table}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description =  'خالی کردن کامل یک جدول (truncate)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $table = $this->argument('table');

        try {
            DB::table($table)->truncate();
            $this->info("records of {$table} successfully removed");
        } catch (Exception $e) {
            $this->error("خطا: " . $e->getMessage());
        }

        return 0;
    }
}
