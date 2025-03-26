<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'action',
        'target_type',
        'target_id',
        'target_name',
        'properties',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'properties' => 'json',
    ];

    /**
     * Get the user that performed the activity.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Log an activity.
     *
     * @param string $action
     * @param string $targetType
     * @param string|null $targetId
     * @param string|null $targetName
     * @param array|null $properties
     * @param string|null $description
     * @return ActivityLog
     */
    public static function log(
        string  $action,
        string  $targetType,
        ?string $targetId = null,
        ?string $targetName = null,
        ?array  $properties = null,
        ?string $description = null
    ): ActivityLog
    {
        return self::create([
            'user_id'     => auth()->id(),
            'action'      => $action,
            'target_type' => $targetType,
            'target_id'   => $targetId,
            'target_name' => $targetName,
            'properties'  => $properties,
            'description' => $description,
        ]);
    }
}
