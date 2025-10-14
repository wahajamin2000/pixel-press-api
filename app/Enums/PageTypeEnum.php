<?php

namespace App\Enums;

enum PageTypeEnum: string
{
    case INDEX     = 'index';
    case TRASH     = 'trash';
    case DASHBOARD = 'dashboard';
}
