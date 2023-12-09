<?php

namespace Modules\Chat\ChatTypes\SupportChat\Models;

use Eloquent;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Model as ModelAlias;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Modules\Chat\Models\Chat;
use Modules\Chat\ChatTypes\SupportChat\Database\factories\SupportChatFactory;

/**
 * Modules\Chat\ChatTypes\SupportChat\Models\SupportChat
 *
 * @property int $id
 * @property int $status
 * @property int $is_login
 * @property int $chat_id
 * @property int $support_reason_id
 * @property int $related_question_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Chat $chat
 * @property-read SupportReason $supportReason
 * @method static SupportChatFactory factory($count = null, $state = [])
 * @method static Builder|SupportChat newModelQuery()
 * @method static Builder|SupportChat newQuery()
 * @method static Builder|SupportChat query()
 * @method static Builder|SupportChat whereChatId($value)
 * @method static Builder|SupportChat whereCreatedAt($value)
 * @method static Builder|SupportChat whereId($value)
 * @method static Builder|SupportChat whereIsLogin($value)
 * @method static Builder|SupportChat whereRelatedQuestionId($value)
 * @method static Builder|SupportChat whereStatus($value)
 * @method static Builder|SupportChat whereSupportReasonId($value)
 * @method static Builder|SupportChat whereUpdatedAt($value)
 * @mixin Eloquent
 */
class SupportChat extends ModelAlias
{
    use HasFactory;
   //waiting for support respond
    public const STATUS_USER_RESPOND= 1;
    //waiting for user respond
    public const STATUS_SUPPORT_RESPOND= 2;
    //unimportant tickets
    public const STATUS_unimportant= 3;
    //checked and ended
    public const STATUS_ENDED = 4;

    protected $guarded = [];

    const TABLE = 'support_chats';
    protected $table = self::TABLE;

    protected static function newFactory()
    {
        return SupportChatFactory::new();
    }
    /**
     * Relation belongsTo with Chat
     *
     * @return BelongsTo
     */
    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class,'chat_id','id');
    }
    /**
     * Relation belongsTo with SupportReason
     *
     * @return BelongsTo
     */
    public function supportReason(): BelongsTo
    {
        return $this->belongsTo(SupportReason::class,'support_reason_id','id');
    }
}
