<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Document;
use App\Models\Image;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Spatie\Activitylog\Enums\ActivityEvent;
use Spatie\Activitylog\Models\Activity;

final class ActivityLog
{
    /**
     * @var list<string>
     */
    private const array HIDDEN_ATTRIBUTES = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    public static function eventOptions(): array
    {
        return [
            ActivityEvent::Created->value => 'Criado',
            ActivityEvent::Updated->value => 'Atualizado',
            ActivityEvent::Deleted->value => 'Excluído',
            ActivityEvent::Restored->value => 'Restaurado',
        ];
    }

    /**
     * @return array<class-string<Model>, string>
     */
    public static function subjectTypeOptions(): array
    {
        return [
            User::class => 'Usuário',
            Document::class => 'Documento',
            Image::class => 'Imagem',
            Role::class => 'Perfil',
            Permission::class => 'Permissão',
        ];
    }

    public static function eventLabel(?string $event): string
    {
        if ($event === null || $event === '') {
            return '—';
        }

        return self::eventOptions()[$event] ?? $event;
    }

    public static function eventColor(?string $event): string
    {
        return match ($event) {
            ActivityEvent::Created->value => 'success',
            ActivityEvent::Updated->value => 'warning',
            ActivityEvent::Deleted->value => 'danger',
            ActivityEvent::Restored->value => 'info',
            default => 'gray',
        };
    }

    public static function subjectTypeLabel(?string $type): string
    {
        if ($type === null || $type === '') {
            return '—';
        }

        return self::subjectTypeOptions()[$type] ?? class_basename($type);
    }

    public static function subjectLabel(Activity $activity): string
    {
        $typeLabel = self::subjectTypeLabel($activity->subject_type);
        $subject = $activity->subject;

        if (! $subject instanceof Model) {
            if ($activity->subject_id === null) {
                return $typeLabel;
            }

            return "{$typeLabel} #{$activity->subject_id}";
        }

        $name = $subject->getAttribute('name');

        if (is_string($name) && $name !== '') {
            return "{$typeLabel}: {$name}";
        }

        return "{$typeLabel} #{$subject->getKey()}";
    }

    /**
     * @return array<string, string>
     */
    public static function oldValues(Activity $activity): array
    {
        return self::values($activity->attribute_changes, 'old');
    }

    /**
     * @return array<string, string>
     */
    public static function newValues(Activity $activity): array
    {
        return self::values($activity->attribute_changes, 'attributes');
    }

    public static function hasChanges(Activity $activity): bool
    {
        return self::oldValues($activity) !== [] || self::newValues($activity) !== [];
    }

    /**
     * @param  Collection<string, mixed>|null  $changes
     * @return array<string, string>
     */
    private static function values(?Collection $changes, string $group): array
    {
        $raw = $changes?->get($group);

        if (! is_array($raw)) {
            return [];
        }

        $formatted = [];

        foreach (Arr::except($raw, self::HIDDEN_ATTRIBUTES) as $key => $value) {
            $formatted[(string) $key] = self::stringify($value);
        }

        return $formatted;
    }

    private static function stringify(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? 'Sim' : 'Não';
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        $encoded = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return $encoded === false ? '' : $encoded;
    }
}
