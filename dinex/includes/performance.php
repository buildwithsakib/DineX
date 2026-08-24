<?php
// Performance helpers

function paginate(int $total, int $perPage = 20, int $currentPage = 1): array
{
    $totalPages = max(1, ceil($total / $perPage));
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $perPage;
    return [
        'total' => $total,
        'per_page' => $perPage,
        'current_page' => $currentPage,
        'total_pages' => $totalPages,
        'offset' => $offset,
    ];
}

function cache_set(string $key, $value, int $ttl = 300): void
{
    $file = LOG_PATH . '/cache_' . md5($key) . '.cache';
    file_put_contents($file, serialize(['expires' => time() + $ttl, 'data' => $value]));
}

function cache_get(string $key)
{
    $file = LOG_PATH . '/cache_' . md5($key) . '.cache';
    if (file_exists($file)) {
        $cache = unserialize(file_get_contents($file));
        if ($cache['expires'] > time()) {
            return $cache['data'];
        }
        unlink($file);
    }
    return null;
}