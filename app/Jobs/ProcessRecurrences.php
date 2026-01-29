<?php
//
//namespace App\Jobs;
//
//use App\Helpers\RecurrenceDate;
//use App\Models\Recurrent;
//use App\Models\Transaction;
//use Carbon\Carbon;
//use Illuminate\Bus\Queueable;
//use Illuminate\Contracts\Queue\ShouldQueue;
//use Illuminate\Foundation\Bus\Dispatchable;
//
//class ProcessRecurrences implements ShouldQueue
//{
//    use Dispatchable, Queueable;
//
//    public function handle(): void
//    {
//        $today = Carbon::today();
//
//        Recurrent::query()
//            ->where('active', true)
//            ->whereNotNull('next_run_date')
//            ->where('next_run_date', '<=', $today)
//            ->chunkById(200, function ($batch) use ($today) {
//                foreach ($batch as $rec) {
//                    $tpl = $rec->transaction()->first();
//                    if (!$tpl) {
//                        $rec->active = false; $rec->save(); continue;
//                    }
//
//                    $next = Carbon::parse($rec->next_run_date);
//                    while ($next->lte($today)) {
//                        Transaction::create([
//                            'user_id' => $tpl->user_id,
//                            'card_id' => null,
//                            'transaction_category_id' => $tpl->transaction_category_id,
//                            'title' => $tpl->title,
//                            'description' => $tpl->description,
//                            'amount' => $rec->amount,
//                            'date' => $next,
//                            'type' => $tpl->type,
//                            'type_card' => $tpl->type_card,
//                            'recurrence_type' => 'unique',
//                            'custom_occurrences' => null,
//                        ]);
//
//                        $next = RecurrenceDate::next(
//                            $next,
//                            $rec->interval_unit,
//                            $rec->interval_value,
//                            (bool)$rec->include_sat,
//                            (bool)$rec->include_sun
//                        );
//                    }
//
//                    $rec->next_run_date = $next;
//                    $rec->save();
//                }
//            });
//    }
//}
