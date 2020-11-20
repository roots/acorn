<?php

/**
 * Filter handle without arguments.
 * Only for test purpose.
 *
 * @return string
 */
function filter_handle(): string
{
    return 'cool';
}

/**
 * Filter handle with arguments.
 * Only for test purpose.
 *
 * @param string $username
 * @param string $motto
 * @param string $role
 * @param int    $level
 *
 * @return string
 */
function filter_handle_with_4_args(
    string $username = 'plumthedev',
    string $motto = 'Work hard',
    string $role = 'Developer',
    int $level = 1
): string
{
    return "[$username]:[$role]:[$level] - $motto";
}
