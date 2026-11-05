<div
    x-data="{ online: navigator.onLine }"
    x-on:online.window="online = true"
    x-on:offline.window="online = false"
    x-show="! online"
    x-cloak
    class="app-offline-banner"
    role="status"
>
    No internet connection. Changes will not be saved until you are back online.
</div>
