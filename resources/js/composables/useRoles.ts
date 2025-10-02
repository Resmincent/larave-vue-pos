export function useRole(user: any) {
    if (!user) return 'guest';

    // kalau kamu kirim array of string
    if (Array.isArray(user.roles)) {
        return user.roles[0]?.toLowerCase() || 'admin';
    }

    // kalau kirim role tunggal
    return user.role?.toLowerCase() || 'admin';
}
