<?php

namespace CustomSidebarManager\Support;

use Illuminate\Support\Str;

final class CustomSidebarStore
{
    private const SETTING_KEY = 'custom_sidebars';

    public function __construct(private readonly ?object $plugin)
    {
    }

    /**
     * Return stored rows without discarding fields added by an older or newer
     * LeConfe release.
     *
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        if (! $this->plugin || ! method_exists($this->plugin, 'getSetting')) {
            return [];
        }

        $rows = $this->plugin->getSetting(self::SETTING_KEY, []);

        if (! is_array($rows)) {
            return [];
        }

        return array_values(array_filter($rows, 'is_array'));
    }

    /**
     * @return array<int, array{id: string, name: string, show_name: bool, content: string}>
     */
    public function rowsForModel(): array
    {
        return array_values(array_map(
            fn (array $row): array => $this->normalize($row),
            array_filter(
                $this->all(),
                fn (array $row): bool => $this->hasUsableId($row),
            ),
        ));
    }

    /**
     * @return array<int, array{id: string, name: string, show_name: bool, content: string}>
     */
    public function rowsForRegistration(): array
    {
        return $this->rowsForModel();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): bool
    {
        $rows = $this->all();
        $data['id'] = (string) Str::uuid();
        $rows[] = array_replace($data, $this->normalize($data));

        return $this->write($rows);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(string|int $id, array $data): bool
    {
        $rows = $this->all();
        $updated = false;

        foreach ($rows as $key => $row) {
            if (! $this->sameId($row['id'] ?? null, $id)) {
                continue;
            }

            // Preserve the original ID type and any version-specific fields.
            $rows[$key] = array_replace($row, [
                'name' => (string) ($data['name'] ?? ''),
                'show_name' => (bool) ($data['show_name'] ?? false),
                'content' => (string) ($data['content'] ?? ''),
            ]);
            $updated = true;
            break;
        }

        return $updated && $this->write($rows);
    }

    public function delete(string|int $id): bool
    {
        $rows = $this->all();
        $remaining = array_values(array_filter(
            $rows,
            fn (array $row): bool => ! $this->sameId($row['id'] ?? null, $id),
        ));

        if (count($remaining) === count($rows)) {
            return false;
        }

        return $this->write($remaining);
    }

    /**
     * @param array<string, mixed> $row
     * @return array{id: string, name: string, show_name: bool, content: string}
     */
    private function normalize(array $row): array
    {
        return [
            'id' => (string) ($row['id'] ?? ''),
            'name' => (string) ($row['name'] ?? ''),
            'show_name' => (bool) ($row['show_name'] ?? false),
            'content' => (string) ($row['content'] ?? ''),
        ];
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hasUsableId(array $row): bool
    {
        $id = $row['id'] ?? null;

        return is_int($id) || (is_string($id) && $id !== '');
    }

    private function sameId(mixed $left, string|int $right): bool
    {
        return (is_string($left) || is_int($left))
            && (string) $left === (string) $right;
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     */
    private function write(array $rows): bool
    {
        if (! $this->plugin || ! method_exists($this->plugin, 'updateSetting')) {
            return false;
        }

        return (bool) $this->plugin->updateSetting(self::SETTING_KEY, array_values($rows));
    }
}
