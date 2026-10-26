// NIK counter

function onNik(el) {
    el.value = el.value.replace(/\D/g, '');
    const n   = el.value.length;
    const ctr = document.getElementById('nikCtr');
    ctr.textContent = n + '/16';
    ctr.classList.toggle('ok', n === 16);
}

// File upload zone
function onFile(zoneId, nameId, input) {
    if (!input.files || !input.files[0]) return;
    if (input.files[0].size > 3 * 1024 * 1024) {
        alert('Ukuran file maksimal 3MB.');
        input.value = '';
        return;
    }
    const name = input.files[0].name;
    document.getElementById(nameId).textContent =
        name.length > 20 ? name.slice(0, 17) + '…' : name;
    document.getElementById(zoneId).classList.add('filled');
}

// Turnstile callback
function turnstileSuccess() {
    const btn = document.getElementById('btnSubmit');
    if (!btn) return;
    btn.disabled = false;
    btn.innerHTML = `
        <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor">
            <path d="M9 16.17L4.83 12l-1.41 1.41L9 19 21 7l-1.41-1.41z"/>
        </svg>
        Kirim Pendaftaran`;
}

// Tab persistence + form validation
document.addEventListener('DOMContentLoaded', function () {

    // Restore last active tab
    const activeTab = localStorage.getItem('activeTab');
    if (activeTab) {
        const trigger = document.querySelector(`[data-bs-target="${activeTab}"]`);
        if (trigger) new bootstrap.Tab(trigger).show();
    }

    // Save active tab on change
    document.querySelectorAll('[data-bs-toggle="pill"]').forEach(tab => {
        tab.addEventListener('shown.bs.tab', e => {
            localStorage.setItem('activeTab', e.target.getAttribute('data-bs-target'));
        });
    });

    // Clear tab on success
    if (window.hasSuccessFlash) {
        localStorage.removeItem('activeTab');
    }

    // Form validation
    const form = document.getElementById('form-register');
    if (form) {
        form.addEventListener('submit', function (e) {
            const nik = document.getElementById('nikInput');
            const hp  = document.getElementById('hp');
            let valid = true;

            if (nik && !/^\d{16}$/.test(nik.value.trim())) {
                alert('NIK harus terdiri dari 16 digit.');
                valid = false;
            }
            if (hp && !/^\d+$/.test(hp.value.trim())) {
                alert('Nomor HP hanya boleh angka.');
                valid = false;
            }
            if (!valid) e.preventDefault();
        });

        // Restore NIK counter on reload
        const nik = document.getElementById('nikInput');
        if (nik && nik.value.length > 0) onNik(nik);
    }
});