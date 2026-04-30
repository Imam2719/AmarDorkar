<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckDbImageConfig extends Command
{
    protected $signature   = 'image:check-config';
    protected $description = 'Check MySQL max_allowed_packet and PHP upload limits for DB image storage';

    public function handle(): void
    {
        $this->info('=== DB Image Storage Config Check ===');
        $this->newLine();

        // MySQL max_allowed_packet
        try {
            $row = DB::selectOne("SHOW VARIABLES LIKE 'max_allowed_packet'");
            $mb  = round($row->Value / 1024 / 1024, 1);
            $ok  = $row->Value >= (32 * 1024 * 1024);
            $this->line(($ok ? '<fg=green>OK</>' : '<fg=red>FAIL</>') . "  MySQL max_allowed_packet: {$mb} MB" . ($ok ? '' : '  <- কমপক্ষে 32MB দরকার'));
            if (!$ok) {
                $this->line('   Fix: my.cnf তে  max_allowed_packet = 64M  যোগ করুন');
            }
        } catch (\Exception $e) {
            $this->error('MySQL check failed: ' . $e->getMessage());
        }

        // PHP limits
        $checks = [
            'upload_max_filesize' => 10,
            'post_max_size'       => 15,
            'memory_limit'        => 128,
        ];

        foreach ($checks as $key => $minMb) {
            $raw = ini_get($key);
            $mb  = $this->toMb($raw);
            $ok  = $mb >= $minMb;
            $this->line(($ok ? 'OK' : 'FAIL') . "  PHP {$key}: {$raw}" . ($ok ? '' : "  <- কমপক্ষে {$minMb}MB দরকার"));
        }

        $this->newLine();
        $this->info('সব OK দেখালে image upload কাজ করবে।');
        $this->newLine();
        $this->comment('WAMP/XAMPP এ my.ini খুলে [mysqld] section এ লিখুন:');
        $this->comment('max_allowed_packet = 64M');
        $this->comment('তারপর MySQL service restart করুন।');
    }

    private function toMb(string $val): float
    {
        $val  = trim($val);
        $last = strtolower(substr($val, -1));
        $num  = (float) $val;
        return match ($last) {
            'g' => $num * 1024,
            'm' => $num,
            'k' => $num / 1024,
            default => $num / 1024 / 1024,
        };
    }
}
