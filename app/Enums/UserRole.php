<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case NON_ADMIN = 'non_admin';
}
