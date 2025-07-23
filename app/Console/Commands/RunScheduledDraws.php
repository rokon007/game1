<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lottery;
use App\Services\LotteryService;

class RunScheduledDraws extends Command
{
    protected $signature = 'lottery:run-draws';
    protected $description = 'Run scheduled lottery draws';

    public function handle(LotteryService $lotteryService)
    {
        $lotteries = Lottery::where('status', 'active')
            ->where('auto_draw', true)
            ->where('draw_date', '<=', now())
            ->get();

        foreach ($lotteries as $lottery) {
            try {
                // Broadcast that draw is starting
                broadcast(new \App\Events\DrawStarted($lottery));

                $this->info("Draw started for lottery: {$lottery->name}");

                // The actual draw will be handled by the LiveDrawModal component
                // when users are watching, or completed automatically after timeout

        } catch (\Exception $e) {
            $this->error("Failed to start draw for lottery {$lottery->name}: " . $e->getMessage());
        }
    }
}
}
