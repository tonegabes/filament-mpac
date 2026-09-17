<?php

declare(strict_types=1);

use App\Models\Document;
use App\Models\User;
use App\Support\ActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Enums\ActivityEvent;
use Spatie\Activitylog\Models\Activity;

uses(RefreshDatabase::class);

it('translates known events and subject types', function (): void {
    expect(ActivityLog::eventLabel(ActivityEvent::Created->value))->toBe('Criado')
        ->and(ActivityLog::eventColor(ActivityEvent::Deleted->value))->toBe('danger')
        ->and(ActivityLog::subjectTypeLabel(User::class))->toBe('Usuário')
        ->and(ActivityLog::subjectTypeLabel(Document::class))->toBe('Documento')
        ->and(ActivityLog::eventLabel(null))->toBe('—');
});

it('builds a subject label from the related model name', function (): void {
    $user = User::factory()->create(['name' => 'Maria Silva']);

    $activity = Activity::query()
        ->where('subject_type', $user->getMorphClass())
        ->where('subject_id', $user->id)
        ->firstOrFail();

    $activity->setRelation('subject', $user);

    expect(ActivityLog::subjectLabel($activity))->toBe('Usuário: Maria Silva');
});

it('hides password values from activity changes', function (): void {
    $activity = new Activity;
    $activity->attribute_changes = collect([
        'attributes' => [
            'name' => 'Novo',
            'password' => 'secret-hash',
        ],
        'old' => [
            'name' => 'Antigo',
            'password' => 'old-hash',
        ],
    ]);

    expect(ActivityLog::newValues($activity))->toBe(['name' => 'Novo'])
        ->and(ActivityLog::oldValues($activity))->toBe(['name' => 'Antigo'])
        ->and(ActivityLog::hasChanges($activity))->toBeTrue();
});
