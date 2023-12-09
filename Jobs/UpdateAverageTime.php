<?php

namespace Modules\Chat\Jobs;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\Chat\ChatTypes\ConsultingChat\Models\ConsultingChat;
use Modules\Chat\Models\Chat;
use Modules\Doctor\Models\Doctor;

class UpdateAverageTime implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $chatId;
    private User $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($chatId, $user)
    {
        $this->user = $user;
        $this->chatId = $chatId;
    }

    /**
     * Execute the job.
     *
     */
    public function handle()
    {
//        $consultingChat = ConsultingChat::where('chat_id', $this->chatId)->first();
//        if ($consultingChat->doctor_first_answer_at == null && $this->user->getRole() == 'doctor') {
            ConsultingChat::where('chat_id', $this->chatId)
                ->update([
                    'doctor_first_answer_at' => Carbon::now(),
                    'doctor_last_answer_at' => Carbon::now(),
                ]);
            $now = Carbon::today()->endOfDay()->toDateTimeString();
            $twenty_days_before = Carbon::now()->subDays(20)->endOfDay()->toDateTimeString();

            $doctorChats = Chat::whereBetween('created_at', [$twenty_days_before, $now])
                ->whereHas('users', function ($q) {
                    $q->where('chat_user.role', 'doctor')
                        ->where('chat_user.user_id', $this->user->id);
                })->get();
            $sum = 0;
            $count = $doctorChats->count();
            foreach ($doctorChats as $doctorChat) {
                $consultingChat = ConsultingChat::where('chat_id', $doctorChat->id)->first();
                if ($consultingChat){
                    if ($consultingChat->doctor_first_answer_at != null) {
                        $diff = $consultingChat->doctor_first_answer_at->diffInHours($consultingChat->created_at);
                    }else{
                        $diff = Carbon::now()->diffInHours($consultingChat->created_at);
                    }
                    $sum = $sum + $diff;
                }
            }
            $sum = $sum / $count;
            if($count >=10) {
                Doctor::where('user_id', $this->user->id)
                    ->update([
                        'avarage_time_responding_question' => floor($sum * 3600000),
                        'question_avarage_time_responding' => floor($sum) . ' ' . 'ساعت'
                    ]);
            }
        }

//    }
}
