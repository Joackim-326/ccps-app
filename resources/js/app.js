// Mobile sidebar toggle
document.addEventListener('click', (e) => {
    const t = e.target.closest('[data-toggle]');
    if (t) document.getElementById(t.dataset.toggle)?.classList.toggle('hidden');
});

// Keep the unread-message badge fresh on every page
const badge = document.querySelectorAll('[data-unread-badge]');
if (badge.length) {
    const refresh = async () => {
        try {
            const r = await fetch(badge[0].dataset.url, { headers: { Accept: 'application/json' } });
            const { count } = await r.json();
            badge.forEach((b) => { b.textContent = count; b.classList.toggle('hidden', count === 0); });
        } catch (_) {}
    };
    setInterval(refresh, 8000);
}
