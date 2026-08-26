<?php

namespace App\Models;

use App\Enums\ReportStatus;
use App\Enums\ReportTargetType;
use App\Models\Concerns\BelongsToOrganization;
use App\Models\Concerns\HasPublicId;
use Database\Factories\ReportFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['reporter_user_id', 'organization_id', 'target_type', 'target_id', 'reason', 'description', 'status', 'resolved_by', 'resolved_at', 'resolution_notes'])]
class Report extends Model
{
    /** @use HasFactory<ReportFactory> */
    use BelongsToOrganization, HasFactory, HasPublicId;

    /** @return BelongsTo<User, $this> */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_user_id');
    }

    /** @return BelongsTo<Organization, $this> */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /** @return BelongsTo<User, $this> */
    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
            'status' => ReportStatus::class,
            'target_type' => ReportTargetType::class,
        ];
    }
}
