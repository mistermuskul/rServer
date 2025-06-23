<?php

namespace App\Enums\Users;

enum RoleEnum: string
{
    case SuperAdmin = 'SuperAdmin';
    case Admin = 'Admin';
    case HR = 'HR';

}
