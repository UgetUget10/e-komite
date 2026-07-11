<?php

return [
    'backup' => [
        'name' => 'komite-backup',

        'source' => [
            'databases' => [
                'mysql' => [
                    'dump_command_path' => '',
                    'dump_command_timeout' => 60,
                    'restore_command_path' => '',
                ],
            ],

            'files' => [
                'include' => [
                    base_path(),
                ],
                'exclude' => [
                    base_path('node_modules'),
                    base_path('vendor'),
                    base_path('storage/logs'),
                    base_path('.git'),
                ],
                'follow_links' => false,
                'relative_path' => base_path(),
            ],
        ],

        'destination' => [
            'disks' => [
                'backups',
            ],
        ],

        'temporary_directory' => storage_path('backups/temp'),
    ],

    'notifications' => [
        'notifications' => [],
    ],

    'cleanup' => [
        'strategy' => \Spatie\Backup\Tasks\Cleanup\Strategies\DefaultStrategy::class,

        'keep_all_backups_for_days' => 7,

        'keep_daily_backups_for_days' => 16,

        'keep_weekly_backups_for_weeks' => 8,

        'keep_monthly_backups_for_months' => 4,

        'keep_yearly_backups_for_years' => 2,

        'delete_oldest_backups_instead_of_newest' => false,
    ],
];
