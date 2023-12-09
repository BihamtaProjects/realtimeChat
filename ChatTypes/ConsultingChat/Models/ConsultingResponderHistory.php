<?php

namespace Modules\Chat\ChatTypes\ConsultingChat\Models;

use App\Models\User;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Modules\Chat\ChatTypes\ConsultingChat\Database\factories\ConsultingResponderHistoryFactory;
use Modules\Doctor\Models\Doctor;

/**
 * Modules\Chat\ChatTypes\ConsultingChat\Models\ConsultingResponderHistory
 *
 * @property int $id
 * @property int $consulting_chat_id
 * @property int $doctor_id
 * @property int $status
 * @property int $past_status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read ConsultingChat $consultingchat
 * @property-read User $user
 * @method static ConsultingResponderHistoryFactory factory($count = null, $state = [])
 * @method static Builder|ConsultingResponderHistory newModelQuery()
 * @method static Builder|ConsultingResponderHistory newQuery()
 * @method static Builder|ConsultingResponderHistory query()
 * @method static Builder|ConsultingResponderHistory whereConsultingchatId($value)
 * @method static Builder|ConsultingResponderHistory whereCreatedAt($value)
 * @method static Builder|ConsultingResponderHistory whereId($value)
 * @method static Builder|ConsultingResponderHistory wherePastStatus($value)
 * @method static Builder|ConsultingResponderHistory whereDocterId($value)
 * @method static Builder|ConsultingResponderHistory whereStatus($value)
 * @method static Builder|ConsultingResponderHistory whereUpdatedAt($value)
 * @mixin Eloquent
 */
class ConsultingResponderHistory extends Model
{
    use HasFactory;

    protected $guarded = [];

    public const STATUS_ADMIN_MOVE = 1; //(admin moved the consultingChat to another doctor)
    public const STATUS_PATIENT_MOVE = 2; //(patient moved the consultingChat to another doctor)
    public const STATUS_WAITING_NOT_IN_MY_SPECIALTY = 3; //(doctor has chosen not in my specialty message)
    public const STATUS_WAITING_NOT_IN_MY_INTERESTS = 4;//(doctor has chosen not in my interests message)
    public const STATUS_SPECIFIC_TIME_PASSED = 5;//(specific time passed but doctor still didn't answer the question)

    protected static function newFactory()
    {
        return ConsultingResponderHistoryFactory::new();
    }
    /**
     * Relation belongsTo  user
     *
     * @return BelongsTo
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class,'old_doctor_id');
    }
    /**
     * Relation belongsTo  consultingChat
     *
     * @return BelongsTo
     */
    public function consultingChat(): BelongsTo
    {
        return $this->belongsTo(ConsultingChat::class);
    }

}
