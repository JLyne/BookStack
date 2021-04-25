<?php

namespace BookStack\Access\Guards;

/**
 * Remote Auth Session Guard
 *
 * Remote auth logins are handled externally, meaning it does not fit very well
 * into the default laravel 'Guard' auth flow. This class provides a safer, thin
 * version of SessionGuard.
 */
class RemoteAuthSessionGuard extends ExternalBaseSessionGuard
{
    /**
     * Validate a user's credentials.
     *
     * @param array $credentials
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        return false;
    }

    /**
     * Attempt to authenticate a user using the given credentials.
     *
     * @param array $credentials
     * @param bool $remember
     * @return bool
     */
    public function attempt(array $credentials = [], $remember = false)
    {
        return false;
    }
}
