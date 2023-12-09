<?php

namespace Modules\Chat\ChatTypes\ConsultingChat\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Modules\Chat\ChatTypes\ConsultingChat\Database\factories\PrivateWordFactory;

/**
 * Modules\Chat\ChatTypes\ConsultingChat\Models\PrivateWord
 *
 * @property int $id
 * @property string $word
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static PrivateWordFactory factory($count = null, $state = [])
 * @method static Builder|PrivateWord newModelQuery()
 * @method static Builder|PrivateWord newQuery()
 * @method static Builder|PrivateWord query()
 * @method static Builder|PrivateWord whereCreatedAt($value)
 * @method static Builder|PrivateWord whereId($value)
 * @method static Builder|PrivateWord whereUpdatedAt($value)
 * @method static Builder|PrivateWord whereWord($value)
 * @mixin Eloquent
 */
class PrivateWord extends Model
{
    use HasFactory;

    protected $fillable = [];

    protected static function newFactory()
    {
        return PrivateWordFactory::new();
    }
}
