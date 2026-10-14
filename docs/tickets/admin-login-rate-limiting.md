# Limit admin login attempts

Status: Open
Priority: High

## Problem

The POST `/admin/login` route has no explicit login rate limiter. Repeated failed password guesses should be restricted.

Relevant files:
- `backend/routes/web.php`
- `backend/app/Http/Controllers/Admin/AuthController.php`
- `backend/app/Providers/AppServiceProvider.php`

## Requested fix

Add server-side throttling for failed admin login attempts using Laravel's rate limiter. Use a normalized email and IP address key, with an additional IP-wide limit to restrict attempts across multiple accounts. Define the attempt threshold and cooldown during implementation. Clear the account/IP failure counter after successful authentication without clearing the IP-wide limit.

Return a clear cooldown message without revealing whether an email exists. Keep normal login, remember-me behavior, session regeneration, logout, and admin authorization working.

## Acceptance criteria

- Repeated failed attempts reach a defined threshold and further attempts are blocked during cooldown.
- Changing email casing does not bypass the account/IP limit.
- Trying different emails cannot bypass the IP-wide limit.
- Attempts are permitted again after cooldown.
- Successful authentication resets the account/IP failure counter.
- Non-admin users still cannot access the admin panel.
- Automated tests cover thresholds, cooldown, reset, and valid login.

## Scope

Tracking only. No login code was changed when this ticket was created.
