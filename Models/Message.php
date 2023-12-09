<?php

namespace Modules\Chat\Models;

use App\Models\User;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Modules\Chat\Database\factories\MessageFactory;
use Modules\Location\Models\Location;
use Modules\Location\Traits\HasLocation;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Modules\Chat\Models\Message
 *
 * @property int $id
 * @property int $chat_id
 * @property int $user_id
 * @property int|null $special_message_id
 * @property string|null $text
 * @property int|null $respond_to
 * @property int $is_delete
 * @property int $is_edit
 * @property string $seen
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Chat $chat
 * @property-read Location|null $location
 * @property-read MediaCollection<int, Media> $media
 * @property-read int|null $media_count
 * @property-read SpecialMessage|null $specialMessage
 * @property-read User $user
 * @method static MessageFactory factory($count = null, $state = [])
 * @method static Builder|Message newModelQuery()
 * @method static Builder|Message newQuery()
 * @method static Builder|Message query()
 * @method static Builder|Message whereChatId($value)
 * @method static Builder|Message whereCreatedAt($value)
 * @method static Builder|Message whereId($value)
 * @method static Builder|Message whereIsDelete($value)
 * @method static Builder|Message whereRespondTo($value)
 * @method static Builder|Message whereSpecialMessageId($value)
 * @method static Builder|Message whereText($value)
 * @method static Builder|Message whereUpdatedAt($value)
 * @method static Builder|Message whereUserId($value)
 * @mixin Eloquent
 */

class Message extends Model implements HasMedia
{
    use InteractsWithMedia;
    use HasFactory;
    use HasLocation;

    protected $guarded = [];
    const TABLE = 'messages';
    protected $table = self::TABLE;
    protected static function newFactory()
    {
        return MessageFactory::new();
    }
    protected $casts = [
        'seen' => 'array',
        'delivered'=>'array'
    ];
    /**
     * Relation belongs with university
     *
     * @return BelongsTo
     */
    public function chat(): BelongsTo
    {
        return $this->belongsTo(chat::class, 'chat_id', 'id');
    }

    /**
     * Relation belongsTo with User
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }


    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->useDisk('file_images')
            ->storeConversionsOnDisk('file_images');
        $this->addMediaCollection('videos');
        $this->addMediaCollection('voices');
        $this->addMediaCollection('documents');
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')->fit(Manipulations::FIT_CROP, 300, 300);
    }

    /**
     * Relation belongsTo with SpecialMessage
     *
     * @return BelongsTo
     */
    public function specialMessage()
    {
        return $this->belongsTo(SpecialMessage::class, 'special_message_id', 'id');
    }


    public function getSeenAttribute($seen)
    {
        return json_decode($seen);
    }

    public function setSeenAttribute($seen)
    {
        $this->attributes['seen'] = json_encode($seen);
    }

    public function getDeliveredAttribute($delivered)
    {
        return json_decode($delivered);
    }

    public function setDeliveredAttribute($delivered)
    {
        $this->attributes['delivered'] = json_encode($delivered);
    }
    /**
     * Get user role One of admin, doctor, patient or null
     *
     * Null role mean that user only import number and don't complete signup.
     *
     * @return string|null
     */
    public function getmethod() : string|null
    {
        $roles = [
            User::PATIENT,
            User::DOCTOR,
            User::ADMIN,
        ];

        foreach ($roles as $role) {
            if ($this->$role !== null) {
                return $role;
            }
        }

        return null;
    }
}
