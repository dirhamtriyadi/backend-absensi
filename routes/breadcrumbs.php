<?php // routes/breadcrumbs.php

// Note: Laravel will automatically resolve `Breadcrumbs::` without
// this import. This is nice for IDE syntax and refactoring.
use Diglactic\Breadcrumbs\Breadcrumbs;

// This import is also not required, and you could replace `BreadcrumbTrail $trail`
//  with `$trail`. This is nice for IDE type checking and completion.
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

// Dashboard
Breadcrumbs::for('dashboard', function (BreadcrumbTrail $trail) {
    $trail->push('Dashboard', route('dashboard'));
});

// Dashboard > Master Data > Work Schedules
Breadcrumbs::for('work-schedules', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Master Data');
    $trail->push('Work Schedules', route('work-schedules.index'));
});

// Dashboard > Master Data > Work Schedules > [Work Schedule] > Edit
Breadcrumbs::for('work-schedules.edit', function (BreadcrumbTrail $trail, $workSchedule) {
    $trail->parent('work-schedules');
    $trail->push('Edit', route('work-schedules.edit', $workSchedule));
});

// Dashboard > Master Data > Work Schedules > Create
Breadcrumbs::for('work-schedules.create', function (BreadcrumbTrail $trail) {
    $trail->parent('work-schedules');
    $trail->push('Create', route('work-schedules.create'));
});

// Dashboard > Master Data > Annual Holidays
Breadcrumbs::for('annual-holidays', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Master Data');
    $trail->push('Annual Holidays', route('annual-holidays.index'));
});

// Dashboard > Master Data > Annual Holidays > [Annual Holiday] > Edit
Breadcrumbs::for('annual-holidays.edit', function (BreadcrumbTrail $trail, $annualHoliday) {
    $trail->parent('annual-holidays');
    $trail->push('Edit', route('annual-holidays.edit', $annualHoliday));
});

// Dashboard > Master Data > Annual Holidays > Create
Breadcrumbs::for('annual-holidays.create', function (BreadcrumbTrail $trail) {
    $trail->parent('annual-holidays');
    $trail->push('Create', route('annual-holidays.create'));
});

// Dashboard > Roles
Breadcrumbs::for('roles', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Roles', route('roles.index'));
});

// Dashboard > Roles > [Role] > Edit
Breadcrumbs::for('roles.edit', function (BreadcrumbTrail $trail, $role) {
    $trail->parent('roles');
    $trail->push('Edit', route('roles.edit', $role));
});

// Dashboard > Roles > Create
Breadcrumbs::for('roles.create', function (BreadcrumbTrail $trail) {
    $trail->parent('roles');
    $trail->push('Create', route('roles.create'));
});

// Dashboard > Users
Breadcrumbs::for('users', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Users', route('users.index'));
});

// Dashboard > Users > [User]
// Breadcrumbs::for('users.show', function (BreadcrumbTrail $trail, $user) {
//     $trail->parent('users');
//     $trail->push($user->name, route('users.show', $user));
// });

// Dashboard > Users > [User] > Edit
Breadcrumbs::for('users.edit', function (BreadcrumbTrail $trail, $user) {
    $trail->parent('users', $user);
    $trail->push('Edit', route('users.edit', $user));
});

// Dashboard > Users > Create
Breadcrumbs::for('users.create', function (BreadcrumbTrail $trail) {
    $trail->parent('users');
    $trail->push('Create', route('users.create'));
});