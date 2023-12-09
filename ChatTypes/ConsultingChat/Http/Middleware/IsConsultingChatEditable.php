<?php

namespace Modules\Chat\ChatTypes\ConsultingChat\Http\Middleware;

use App\Enums\UserRoleEnum;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Modules\Chat\ChatTypes\ConsultingChat\Exceptions\EditRulesException;
use Modules\Chat\ChatTypes\ConsultingChat\Models\ConsultingChat;
use Modules\Doctor\Models\Doctor;

class IsConsultingChatEditable
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     * @throws EditRulesException
     */
    public function handle(Request $request, Closure $next): mixed
    {
        /** @var ConsultingChat $consultingChat */
        $consultingChat = $request->route('consultingChat');
        $chat = $consultingChat->chat;
        $doctorUser = $chat->users()->where('role',UserRoleEnum::Doctor)->first();
        $doctor = Doctor::where('user_id',$doctorUser->id)->first();
        $averageTimeInHour = $doctor->guaranteed_answering_question ? $doctor->manual_question_avarage_time_responding : floor($doctor->avarage_time_responding_question/3600000);
        $openTime = $consultingChat->open_time;

        $isPassPredictedTime = false;
        if($consultingChat->isSendToAnotherDoctor()){
            $isPassPredictedTime = $openTime->diffInHours(Carbon::now()) >= $averageTimeInHour;
        }

        $isPassThreeDays = false;
        if($consultingChat->isWaitForDoctor() && $consultingChat->doctor_last_answer_at === null && isset($openTime)){
            $isPassThreeDays = $openTime->diffInHours(Carbon::now()) >= 72;
        }

        if( $consultingChat->isPending() || $consultingChat->isDoctorFree() || $isPassThreeDays || $isPassPredictedTime) {
            return $next($request);
        } else {
            throw new  EditRulesException();
        }
    }
}
