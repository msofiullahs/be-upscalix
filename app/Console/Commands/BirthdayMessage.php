<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use DateTimeZone;
use Carbon\Carbon;
use App\Models\UserProfile;
use App\Models\SendEmail;

class BirthdayMessage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:birthday-message';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send birthday message';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $timezones = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
        foreach ($timezones as $timezone) {
            $nineAm = Carbon::createFromTimeString('09:00')->shiftTimezone($timezone);
            $currentTime = Carbon::now($timezone);
            $dateOnly = Carbon::now($timezone)->format('Y-m-d');

            if ($currentTime->eq($nineAm)) {
                $profiles = UserProfile::with('user')->where('location', $timezone)->where('birthday', $dateOnly)->get();

                foreach ($profiles as $profile) {
                    // for this part, I prefer to save it on mongodb. But, since the mongodb extension for laravel 10 is not stable yet.
                    // I keep it on MySQL for this case.
                    $sendEmail = new SendEmail();
                    $sendEmail->user_id = $profile->user_id;
                    $sendEmail->sent_status = 0;
                    $sendEmail->save();

                    $mailApi = Http::post(config('app.mail_api'), [
                        'email'=> $profile->user->email,
                        'message'=> "Hey, ".$profile->user->name." it's your birthday."
                    ]);

                    if ($mailApi->status() == 200) {
                        $sendEmail->sent_status = 1;
                        $sendEmail->save();
                    }

                }
            }
        }
    }
}
