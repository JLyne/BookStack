<?php

return [
    // Dump user details after a login request for debugging purposes
    'dump_user_details' => env('REMOTE_AUTH_DUMP_USER_DETAILS', false),

    // Attribute, within a SAML response, to find the user's email address
    'email_header' => env('REMOTE_AUTH_EMAIL_HEADER', 'X-Auth-Request-Email'),
    // Attribute, within a SAML response, to find the user's display name
    'display_name_headers' => explode('|', env('REMOTE_AUTH_DISPLAY_NAME_HEADERS', 'X-Auth-Request-Name')),
    // Attribute, within a SAML response, to use to connect a BookStack user to the SAML user.
    'external_id_header' => env('REMOTE_AUTH_EXTERNAL_ID_HEADER', 'X-Auth-Request-Id'),

    // Group sync options
    // Enable syncing, upon login, of REMOTE_AUTH groups to BookStack groups
    'user_to_groups' => env('REMOTE_AUTH_USER_TO_GROUPS', false),
    // Attribute, within a SAML response, to find group names on
    'group_header' => env('REMOTE_AUTH_GROUPS_HEADER', 'X-Auth-Request-Groups'),
    // When syncing groups, remove any groups that no longer match. Otherwise sync only adds new groups.
    'remove_from_groups' => env('REMOTE_AUTH_REMOVE_FROM_GROUPS', false),
];
