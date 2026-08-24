<?php
// Role and permission helpers

require_once __DIR__ . '/../config/database.php';

function get_role_permissions(string $role): array
{
    $pdo = db();
    $stmt = $pdo->prepare('
        SELECT p.key
        FROM permissions p
        JOIN role_permissions rp ON rp.permission_id = p.id
        JOIN roles r ON r.id = rp.role_id
        WHERE r.key = :role
    ');
    $stmt->execute([':role' => $role]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function has_permission(string $role, string $permissionKey): bool
{
    static $cache = [];
    if (!isset($cache[$role])) {
        $cache[$role] = get_role_permissions($role);
    }
    return in_array($permissionKey, $cache[$role], true);
}

function require_permission(string $permissionKey): void
{
    $staff = require_restaurant_auth();
    if (!has_permission($staff['role'], $permissionKey)) {
        header('HTTP/1.1 403 Forbidden');
        exit('Forbidden: You do not have permission to access this resource.');
    }
}

function require_role(array $allowedRoles): void
{
    $staff = require_restaurant_auth();
    if (!in_array($staff['role'], $allowedRoles, true)) {
        header('HTTP/1.1 403 Forbidden');
        exit('Forbidden: You do not have the required role.');
    }
}

function can_access_section(string $permissionKey): bool
{
    $staff = require_restaurant_auth();
    return has_permission($staff['role'], $permissionKey);
}

function can_access_any(array $permissions): bool
{
    foreach ($permissions as $perm) {
        if (can_access_section($perm)) {
            return true;
        }
    }
    return false;
}