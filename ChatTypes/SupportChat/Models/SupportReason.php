<?php

namespace Modules\Chat\ChatTypes\SupportChat\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Modules\Chat\ChatTypes\SupportChat\Database\factories\SupportReasonFactory;

/**
 * Modules\Chat\ChatTypes\SupportChat\Models\SupportReason
 *
 * @property int $id
 * @property string $title
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, SupportChat> $supportChats
 * @property-read int|null $support_chats_count
 * @method static SupportReasonFactory factory($count = null, $state = [])
 * @method static Builder|SupportReason newModelQuery()
 * @method static Builder|SupportReason newQuery()
 * @method static Builder|SupportReason query()
 * @method static Builder|SupportReason whereCreatedAt($value)
 * @method static Builder|SupportReason whereId($value)
 * @method static Builder|SupportReason whereTitle($value)
 * @method static Builder|SupportReason whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class SupportReason extends Model
{
    use HasFactory;

    protected $guarded = [];
    const TABLE = 'support_reasons';
    protected $table = self::TABLE;

    protected static function newFactory()
    {
        return SupportReasonFactory::new();
    }
    /**
     * Relation HasMany with SupportChat
     *
     * @return HasMany
     */
    public function supportChats(): HasMany
    {
        return $this->hasMany(SupportChat::class);
    }

}
