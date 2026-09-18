<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="oneqay-merchant-entry" content="{{ in_array(strtolower(trim((string) config('oneqay.runtime_class', ''))), ['local', 'test', 'ci'], true) && (bool) config('database.oneqay_persistence_enabled', false) && (bool) config('oneqay.session_control.enabled', false) ? 'enabled' : 'disabled' }}">
    <title>oneQay</title>
    @vite('resources/js/app.ts')
    @inertiaHead
</head>
<body>
    @inertia
</body>
</html>
