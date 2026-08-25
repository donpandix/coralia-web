<?php

use Illuminate\Support\Facades\Schema;

test('creates the complete database schema', function () {
    $expectedColumns = [
        'users' => ['id', 'public_id', 'name', 'email', 'photo_path', 'description', 'is_super_admin', 'status', 'deleted_at'],
        'organizations' => ['id', 'public_id', 'name', 'owner_user_id', 'status', 'city', 'archived_at'],
        'organization_requests' => ['id', 'public_id', 'requested_by', 'organization_name', 'status', 'reviewed_by', 'organization_id'],
        'organization_memberships' => ['id', 'organization_id', 'user_id', 'role', 'voice_type', 'status', 'approved_by'],
        'groups' => ['id', 'public_id', 'organization_id', 'name', 'status', 'created_by', 'archived_at'],
        'group_members' => ['id', 'group_id', 'membership_id', 'created_at'],
        'tags' => ['id', 'public_id', 'name', 'slug', 'status', 'created_by'],
        'pieces' => ['id', 'public_id', 'organization_id', 'title', 'status', 'created_by', 'updated_by', 'published_at', 'archived_at'],
        'piece_tags' => ['piece_id', 'tag_id', 'created_at'],
        'piece_files' => ['id', 'public_id', 'piece_id', 'file_type', 'voice_type', 'storage_disk', 'storage_path', 'file_size', 'duration_seconds'],
        'piece_shares' => ['id', 'piece_id', 'share_type', 'voice_type', 'group_id', 'membership_id', 'created_by'],
        'favorites' => ['id', 'user_id', 'piece_id', 'created_at'],
        'piece_views' => ['id', 'user_id', 'piece_id', 'first_viewed_at', 'last_viewed_at', 'view_count'],
        'notification_preferences' => ['id', 'user_id', 'new_piece', 'voice_audio_added', 'membership_changes', 'administrative_events'],
        'notifications' => ['id', 'type', 'notifiable_type', 'notifiable_id', 'data', 'read_at'],
        'device_tokens' => ['id', 'user_id', 'platform', 'device_token', 'device_name', 'last_seen_at'],
        'reports' => ['id', 'public_id', 'reporter_user_id', 'organization_id', 'target_type', 'target_id', 'status', 'resolved_by'],
        'audit_logs' => ['id', 'user_id', 'organization_id', 'action', 'entity_type', 'entity_id', 'old_values', 'new_values', 'created_at'],
        'personal_access_tokens' => ['id', 'tokenable_type', 'tokenable_id', 'name', 'token', 'abilities', 'last_used_at', 'expires_at'],
    ];

    foreach ($expectedColumns as $table => $columns) {
        expect(Schema::hasTable($table))->toBeTrue()
            ->and(Schema::hasColumns($table, $columns))->toBeTrue();
    }
});

test('creates the required unique and query indexes', function () {
    $uniqueIndexes = [
        'users' => [['public_id'], ['email']],
        'organizations' => [['public_id']],
        'organization_requests' => [['public_id']],
        'organization_memberships' => [['organization_id', 'user_id']],
        'groups' => [['public_id'], ['organization_id', 'name']],
        'tags' => [['public_id'], ['name'], ['slug']],
        'pieces' => [['public_id']],
        'piece_tags' => [['piece_id', 'tag_id']],
        'piece_files' => [['public_id'], ['piece_id', 'file_type', 'voice_type']],
        'favorites' => [['user_id', 'piece_id']],
        'piece_views' => [['user_id', 'piece_id']],
        'notification_preferences' => [['user_id']],
        'device_tokens' => [['device_token']],
        'reports' => [['public_id']],
        'personal_access_tokens' => [['token']],
    ];

    $queryIndexes = [
        'organizations' => [['status'], ['owner_user_id'], ['name']],
        'organization_memberships' => [['user_id', 'status'], ['organization_id', 'status'], ['organization_id', 'role'], ['organization_id', 'voice_type']],
        'pieces' => [['organization_id', 'status'], ['organization_id', 'title'], ['published_at']],
        'piece_shares' => [['piece_id', 'share_type'], ['voice_type'], ['group_id'], ['membership_id']],
    ];

    foreach ($uniqueIndexes as $table => $indexes) {
        foreach ($indexes as $columns) {
            expect(Schema::hasIndex($table, $columns, 'unique'))->toBeTrue();
        }
    }

    foreach ($queryIndexes as $table => $indexes) {
        foreach ($indexes as $columns) {
            expect(Schema::hasIndex($table, $columns))->toBeTrue();
        }
    }
});

test('creates the required foreign keys', function () {
    $expectedForeignKeys = [
        'organizations' => ['owner_user_id' => 'users'],
        'organization_requests' => ['requested_by' => 'users', 'reviewed_by' => 'users', 'organization_id' => 'organizations'],
        'organization_memberships' => ['organization_id' => 'organizations', 'user_id' => 'users', 'approved_by' => 'users'],
        'groups' => ['organization_id' => 'organizations', 'created_by' => 'users'],
        'group_members' => ['group_id' => 'groups', 'membership_id' => 'organization_memberships'],
        'tags' => ['created_by' => 'users'],
        'pieces' => ['organization_id' => 'organizations', 'created_by' => 'users', 'updated_by' => 'users'],
        'piece_tags' => ['piece_id' => 'pieces', 'tag_id' => 'tags'],
        'piece_files' => ['piece_id' => 'pieces', 'created_by' => 'users'],
        'piece_shares' => ['piece_id' => 'pieces', 'group_id' => 'groups', 'membership_id' => 'organization_memberships', 'created_by' => 'users'],
        'favorites' => ['user_id' => 'users', 'piece_id' => 'pieces'],
        'piece_views' => ['user_id' => 'users', 'piece_id' => 'pieces'],
        'notification_preferences' => ['user_id' => 'users'],
        'device_tokens' => ['user_id' => 'users'],
        'reports' => ['reporter_user_id' => 'users', 'organization_id' => 'organizations', 'resolved_by' => 'users'],
        'audit_logs' => ['user_id' => 'users', 'organization_id' => 'organizations'],
    ];

    foreach ($expectedForeignKeys as $table => $foreignKeys) {
        $actualForeignKeys = collect(Schema::getForeignKeys($table))
            ->mapWithKeys(fn (array $foreignKey): array => [$foreignKey['columns'][0] => $foreignKey['foreign_table']])
            ->all();

        expect($actualForeignKeys)->toMatchArray($foreignKeys);
    }
});
