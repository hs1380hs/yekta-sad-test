<?php

namespace App\Console\Commands;

use App\Models\Basket;
use Illuminate\Console\Command;
use Carbon\Carbon;

class RemoveExpiredBaskets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'basket:clear-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove expired baskets';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $expiredBaskets = Basket::where('expires_at', '<', Carbon::now())->get();

        foreach ($expiredBaskets as $basket) {
            $this->info("Removing basket ID: {$basket->id}");
            $basket->items()->delete();
            $basket->delete();
        }

        $this->info("Removed " . count($expiredBaskets) . " expired baskets.");
    }
}
