// Author by Lab | zefry. Capture before Inertia snapshots browser history; never persist the capability.
let invitationToken = '';
if (typeof window !== 'undefined' && window.location.pathname === '/activate-business') {
    invitationToken = new URLSearchParams(window.location.hash.slice(1)).get('token') ?? '';
    if (window.location.hash) window.history.replaceState(window.history.state, '', '/activate-business');
}
export function consumeInvitationToken(): string {
    const value = invitationToken;
    invitationToken = '';
    return value;
}
