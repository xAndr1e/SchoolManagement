document.addEventListener('DOMContentLoaded', () => {
    // ── Image preview ──────────────────────────────────────────────────────────
    const imageInput = document.getElementById('ann-image');
    let previewEl    = document.getElementById('ann-image-preview');

    if (imageInput) {
        imageInput.addEventListener('change', () => {
            const file = imageInput.files[0];

            if (!previewEl) {
                previewEl    = document.createElement('img');
                previewEl.id = 'ann-image-preview';
                previewEl.classList.add('ann-image-preview');
                imageInput.parentElement.appendChild(previewEl);
            }

            if (file) {
                previewEl.src           = URL.createObjectURL(file);
                previewEl.style.display = 'block';
            } else {
                previewEl.src           = '';
                previewEl.style.display = 'none';
            }
        });
    }

    // ── Clear preview on reset ─────────────────────────────────────────────────
    const form = document.getElementById('announcement-form');
    if (form) {
        form.addEventListener('reset', () => {
            if (previewEl) {
                previewEl.src           = '';
                previewEl.style.display = 'none';
            }
        });
    }

    // ── Refresh list after successful submission ───────────────────────────────
    window.addEventListener('form:success', async () => {
        const listEl = document.querySelector('.announcements-list');
        if (!listEl) return;

        try {
            const response = await fetch(window.location.href, { cache: 'no-store' });
            if (!response.ok) return;

            const html    = await response.text();
            const parser  = new DOMParser();
            const doc     = parser.parseFromString(html, 'text/html');
            const newList = doc.querySelector('.announcements-list');

            if (newList) {
                listEl.innerHTML = newList.innerHTML;
            }
        } catch {
            // Silent fail
        }
    });
});