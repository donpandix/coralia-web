<?php

namespace App\Models;

use App\Enums\OrganizationRequestStatus;
use App\Models\Concerns\HasPublicId;
use Database\Factories\OrganizationRequestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['requested_by', 'organization_name', 'description', 'city', 'additional_info', 'status', 'reviewed_by', 'reviewed_at', 'review_notes', 'organization_id'])]
class OrganizationRequest extends Model
{
    /** @use HasFactory<OrganizationRequestFactory> */
    use HasFactory, HasPublicId;

    /** @return BelongsTo<User, $this> */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /** @return BelongsTo<Organization, $this> */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
            'status' => OrganizationRequestStatus::class,
        ];
    }
}
