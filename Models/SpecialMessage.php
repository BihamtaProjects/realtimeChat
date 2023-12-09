<?php

namespace Modules\Chat\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Modules\Chat\Database\factories\SpecialMessageFactory;

/**
 * Modules\Chat\Models\SpecialMessage
 *
 * @property int $id
 * @property string $content
 * @property string|null $controller_method
 * @property string|null $role
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Message> $messages
 * @property-read int|null $messages_count
 * @method static SpecialMessageFactory factory($count = null, $state = [])
 * @method static Builder|SpecialMessage newModelQuery()
 * @method static Builder|SpecialMessage newQuery()
 * @method static Builder|SpecialMessage query()
 * @method static Builder|SpecialMessage whereContent($value)
 * @method static Builder|SpecialMessage whereControllerMethod($value)
 * @method static Builder|SpecialMessage whereCreatedAt($value)
 * @method static Builder|SpecialMessage whereId($value)
 * @method static Builder|SpecialMessage whereRole($value)
 * @method static Builder|SpecialMessage whereUpdatedAt($value)
 * @mixin Eloquent
 */
class SpecialMessage extends Model
{
    use HasFactory;

    protected $guarded = [];

    const TABLE = 'special_messages';

    protected $table = self::TABLE;

    public const STATUS_CONSULTING_DOCTOR = 1;
    public const STATUS_CONSULTING_DOCTOR_ARCHIVE = 2;
    public const STATUS_CONSULTING_PATIENT = 3;
    public const STATUS_ADMIN_TICKET = 4;
    public const STATUS_USER_TICKET = 5;

    protected static function newFactory()
    {
        return SpecialMessageFactory::new();
    }

    /**
     * Relation belongs with message
     *
     * @return HasMany
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function getStatusAttribute($status)
    {
        return json_decode($status);
    }

    public function setStatusAttribute($status)
    {
        $this->attributes['status'] = (int) $status;    }

    public function scopeFilterByStatus($query, $status)
    {
        return $query->whereJsonContains('status', $status);

    }
}
