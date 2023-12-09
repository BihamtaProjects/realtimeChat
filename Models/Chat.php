<?php

namespace Modules\Chat\Models;

use App\Models\User;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Modules\Chat\Database\factories\ChatFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Modules\Chat\Models\Chat
 *
 * @property int $id
 * @property string $title
 * @property string $content
 * @property int $status
 * @property int $last_status
 * @property int $create_user_id
 * @property int $last_respond_id
 * @property int|null $suspend_user_id
 * @property int $admin_read
 * @property int|null $last_answer_time
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Message> $messages
 * @property-read int|null $messages_count
 * @property-read Collection<int, User> $users
 * @property-read int|null $users_count
 * @method static ChatFactory factory($count = null, $state = [])
 * @method static Builder|Chat newModelQuery()
 * @method static Builder|Chat newQuery()
 * @method static Builder|Chat query()
 * @method static Builder|Chat whereAdminRead($value)
 * @method static Builder|Chat whereContent($value)
 * @method static Builder|Chat whereCreateUserId($value)
 * @method static Builder|Chat whereCreatedAt($value)
 * @method static Builder|Chat whereId($value)
 * @method static Builder|Chat whereLastAnswerTime($value)
 * @method static Builder|Chat whereLastRespondId($value)
 * @method static Builder|Chat whereLastStatus($value)
 * @method static Builder|Chat whereStatus($value)
 * @method static Builder|Chat whereSuspendUserId($value)
 * @method static Builder|Chat whereTitle($value)
 * @method static Builder|Chat whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Chat extends Model
{
    use HasFactory;

    const TABLE = 'chats';
    protected $table = self::TABLE;

    protected $guarded = [];

    protected $with = [
        'users'
    ];

    protected $casts = [
        'created_at' => 'datetime'
    ];

    protected $hidden = [
        'updated_at'
    ];

    protected static function newFactory()
    {
        return ChatFactory::new();
    }
    /**
     * Relation HasMany with Message
     *
     * @return HasMany
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Relation belongsToMany with user
     *
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'chat_user', 'chat_id', 'user_id')->withPivot(['role']);
    }

}
