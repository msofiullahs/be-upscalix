<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SendEmail;
use Illuminate\Support\Facades\Http;

class ResendMessage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:resend-message';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resend for unsent messages';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $unsents = SendEmail::with('user')->where('sent_status', '!=', 1)->get();
        foreach ($unsents as $item) {
            $mailApi = Http::post(config('app.mail_api'), [
                'email'=> $item->user->email,
                'message'=> "Hey, ".$item->user->name." it's your birthday."
            ]);
            $report = SendEmail::find($item->id);
            if ($mailApi->status() == 200) {
                $report->sent_status = 1;
                $report->save();
            }
        }
    }
}
