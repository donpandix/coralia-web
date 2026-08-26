<?php

namespace App\Actions\Pieces;

use App\Enums\OrganizationMembershipStatus;
use App\Enums\PieceFileType;
use App\Enums\PieceShareType;
use App\Enums\PieceStatus;
use App\Enums\VoiceType;
use App\Models\Organization;
use App\Models\OrganizationMembership;
use App\Models\Piece;
use App\Models\PieceFile;
use App\Models\User;
use App\Notifications\NewPieceAvailable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class SavePiece
{
    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, UploadedFile|null>  $uploads
     */
    public function handle(User $actor, Organization $organization, ?Piece $piece, array $data, array $uploads): Piece
    {
        $isNew = $piece === null;

        $piece = DB::transaction(function () use ($actor, $organization, $piece, $data, $uploads): Piece {
            $piece ??= new Piece(['organization_id' => $organization->id, 'created_by' => $actor->id]);
            $piece->fill([
                ...Arr::only($data, ['title', 'subtitle', 'body', 'status']),
                'updated_by' => $actor->id,
            ]);
            $piece->published_at ??= now();
            $piece->archived_at = $data['status'] === PieceStatus::Archived->value ? now() : null;
            $piece->save();

            $piece->tags()->sync($data['tagIds']);
            $this->replaceShares($piece, $actor, $data);
            $this->storeUploads($piece, $actor, $uploads);

            return $piece->refresh();
        });

        if ($isNew) {
            Notification::send($this->recipientUsers($piece), (new NewPieceAvailable($piece))->afterCommit());
        }

        return $piece;
    }

    /** @param array<string, mixed> $data */
    private function replaceShares(Piece $piece, User $actor, array $data): void
    {
        $piece->shares()->delete();

        if ($data['shareOrganization']) {
            $piece->shares()->create(['share_type' => PieceShareType::Organization, 'created_by' => $actor->id]);
        }

        foreach ($data['voiceShares'] as $voiceType) {
            $piece->shares()->create(['share_type' => PieceShareType::Voice, 'voice_type' => $voiceType, 'created_by' => $actor->id]);
        }

        foreach ($data['groupIds'] as $groupId) {
            $piece->shares()->create(['share_type' => PieceShareType::Group, 'group_id' => $groupId, 'created_by' => $actor->id]);
        }

        foreach ($data['membershipIds'] as $membershipId) {
            $piece->shares()->create(['share_type' => PieceShareType::Member, 'membership_id' => $membershipId, 'created_by' => $actor->id]);
        }
    }

    /** @param array<string, UploadedFile|null> $uploads */
    private function storeUploads(Piece $piece, User $actor, array $uploads): void
    {
        $definitions = [
            'score' => [PieceFileType::Score, VoiceType::General],
            'audioSoprano' => [PieceFileType::Audio, VoiceType::Soprano],
            'audioAlto' => [PieceFileType::Audio, VoiceType::Alto],
            'audioTenor' => [PieceFileType::Audio, VoiceType::Tenor],
            'audioBass' => [PieceFileType::Audio, VoiceType::Bass],
        ];

        foreach ($definitions as $property => [$fileType, $voiceType]) {
            $upload = $uploads[$property] ?? null;

            if (! $upload instanceof UploadedFile) {
                continue;
            }

            $existingFile = $piece->files()->where('file_type', $fileType)->where('voice_type', $voiceType)->first();
            $path = $upload->store('pieces/'.$piece->public_id, 'local');

            abort_if($path === false, 500, 'No pudimos almacenar el archivo.');

            PieceFile::query()->updateOrCreate(
                ['piece_id' => $piece->id, 'file_type' => $fileType, 'voice_type' => $voiceType],
                ['storage_disk' => 'local', 'storage_path' => $path, 'original_filename' => $upload->getClientOriginalName(), 'mime_type' => $upload->getMimeType() ?: 'application/octet-stream', 'file_size' => $upload->getSize(), 'duration_seconds' => null, 'checksum' => hash_file('sha256', $upload->getRealPath()), 'created_by' => $actor->id],
            );

            if ($existingFile !== null && $existingFile->storage_path !== $path) {
                Storage::disk($existingFile->storage_disk)->delete($existingFile->storage_path);
            }
        }
    }

    /** @return Collection<int, User> */
    private function recipientUsers(Piece $piece): Collection
    {
        $shares = $piece->shares()->get();
        $hasOrganizationShare = $shares->contains('share_type', PieceShareType::Organization);
        $voiceTypes = $shares
            ->where('share_type', PieceShareType::Voice)
            ->pluck('voice_type')
            ->map(fn (VoiceType $voiceType): string => $voiceType->value);
        $groupIds = $shares->where('share_type', PieceShareType::Group)->pluck('group_id');
        $membershipIds = $shares->where('share_type', PieceShareType::Member)->pluck('membership_id');

        $userIds = OrganizationMembership::query()
            ->forOrganization($piece->organization_id)
            ->where('status', OrganizationMembershipStatus::Active)
            ->where(function (Builder $query) use ($hasOrganizationShare, $voiceTypes, $groupIds, $membershipIds): void {
                if ($hasOrganizationShare) {
                    $query->whereNotNull('id');

                    return;
                }

                $query->whereIn('voice_type', $voiceTypes)
                    ->orWhereIn('id', $membershipIds)
                    ->orWhereHas('groups', fn (Builder $groupQuery) => $groupQuery->whereIn('groups.id', $groupIds));
            })
            ->pluck('user_id');

        return User::query()->whereKey($userIds)->get();
    }
}
