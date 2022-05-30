<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use Carbon\Carbon;

use App\Models\Auction;
use App\Models\Follow;
use App\Models\User;
use App\Models\Notification;
use App\Models\Bid;
use App\Models\Transaction;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            $auctions_ended = Auction::where('end_date', '<=' ,Carbon::now()->toDateTimeString())
                                        ->where('end_date', '>', Carbon::now()->subMinutes(5)->toDateTimeString())
                                        ->whereNotNull('start_date')->get();

            foreach($auctions_ended as $auction){
                $auction_id = $auction->id;
                $followers = User::wherein('id', 
                                        Follow::where("auction_id", $auction_id)->get()->map->only(['user_id'])
                                )->orWherein('id', Auction::where('id', $auction_id)->get()->map->only(['seller_id']))->get();

                foreach($followers as $follower){
                    $notification =  new Notification([
                        'user_id' => $follower->id,
                        'auction_id' => $auction_id,
                        'date' => Carbon::now(),
                        'type' => 'Auction Ended',
                    ]);
                    $notification->save();
                }

                $winning_bid = $auction->getCurrentHighestBid();

                if(!is_null($winning_bid)){
                    $notification =  new Notification([
                        'user_id' => $winning_bid->user_id,
                        'auction_id' => $auction_id,
                        'date' => Carbon::now(),
                        'type' => 'Winning Bid',
                    ]);
                    $notification->save();

                    $winner = User::findOrFail($winning_bid->user_id);
                    $transaction_winner = new Transaction([
                        'user_id'            => $winner->id,
                        'value'              => $winning_bid->value,
                        'date'               => Carbon::now(),
                        'description'        => 'Add Credit to ',
                        'method'             => 'Transfer',
                        'status'             => 'Pending',
                    ]);
                    $transaction_winner->save();

                    $seller = User::findOrFail($auction->seller_id);
                    $transaction_seller = new Transaction([
                        'user_id'            => $seller->id,
                        'value'              => $winning_bid->value,
                        'date'               => Carbon::now(),
                        'description'        => 'Take Credit From ',
                        'method'             => 'Transfer',
                        'status'             => 'Pending',
                    ]);
                    $transaction_seller->save();
                }
            }
        })->everyFiveMinutes();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
