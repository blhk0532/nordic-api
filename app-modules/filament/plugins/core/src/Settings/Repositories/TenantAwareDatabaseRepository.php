<?php

declare(strict_types=1);

namespace Cachet\Settings\Repositories;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Spatie\LaravelSettings\Models\SettingsProperty;
use Spatie\LaravelSettings\SettingsRepositories\SettingsRepository;

final class TenantAwareDatabaseRepository implements SettingsRepository
{
    /** @var class-string<Model> */
    protected string $propertyModel;

    protected ?string $connection;

    protected ?string $table;

    protected string $settingsTableName;

    public function __construct(array $config)
    {
        $this->propertyModel = $config['model'] ?? SettingsProperty::class;
        $this->connection = $config['connection'] ?? null;
        $this->table = $config['table'] ?? null;
        $this->settingsTableName = $this->table ?? 'settings';
    }

    public function getPropertiesInGroup(string $group): array
    {
        if (! $this->settingsTableExists()) {
            return [];
        }

        $tenant = Filament::getTenant();
        $teamId = $tenant?->id;

        // Try tenant-specific settings first
        if (Schema::hasColumn($this->settingsTableName, 'team_id') && $teamId) {
            $results = $this->getBaseBuilder()
                ->where('group', $group)
                ->where('team_id', $teamId)
                ->get(['name', 'payload'])
                ->mapWithKeys(fn ($object) => [$object->name => $this->decode($object->payload, true)])
                ->toArray();

            if (! empty($results)) {
                return $results;
            }
        }

        // Fall back to global settings (team_id = NULL)
        return $this->getBaseBuilder()
            ->where('group', $group)
            ->whereNull('team_id')
            ->get(['name', 'payload'])
            ->mapWithKeys(fn ($object) => [$object->name => $this->decode($object->payload, true)])
            ->toArray();
    }

    public function checkIfPropertyExists(string $group, string $name): bool
    {
        if (! $this->settingsTableExists()) {
            return false;
        }

        $tenant = Filament::getTenant();
        $teamId = $tenant?->id;

        // Check tenant-specific first
        if (Schema::hasColumn($this->settingsTableName, 'team_id') && $teamId) {
            if ($this->getBaseBuilder()
                ->where('group', $group)
                ->where('name', $name)
                ->where('team_id', $teamId)
                ->exists()) {
                return true;
            }
        }

        // Fall back to global
        return $this->getBaseBuilder()
            ->where('group', $group)
            ->where('name', $name)
            ->whereNull('team_id')
            ->exists();
    }

    public function getPropertyPayload(string $group, string $name)
    {
        if (! $this->settingsTableExists()) {
            return null;
        }

        $tenant = Filament::getTenant();
        $teamId = $tenant?->id;

        // Try tenant-specific first
        if (Schema::hasColumn($this->settingsTableName, 'team_id') && $teamId) {
            $payload = $this->getBaseBuilder()
                ->where('group', $group)
                ->where('name', $name)
                ->where('team_id', $teamId)
                ->value('payload');

            if ($payload !== null) {
                return $payload;
            }
        }

        // Fall back to global
        return $this->getBaseBuilder()
            ->where('group', $group)
            ->where('name', $name)
            ->whereNull('team_id')
            ->value('payload');
    }

    public function createProperty(string $group, string $name, $payload): void
    {
        if (! $this->settingsTableExists()) {
            return;
        }

        $attributes = [
            'group' => $group,
            'name' => $name,
            'payload' => $this->encode($payload),
        ];

        if (Schema::hasColumn($this->settingsTableName, 'team_id')) {
            $tenant = Filament::getTenant();
            $attributes['team_id'] = $tenant?->id;
        }

        $this->getBaseBuilder()->create($attributes);
    }

    public function updatePropertiesPayload(string $group, array $properties): void
    {
        if (! $this->settingsTableExists()) {
            return;
        }

        $tenant = Filament::getTenant();
        $teamId = Schema::hasColumn($this->settingsTableName, 'team_id') ? $tenant?->id : null;

        foreach ($properties as $name => $payload) {
            $encoded = $this->encode($payload);

            if (Schema::hasColumn($this->settingsTableName, 'team_id') && $teamId) {
                // Update or create tenant-specific setting
                $this->getBaseBuilder()
                    ->where('group', $group)
                    ->where('name', $name)
                    ->where('team_id', $teamId)
                    ->update(['payload' => $encoded]);

                // If not found, create it
                if (! $this->getBaseBuilder()
                    ->where('group', $group)
                    ->where('name', $name)
                    ->where('team_id', $teamId)
                    ->exists()) {
                    $this->createProperty($group, $name, $payload);
                }
            } else {
                // Update global setting
                $this->getBaseBuilder()
                    ->where('group', $group)
                    ->where('name', $name)
                    ->whereNull('team_id')
                    ->update(['payload' => $encoded]);
            }
        }
    }

    public function deleteProperty(string $group, string $name): void
    {
        if (! $this->settingsTableExists()) {
            return;
        }

        $tenant = Filament::getTenant();
        $teamId = $tenant?->id;

        if (Schema::hasColumn($this->settingsTableName, 'team_id') && $teamId) {
            $this->getBaseBuilder()
                ->where('group', $group)
                ->where('name', $name)
                ->where('team_id', $teamId)
                ->delete();
        } else {
            $this->getBaseBuilder()
                ->where('group', $group)
                ->where('name', $name)
                ->whereNull('team_id')
                ->delete();
        }
    }

    public function lockProperties(string $group, array $properties): void
    {
        if (! $this->settingsTableExists()) {
            return;
        }

        $tenant = Filament::getTenant();
        $teamId = $tenant?->id;

        if (Schema::hasColumn($this->settingsTableName, 'team_id') && $teamId) {
            $this->getBaseBuilder()
                ->where('group', $group)
                ->whereIn('name', $properties)
                ->where('team_id', $teamId)
                ->update(['locked' => true]);
        } else {
            $this->getBaseBuilder()
                ->where('group', $group)
                ->whereIn('name', $properties)
                ->whereNull('team_id')
                ->update(['locked' => true]);
        }
    }

    public function unlockProperties(string $group, array $properties): void
    {
        if (! $this->settingsTableExists()) {
            return;
        }

        $tenant = Filament::getTenant();
        $teamId = $tenant?->id;

        if (Schema::hasColumn($this->settingsTableName, 'team_id') && $teamId) {
            $this->getBaseBuilder()
                ->where('group', $group)
                ->whereIn('name', $properties)
                ->where('team_id', $teamId)
                ->update(['locked' => false]);
        } else {
            $this->getBaseBuilder()
                ->where('group', $group)
                ->whereIn('name', $properties)
                ->whereNull('team_id')
                ->update(['locked' => false]);
        }
    }

    public function getLockedProperties(string $group): array
    {
        if (! $this->settingsTableExists()) {
            return [];
        }

        $tenant = Filament::getTenant();
        $teamId = $tenant?->id;

        if (Schema::hasColumn($this->settingsTableName, 'team_id') && $teamId) {
            // Try tenant-specific first
            $locked = $this->getBaseBuilder()
                ->where('group', $group)
                ->where('team_id', $teamId)
                ->where('locked', true)
                ->pluck('name')
                ->toArray();

            if (! empty($locked)) {
                return $locked;
            }

            // Fall back to global
            return $this->getBaseBuilder()
                ->where('group', $group)
                ->whereNull('team_id')
                ->where('locked', true)
                ->pluck('name')
                ->toArray();
        }

        return $this->getBaseBuilder()
            ->where('group', $group)
            ->whereNull('team_id')
            ->where('locked', true)
            ->pluck('name')
            ->toArray();
    }

    protected function settingsTableExists(): bool
    {
        return Schema::hasTable($this->settingsTableName);
    }

    protected function getBaseBuilder(): Builder
    {
        $query = $this->propertyModel::query();

        if ($this->connection) {
            $query = $query->on($this->connection);
        }

        if ($this->table) {
            $query = $query->from($this->table);
        }

        return $query;
    }

    protected function encode($payload): string
    {
        return json_encode($payload);
    }

    protected function decode(string $payload, bool $associative = false)
    {
        return json_decode($payload, $associative);
    }
}
