<?php

namespace Modules\Chat\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DoctorUserBlocked extends Model
{
    use HasFactory;

    protected $guarded = [];

    public const BLOCK_TYPE_ADMIN = 0;
    public const BLOCK_TYPE_QUESTION = 1;
}
