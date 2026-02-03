# Authenticating requests

To authenticate requests, include an **`Authorization`** header with the value **`"Bearer YOUR_ACCESS_TOKEN"`**.

All authenticated endpoints are marked with a `requires authentication` badge in the documentation below.

This API uses <b>Laravel Sanctum</b> for authentication. After logging in via <code>/api/login</code>, you will receive an access token. Include this token in the <code>Authorization</code> header as <code>Bearer YOUR_ACCESS_TOKEN</code> for all authenticated requests.
