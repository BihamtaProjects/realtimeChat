<?php

namespace Modules\Chat\ChatTypes\ConsultingChat\Traits;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use Modules\Chat\ChatTypes\ConsultingChat\Models\ConsultingChat;

/**
 * Has places trait
 *
 * @property-read ConsultingChat|null $consultingChat
 */
trait HasPlace
{
    /**
     * Relation with place
     *
     * @return MorphOne
     */
    public function place(): Morphone
    {
        return $this->morphOne(ConsultingChat::class, 'placeable');
    }

}
