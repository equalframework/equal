# Passkey Authentication

Passkeys provide passwordless authentication through the Web Authentication (WebAuthn) standard. The authenticator keeps the private key; eQual stores the credential identifier, public key, signature counter, and attestation format in a user-owned `Passkey` authentication factor.

A passkey can open a new session or strengthen an existing session for the same user. The current core controller records the `passkey` method at authentication level 2.

## Configuration

### Prerequisites

* WebAuthn requires a secure HTTPS context. Browsers allow `http://localhost` as a development exception.
* `core.security.auth.passkey.enabled` must be enabled to offer passkey authentication.
* `core.security.auth.passkey.creation` separately controls whether users may be offered passkey enrollment.

Settings can be overridden per user. The current setting codes are:

| Setting | Purpose |
| :------ | :------ |
| `auth.passkey.enabled` | Offer authentication with registered passkeys. |
| `auth.passkey.creation` | Offer enrollment to eligible users. |
| `auth.passkey.rp_id` | WebAuthn Relying Party domain, for example `app.example.com`. |
| `auth.passkey.rp_name` | Human-readable Relying Party name displayed by the authenticator. |
| `auth.passkey.user_verification` | `required`, `preferred`, or `discouraged`. |
| `auth.passkey.cross_platform` | Allow all authenticators, only cross-platform authenticators, or only platform authenticators. |
| `auth.passkey.format.<format>` | Allow an attestation format such as `android-key`, `android-safetynet`, `apple`, `fido-u2f`, `none`, `packed`, or `tpm`. |
| `auth.passkey.authenticator.<transport>` | Allow `usb`, `nfc`, `ble`, `hybrid`, or `internal` authenticator transports. |

The older flat setting names such as `passkey_creation` and `passkey_rp_id` are deprecated. Use the `auth.passkey.*` names in new code and configuration.

## Sign-In Discovery

`core_signin-info` returns the passkey method only when it is enabled for the identified user. It also supplies the anonymous `user_handle` used by the WebAuthn option controllers and indicates whether the user already has an active passkey factor.

Detailed factor records are disclosed only when the account being queried is the current resolved user.

## Registration Workflow

Registration requires an authenticated session:

1. Obtain the user's `user_handle` from `core_signin-info`.
2. Call `core_user_passkey-register-options` with that handle.
3. Keep the returned `register_token` and convert the encoded binary option values to `ArrayBuffer`.
4. Call `navigator.credentials.create(options)`.
5. Send the result to `core_user_passkey-register` with:
    * `register_token`;
    * `transports`;
    * `client_data_json` as Base64;
    * `attestation_object` as Base64.

The backend verifies the challenge and attestation, then creates an active `core\security\factor\Passkey` linked to the user. Several passkeys can be associated with one user and distinguished by their labels and credential identifiers.

<center><img src="/_assets/uml/passkey_register.png" /></center>

## Authentication Workflow

Authentication can start without an existing session:

1. Obtain the anonymous `user_handle` from `core_signin-info`.
2. Call `core_user_passkey-auth-options` with that handle.
3. Keep the returned `auth_token` and convert the encoded binary option values to `ArrayBuffer`.
4. Call `navigator.credentials.get(options)`.
5. POST the assertion to `/auth/passkey`, which routes to `core_user_auth_passkey`, with:
    * `auth_token`;
    * `credential_id` from `rawId`, as Base64;
    * `client_data_json` as Base64;
    * `authenticator_data` as Base64;
    * `signature` as Base64;
    * `user_handle` as Base64 when returned by the authenticator.

The backend verifies the challenge, origin/Relying Party data, credential status, public-key signature, and signature counter. It then creates a level-2 access token or adds the passkey authentication to the existing token. An existing token for another user is rejected with `authenticated_user_mismatch`.

`core_user_passkey-auth` is a deprecated compatibility controller. New integrations should use `core_user_auth_passkey` or the `/auth/passkey` route.

<center><img src="/_assets/uml/passkey_authentication.png" /></center>

## Browser Integration Notes

Two conversion helpers are usually required:

* a recursive converter from eQual's `=?BINARY?B?...?=` wrappers to `ArrayBuffer` before calling WebAuthn;
* an `ArrayBuffer`-to-Base64 converter for the JSON request sent back to eQual.

Keep `register_token` and `auth_token` unchanged between the options request and the final action. These are signed challenge tokens, not access tokens. The current passkey challenge payloads do not contain an explicit expiration; core integrations should treat them as single-flow values and avoid persisting them.

## Assurance Policy

The current passkey controller always grants level 2 after successful WebAuthn verification. Although attestation formats and authenticator properties may support a future distinction between levels 2 and 3, that mapping is not implemented today. App code must therefore not infer a level from `fmt`, transport, biometrics, or the `passkey` method name.

Use [`access.level`](authentication.md#protecting-an-app-controller) to state the required assurance and let `AuthenticationManager` evaluate the session. Core contributors can find the factor and token contracts in [Authentication internals](../../../community/internal-architecture/authentication.md).
