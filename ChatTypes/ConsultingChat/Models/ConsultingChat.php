<?php

namespace Modules\Chat\ChatTypes\ConsultingChat\Models;

use App\Models\User;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Carbon;
use Modules\Chat\ChatTypes\ConsultingChat\Traits\HasPlace;
use Modules\Chat\ChatTypes\ConsultingChat\Traits\HasPlaces;
use Modules\Chat\Models\Chat;
use Modules\Chat\ChatTypes\ConsultingChat\Database\factories\ConsultingChatFactory;
use Modules\Financial\Models\Invoice;

/**
 * Modules\Chat\ChatTypes\ConsultingChat\Models\ConsultingChat
 *
 * @property int $id
 * @property string $unique_id
 * @property int $status
 * @property int $chat_id
 * @property int $related_patient_id
 * @property Carbon|null $open_time
 * @property int $private
 * @property int $priority
 * @property int $visit_number
 * @property string|null $doctor_last_answer_at
 * @property int $is_payment_notification_sent
 * @property int $view_counter
 * @property int $after_specified_time
 * @property int $is_auto_close
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Chat $chat
 * @property-read Collection<int, ConsultingResponderHistory> $consultingResponderHistories
 * @property-read int|null $consulting_moves_count
 * @property-read Invoice|null $invoice
 * @property-read User $user
 * @method static ConsultingChatFactory factory($count = null, $state = [])
 * @method static Builder|ConsultingChat newModelQuery()
 * @method static Builder|ConsultingChat newQuery()
 * @method static Builder|ConsultingChat query()
 * @method static Builder|ConsultingChat whereAfterSpecifiedTime($value)
 * @method static Builder|ConsultingChat whereChatId($value)
 * @method static Builder|ConsultingChat whereCreatedAt($value)
 * @method static Builder|ConsultingChat whereDoctorLastAnswerAt($value)
 * @method static Builder|ConsultingChat whereId($value)
 * @method static Builder|ConsultingChat whereIsAutoClose($value)
 * @method static Builder|ConsultingChat whereIsPaymentNotificationSent($value)
 * @method static Builder|ConsultingChat whereOpenTime($value)
 * @method static Builder|ConsultingChat wherePriority($value)
 * @method static Builder|ConsultingChat wherePrivate($value)
 * @method static Builder|ConsultingChat whereRelatedPatient($value)
 * @method static Builder|ConsultingChat whereStatus($value)
 * @method static Builder|ConsultingChat whereUniqueId($value)
 * @method static Builder|ConsultingChat whereUpdatedAt($value)
 * @method static Builder|ConsultingChat whereViewCounter($value)
 * @method static Builder|ConsultingChat whereVisitNumber($value)
 * @mixin Eloquent
 */
class ConsultingChat extends Model
{
    use HasFactory;
    use HasPlace;

    protected $guarded = [];

    protected $hidden = [
        'created_at',
        'updated_at',

    ];

    protected $with = [
        'chat'
    ];

    protected $casts = [
      'open_time' => 'datetime',
      'doctor_first_answer_at' => 'datetime'
    ];

    const TABLE = 'consulting_chats';
    protected $table = self::TABLE;

    public const STATUS_USER_RESPOND  = 1;
    public const STATUS_DOCTOR_RESPOND = 2; //means archived at the same time
    public const STATUS_CLOSE = 3; //means archived at the same time
    public const STATUS_PENDING = 4; //(draft)
    public const STATUS_OPEN = 5; //(being open means has paid at the same time)
    public const STATUS_DOCTOR_FREE = 6; //(being doctor free means chat is open but doctor free)
    public const STATUS_REFUND = 7; //means closed at the same time
    public const STATUS_REQUEST_OPEN = 8;
    public const STATUS_REQUEST_OPEN_ACCEPT = 9;
    public const STATUS_REQUEST_OPEN_REJECT = 10;


    public const PRIVATE_STATUS_PRIVATE = 1;
    public const PRIVATE_STATUS_ASKED_FOR_PUBLIC = 2;
    public const PRIVATE_STATUS_CONFIRMED_PUBLIC = 3;

    public const WAITE_FOR_DOCTOR = 1;
    public const SEND_TO_ANOTHER_DOCTOR = 2;
    public const REFUND_MONEY_TO_WALLET = 3;

    protected static function newFactory()
    {
        return ConsultingChatFactory::new();
    }

    /**
     * Relation belongsTo with Chat
     *
     * @return BelongsTo
     */
    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class, 'chat_id', 'id');
    }

    /**
     * Relation morphOne with invoice
     *
     * @return morphOne
     */
    public function invoice()
    {
        return $this->morphOne(Invoice::class, 'invoiceable');
    }

    /**
     * Relation belongsTo  user
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    /**
     * Relation hasMany consultingMove
     *
     * @return hasMany
     */
    public function consultingResponderHistories(): hasMany
    {
        return $this->hasMany(ConsultingResponderHistory::class,'consulting_chat_id','id');
    }

    /**
     * @return bool
     */
    public function isWaitForDoctor(): bool
    {
        return $this->after_specified_time === self::WAITE_FOR_DOCTOR;
    }
    /**
     * @return bool
     */
    public function isSendToAnotherDoctor(): bool
    {
        return $this->after_specified_time === self::SEND_TO_ANOTHER_DOCTOR;
    }
    /**
     * @return bool
     */
    public function isDoctorFree(): bool
    {
        return $this->status === self::STATUS_DOCTOR_FREE;
    }
    /**
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

}
