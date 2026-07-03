# Authenticating requests

To authenticate requests, include an **`Authorization`** header with the value **`"Bearer {JWT_TOKEN}"`**.

All authenticated endpoints are marked with a `requires authentication` badge in the documentation below.

Obtain a token from <code>POST /api/v1/auth/login</code>. Reporter (public) endpoints use X-Case-Code / X-Case-Pin headers instead.
