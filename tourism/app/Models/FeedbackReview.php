<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedbackReview extends Model
{
    protected $fillable = [
        'feedback_submission_id',
        'status',
        'is_verified',
        'verified_by',
        'moderated_by',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(FeedbackSubmission::class, 'feedback_submission_id');
    }

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }
}
