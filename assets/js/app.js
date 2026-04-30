const root = document.documentElement;
const savedTheme = localStorage.getItem('designhub-theme') || 'light';
root.dataset.theme = savedTheme;

document.getElementById('themeToggle')?.addEventListener('click', () => {
    const nextTheme = root.dataset.theme === 'dark' ? 'light' : 'dark';
    root.dataset.theme = nextTheme;
    localStorage.setItem('designhub-theme', nextTheme);
});

document.querySelectorAll('[data-confirm]').forEach((button) => {
    button.addEventListener('click', (event) => {
        if (!confirm(button.dataset.confirm || 'هل أنت متأكد؟')) {
            event.preventDefault();
        }
    });
});

// Preview selected preview image before upload
document.querySelector('input[name="preview_image"]')?.addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = () => {
        let img = document.querySelector('.preview-preview');
        if (!img) {
            img = document.createElement('img');
            img.className = 'mb-3 preview-preview';
            img.style.maxWidth = '200px';
            e.target.closest('form').insertBefore(img, e.target.closest('.mb-3'));
        }
        img.src = reader.result;
    };
    reader.readAsDataURL(file);
});

// Show selected file name and size for design_file
document.querySelector('input[name="design_file"]')?.addEventListener('change', (e) => {
    const file = e.target.files[0];
    const infoSel = 'small.file-info';
    let info = e.target.parentNode.querySelector(infoSel);
    if (!info) {
        info = document.createElement('small');
        info.className = 'file-info d-block text-muted';
        e.target.parentNode.appendChild(info);
    }
    if (!file) {
        info.textContent = '';
        return;
    }
    const size = (file.size / 1024 / 1024).toFixed(2) + ' MB';
    info.textContent = file.name + ' • ' + size;
});

// Lazy-load images with IntersectionObserver and fade-in
document.querySelectorAll('img[data-lazy="true"]').forEach(img => {
    img.classList.add('skeleton', 'lazy-loaded');
});
if ('IntersectionObserver' in window) {
    const io = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            const img = entry.target;
            const src = img.dataset.src;
            if (!src) return io.unobserve(img);
            img.src = src;
            img.addEventListener('load', () => {
                img.classList.remove('skeleton');
                img.classList.add('loaded');
            });
            io.unobserve(img);
        });
    }, { rootMargin: '200px' });
    document.querySelectorAll('img[data-lazy="true"]').forEach(i => io.observe(i));
} else {
    // Fallback: load immediately
    document.querySelectorAll('img[data-lazy="true"]').forEach(img => {
        img.src = img.dataset.src;
        img.classList.remove('skeleton');
    });
}
