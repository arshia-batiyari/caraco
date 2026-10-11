<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FeedbackSubmission extends Model
{
    protected $fillable = [
        'type',
        'is_anonymous',
        'name',
        'email',
        'phone',
        'wants_follow_up',
        'tour_id',
        'service_id',
        'rating_overall',
        'rating_accommodation',
        'rating_transport',
        'rating_organization',
        'rating_service',
        'title',
        'message',
        'locale',
        'preferred_language',
        'consent',
        'ip',
    ];

    protected function casts(): array
    {
        return [
            'is_anonymous' => 'boolean',
            'wants_follow_up' => 'boolean',
            'consent' => 'boolean',
            'rating_overall' => 'integer',
            'rating_accommodation' => 'integer',
            'rating_transport' => 'integer',
            'rating_organization' => 'integer',
            'rating_service' => 'integer',
        ];
    }

    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(FeedbackReview::class);
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(FeedbackFollowUp::class)->orderBy('id');
    }

    public function publicName(): string
    {
        if ($this->is_anonymous || ! filled($this->name)) {
            return __('ui.feedback.anonymous');
        }

        return (string) $this->name;
    }

    public function canBePublic(): bool
    {
        return $this->type !== 'complaint';
    }
}
