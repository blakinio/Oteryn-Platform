<?php

namespace App\Admin;

final class AdminPermission
{
    public const ACCESS = 'admin.access';

    public const MANAGE_ROLES = 'admin.roles.manage';

    public const VIEW_AUDIT = 'audit.view';

    public const MANAGE_NEWS = 'cms.news.manage';

    public const MANAGE_PAGES = 'cms.pages.manage';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            self::ACCESS,
            self::MANAGE_ROLES,
            self::VIEW_AUDIT,
            self::MANAGE_NEWS,
            self::MANAGE_PAGES,
        ];
    }
}
