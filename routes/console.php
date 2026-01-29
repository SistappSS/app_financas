<?php

use App\Jobs\SendDailyDigestJob;
use App\Jobs\SendEveningReminderJob;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\ProcessRecurrences;

//Schedule::job(new ProcessRecurrences)->dailyAt('03:10');

Schedule::job(new SendDailyDigestJob)->dailyAt('08:00');
Schedule::job(new SendEveningReminderJob)->dailyAt('18:00');
//Schedule::job(new SendEveningReminderJob)->dailyAt('16:50');

