<?php

namespace App\Enums\Users;

enum PermissionEnum: string
{
    case VIEW = 'view';
    case EDIT = 'edit';
    case DELETE = 'delete';
    case CREATE = 'create';
    case ADMIN = 'admin';
}
