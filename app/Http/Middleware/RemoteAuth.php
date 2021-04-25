<?php

    namespace BookStack\Http\Middleware;

    use BookStack\Activity\ActivityType;
    use BookStack\Access\GroupSyncService;
    use BookStack\Access\RegistrationService;
    use BookStack\Exceptions\JsonDebugException;
    use BookStack\Exceptions\RemoteAuthException;
    use BookStack\Exceptions\UserRegistrationException;
    use BookStack\Facades\Activity;
    use BookStack\Facades\Theme;
    use BookStack\Theming\ThemeEvents;
    use Closure;
    use BookStack\Users\Models\User;
    use Illuminate\Support\Str;

class RemoteAuth extends GroupSyncService
{

    protected $registrationService;
    protected $user;
    protected $config;


    public function __construct(RegistrationService $registrationService, User $user)
    {
        $this->config = config('remote_auth');
        $this->registrationService = $registrationService;
        $this->user = $user;
    }

    /**
     * Check if SSO variable is set and auto login if they are.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     *
     * @return mixed
     * @throws JsonDebugException
     * @throws RemoteAuthException
     * @throws UserRegistrationException
     */
    public function handle($request, Closure $next)
    {
        /*
             * REMOTE_AUTH_EMAIL_HEADER
             * REMOTE_AUTH_NAME_HEADERS
             * REMOTE_AUTH_EXTERNAL_ID_HEADER
             * REMOTE_AUTH_DUMP_USER_DETAILS
             * REMOTE_AUTH_USER_TO_GROUPS
             * REMOTE_AUTH_GROUP_HEADER
             * REMOTE_AUTH_REMOVE_FROM_GROUPS
         */
        $remoteAuthEnabled = env('REMOTE_AUTH_ENABLED', false);

        if (!$remoteAuthEnabled) {
            return $next($request);
        }

        $userDetails = $this->getUserDetails($request);
        $isLoggedIn = auth()->check();

        if ($this->config['dump_user_details']) {
            throw new JsonDebugException([
                'id_from_idp' => $userDetails['external_id'] ?? null,
                'attrs_after_parsing' => $userDetails,
            ]);
        }

        if (!$userDetails['external_id']) {
            return $next($request);
        }

        $user = $this->getOrRegisterUser($userDetails);

        if ($user === null) {
            throw new RemoteAuthException(trans('errors.saml_user_not_registered', ['name' => $userDetails['external_id']]), '/login');
        }

        if ($userDetails['email'] === null) {
            throw new RemoteAuthException(trans('errors.saml_no_email_address'));
        }

        if ($isLoggedIn) {
            throw new RemoteAuthException(trans('errors.saml_already_logged_in'), '/login');
        }

        $syncGroups = $this->config['user_to_groups'];

        if ($syncGroups) {
            $groups = $userDetails['groups'];
            $this->syncUserWithFoundGroups($user, $groups, env('REMOTE_AUTH_REMOVE_FROM_GROUPS', false));
        }

        auth()->setUser($user);
        Activity::add(ActivityType::AUTH_LOGIN, "remote auth; {$user->logDescriptor()}");
        Theme::dispatch(ThemeEvents::AUTH_LOGIN, 'remote auth', $user);

        return $next($request);
    }

    /**
     * Extract the details of a user from a SAML response.
     */
    protected function getUserDetails($request): array
    {
        $idHeader = $this->config['external_id_header'];
        $emailHeader = $this->config['email_header'];
        $groupHeader = $this->config['group_header'];

        $externalId = $request->header($idHeader);
        $email = $request->header($emailHeader);
        $groups = explode(',', $request->header($groupHeader) ?? '');

        $email = filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;

        return [
            'external_id' => $externalId,
            'name'        => $this->getUserDisplayName($request, $externalId),
            'email'       => $email,
            'groups'      => $groups,
        ];
    }

    /**
     * Calculate the display name
     */
    protected function getUserDisplayName($request, $defaultValue): ?string
    {
        $displayNameHeaders = $this->config['display_name_headers'];

        $displayName = [];

        foreach ($displayNameHeaders as $header) {
            $value = $request->header($header);

            if ($value !== null) {
                $displayName[] = $value;
            }
        }

        if (count($displayName) == 0) {
            $displayName = $defaultValue;
        } else {
            $displayName = implode(' ', $displayName);
        }

        return $displayName;
    }

    /**
     * @throws UserRegistrationException
     */
    protected function getOrRegisterUser(array $userDetails): ?User
    {
        $user = $this->user->newQuery()
          ->where('external_auth_id', '=', $userDetails['external_id'])
          ->first();

        if (is_null($user)) {
            $userData = [
                'name' => $userDetails['name'],
                'email' => $userDetails['email'],
                'password' => Str::random(32),
                'external_auth_id' => $userDetails['external_id'],
            ];

            $user = $this->registrationService->registerUser($userData, null, false);
        }

        return $user;
    }
}
