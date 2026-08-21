# Remote Interface

Rideshare instances can call selected router methods server-to-server. These calls do not use WordPress nonces. They require a linked entry in the `Remote_Instance_Model` table with status `allowed` and a shared secret.

## Headers

Every remote request must send:

- `X-Rideshare-Instance`: UUID of the calling instance.
- `X-Rideshare-Timestamp`: Unix timestamp or parseable date string.
- `X-Rideshare-Request-Id`: unique request id.
- `X-Rideshare-Signature`: HMAC-SHA256 signature.

Requests are accepted only when the timestamp is within five minutes and the request id has not been used before.

## Signature

The signature message is:

```text
HTTP_METHOD
target
method
timestamp
request_id
sha256(body)
```

Example for `remote_items` with an empty body:

```sh
timestamp="$(date +%s)"
request_id="collector-${timestamp}"
body_hash="e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855"
message="$(printf 'GET\nriding\nremote_items\n%s\n%s\n%s' "$timestamp" "$request_id" "$body_hash")"
signature="$(printf '%s' "$message" | openssl dgst -sha256 -hmac "$shared_secret" -r | awk '{print $1}')"
```

## Endpoints

The transport is the existing AJAX router:

```text
/wp-admin/admin-ajax.php?action=rideshare-router&target=riding&_method=remote_ping
/wp-admin/admin-ajax.php?action=rideshare-router&target=riding&_method=remote_items
```

`remote_items` returns only publishable ride data and stop labels/address details. It does not expose local WordPress user data.
