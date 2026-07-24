<?php

namespace App\PublicPortal\Navigation;

use Illuminate\Support\Facades\Route;

final class PublicNavigationRegistry
{
    /**
     * @return list<array{label: string, url: string, active: string, priority: int}>
     */
    public function header(): array
    {
        $items = [];

        foreach ($this->definitions() as $definition) {
            $header = $definition['header'] ?? [];

            if (! is_array($header)) {
                continue;
            }

            foreach ($header as $item) {
                $normalized = $this->normalizeItem($item);

                if ($normalized !== null) {
                    $items[] = $normalized;
                }
            }
        }

        usort($items, static fn (array $left, array $right): int => $left['priority'] <=> $right['priority']);

        return $items;
    }

    /**
     * @return list<array{label: string, priority: int, items: list<array{label: string, url: string, active: string, priority: int}>}>
     */
    public function footer(): array
    {
        /** @var array<string, array{label: string, priority: int, items: list<array{label: string, url: string, active: string, priority: int}>}> $groups */
        $groups = [];

        foreach ($this->definitions() as $definition) {
            $footer = $definition['footer'] ?? [];

            if (! is_array($footer)) {
                continue;
            }

            foreach ($footer as $group) {
                if (! is_array($group)) {
                    continue;
                }

                $key = $group['key'] ?? null;
                $label = $group['label'] ?? null;
                $priority = $group['priority'] ?? 100;
                $groupItems = $group['items'] ?? [];

                if (! is_string($key) || $key === '' || ! is_string($label) || $label === '' || ! is_int($priority) || ! is_array($groupItems)) {
                    continue;
                }

                $groups[$key] ??= [
                    'label' => $label,
                    'priority' => $priority,
                    'items' => [],
                ];

                foreach ($groupItems as $item) {
                    $normalized = $this->normalizeItem($item);

                    if ($normalized !== null) {
                        $groups[$key]['items'][] = $normalized;
                    }
                }
            }
        }

        foreach ($groups as &$group) {
            usort($group['items'], static fn (array $left, array $right): int => $left['priority'] <=> $right['priority']);
        }
        unset($group);

        $groups = array_values(array_filter(
            $groups,
            static fn (array $group): bool => $group['items'] !== [],
        ));
        usort($groups, static fn (array $left, array $right): int => $left['priority'] <=> $right['priority']);

        return $groups;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function definitions(): array
    {
        $files = glob(resource_path('navigation/public/*.php'));

        if ($files === false) {
            return [];
        }

        sort($files, SORT_STRING);
        $definitions = [];

        foreach ($files as $file) {
            $definition = require $file;

            if (is_array($definition)) {
                $definitions[] = $definition;
            }
        }

        return $definitions;
    }

    /**
     * @return array{label: string, url: string, active: string, priority: int}|null
     */
    private function normalizeItem(mixed $item): ?array
    {
        if (! is_array($item)) {
            return null;
        }

        $label = $item['label'] ?? null;
        $route = $item['route'] ?? null;
        $active = $item['active'] ?? $route;
        $priority = $item['priority'] ?? 100;
        $fragment = $item['fragment'] ?? null;

        if (! is_string($label) || $label === '' || ! is_string($route) || $route === '' || ! is_string($active) || $active === '' || ! is_int($priority) || ! Route::has($route)) {
            return null;
        }

        if ($fragment !== null && (! is_string($fragment) || $fragment === '')) {
            return null;
        }

        $url = route($route);

        if (is_string($fragment)) {
            $url .= '#'.rawurlencode($fragment);
        }

        return [
            'label' => $label,
            'url' => $url,
            'active' => $active,
            'priority' => $priority,
        ];
    }
}
