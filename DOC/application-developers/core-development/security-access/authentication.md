# Authentication

eQual separates three concerns:

* an **authentication method** validates a proof such as a password, a TOTP code, or a passkey assertion;
* an **access token** carries the authenticated user and the assurance obtained from successful methods;
* a controller's **access announcement** states whether a user must be authenticated and, optionally, the minimum authentication level required.

Application code normally declares the required access level and lets the framework interpret the token. It should not inspect JWT claims or assign an authentication level itself.

For the token format, method replacement rules, factor model, and extension checklist, see [Authentication internals](../../../community/internal-architecture/authentication.md).

## Protecting an App Controller

Controllers are protected by default when no visibility is announced. Make the requirement explicit when the controller handles authenticated data:

```php
'access' => [
    'visibility' => 'protected'
]
```

Add `level` when the operation requires stronger, recent authentication:

```php
'access' => [
    'visibility' => 'protected',
    'level'      => 2
]
```

The framework resolves the user, evaluates the security policy and access restrictions, then compares the required level with the effective level calculated by `AuthenticationManager`.

If the level is insufficient, the controller is not executed and eQual raises `insufficient_auth_level`. The client can then start an additional authentication and retry the original operation. `auth_level` is still accepted as a deprecated alias of `level`.

!!! warning "Do not read `amr` in an App controller"
    The presence of a method in a token does not mean that it is still valid or that it grants a particular level. Authentication entries can expire independently. Use the controller access contract; framework-level code can use `AuthenticationManager::getAuthLevel()`.

## Authentication Levels

The level is the confidence granted to the current session by eQual policy. It is contextual and is not an intrinsic, universal property of a method or authenticator.

| Level | Intended use | Current built-in examples |
| :---- | :----------- | :------------------------ |
| `0` | No currently valid authentication entry. A renewed session token can still identify a user at this level. | Expired authentication entries. |
| `1` | Basic authentication. | Password, email nonce, federated login, tracked access token, HTTP Basic authentication. |
| `2` | Enhanced authentication or step-up. | TOTP and passkey. |
| `3` | Reserved for policies requiring stronger verified guarantees. | No built-in authentication controller currently grants level 3. |

Two level-1 methods do not automatically produce level 2. The effective level is the highest level granted by a non-expired authentication event. The authentication controller that has verified the proof assigns that event's level according to framework policy.

## Built-in Methods

The following method references are currently issued by core controllers:

| Reference | Method | Granted level | Notes |
| :-------- | :----- | :------------ | :---- |
| `pwd` | Password | `1` | Can open a session. When TOTP is required, successful password verification produces a short-lived MFA challenge instead of an access token. |
| `email` | Signed email nonce | `1` | Can open a session or refresh the same method on an existing session. |
| `fed` | Legacy Facebook or Google OAuth integration | `1` | This is a direct legacy integration, not yet a generic provider model. |
| `token` | Stored access token | `1` | Tracked server-side and revocable. Intended for explicit API access-token workflows. |
| `passkey` | WebAuthn passkey | `2` | Can open a passwordless session or strengthen an existing session. See [Passkeys](passkeys.md). |
| `otp` | TOTP code | `2` | Used after the password MFA challenge, during enrollment validation, or to strengthen an existing session. |

HTTP Basic authentication is also recognized when no usable JWT is found. It authenticates the request at level 1 but does not create an access token.

Names such as SMS OTP, recovery codes, generic OIDC/SAML providers, and hardware-specific level-3 methods are extension targets; they must not be presented to users as built-in capabilities until their controllers and policies exist.

## Sign-In and Step-Up

The built-in sign-in UI follows this general sequence:

1. `core_signin-info` receives a login and returns the methods and factor creations available for that user under the current settings.
2. The selected method validates its own proof.
3. On success, the backend creates an access token or adds the method to the existing token for the same user.
4. The token is returned as the `access_token` `HttpOnly` cookie.
5. Protected controllers resolve the user and, when `access.level` is present, enforce the effective level.

`core_signin-info` exposes `allowed_methods`, `allowed_creations`, method-specific data, and whether active passkey or TOTP factors exist. Detailed factor records are returned only when the requested account is the current resolved user.

During step-up, the additional method updates the authentication state without extending the JWT lifetime. The client must keep the new `access_token` cookie and retry the operation that required the higher level.

## TOTP Configuration

TOTP support is disabled by default and uses user-overridable settings in the `core.security` section.

| Setting | Purpose |
| :------ | :------ |
| `auth.totp.enabled` | Enables TOTP authentication. Enable this before requiring it or allowing enrollment. |
| `auth.password.totp_required` | Requires a TOTP after successful password verification. |
| `auth.totp.creation` | Allows eligible users to enroll a TOTP key. |
| `auth.totp.algorithm` | Hash algorithm used by new keys: `SHA1`, `SHA256`, or `SHA512`. |
| `auth.totp.digits` | Code length: 6 or 8 digits. |
| `auth.totp.period` | Generation period in seconds, commonly 30. |
| `auth.totp.allowed_failed_attempts` | Maximum failed validations before the key is blocked from further attempts; the controller defaults to 5. |

Enabling `auth.password.totp_required` without an enrollment or recovery path can lock affected users out. A TOTP key starts as `pending`, becomes usable only after validation, and is then represented as an active authentication factor.

## Access Token Transport and Lifetime

Browser sessions use a signed JWT in an `HttpOnly` `access_token` cookie. API clients can send the same JWT in `Authorization: Bearer <token>` when no cookie is present.

`AUTH_ACCESS_TOKEN_VALIDITY` controls the usual access-token validity. Calling `core_userinfo` renews the JWT lifetime, but does not renew the expiration of the authentication events already recorded in it. Consequently, a session may remain authenticated while its effective level falls from 2 to 1, or eventually to 0, until the user authenticates again.

!!! tip "CLI authentication"
    In the [Command-Line Interface](../../how-tos-references/api-cli.md), eQual resolves the user as `root` and bypasses HTTP authentication and controller access checks. Do not use CLI behavior to validate an HTTP authentication flow.
