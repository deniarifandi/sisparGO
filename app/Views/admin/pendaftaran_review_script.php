<script>
(() => {
  function showCredentials(credentials) {
    const popup = document.createElement('div');
    popup.className = 'modal fade';
    popup.tabIndex = -1;
    popup.setAttribute('aria-labelledby', 'newAccountTitle');
    popup.innerHTML = `<div class="modal-dialog modal-dialog-centered"><div class="modal-content">
      <div class="modal-header"><h5 class="modal-title" id="newAccountTitle">Anggota dan akun berhasil dibuat</h5></div>
      <div class="modal-body">
        <p>Sampaikan ID login dan password sementara berikut kepada pendaftar. Password ini hanya ditampilkan sekali.</p>
        <label class="form-label" for="newAccountId">ID Login</label><input id="newAccountId" class="form-control mb-3" readonly autocomplete="off">
        <label class="form-label" for="newAccountPassword">Password Sementara</label><input id="newAccountPassword" class="form-control mb-3" readonly autocomplete="off">
        <p class="small text-muted">Pendaftar wajib mengganti password saat login pertama.</p>
        <p class="small mb-0" role="status" data-copy-status></p>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-outline-primary" data-copy-account>Salin ID &amp; Password</button><button type="button" class="btn btn-success" data-bs-dismiss="modal">Selesai</button></div>
    </div></div>`;
    const idInput=popup.querySelector('#newAccountId');
    const passwordInput=popup.querySelector('#newAccountPassword');
    idInput.value=credentials.user_id;
    passwordInput.value=credentials.password;
    credentials.password='';
    popup.querySelector('[data-copy-account]').addEventListener('click', async () => {
      try {
        await navigator.clipboard.writeText('ID Login: '+idInput.value+'\nPassword Sementara: '+passwordInput.value);
        popup.querySelector('[data-copy-status]').textContent='ID dan password disalin.';
      } catch (_) {
        popup.querySelector('[data-copy-status]').textContent='Salin manual dari kolom di atas.';
      }
    });
    const clearSecrets=()=>{idInput.value='';passwordInput.value='';};
    window.addEventListener('pagehide',clearSecrets,{once:true});
    popup.addEventListener('hidden.bs.modal',()=>{
      clearSecrets();popup.remove();window.location.reload();
    },{once:true});
    document.body.append(popup);
    const show=()=>new bootstrap.Modal(popup,{backdrop:'static',keyboard:false}).show();
    const detail=document.getElementById('registrationDetailModal');
    if(detail?.classList.contains('show')) {
      detail.addEventListener('hidden.bs.modal',show,{once:true});
      bootstrap.Modal.getInstance(detail).hide();
    } else show();
  }
  document.addEventListener('hide.bs.modal',event=>{
    if(event.target.id==='registrationDetailModal' && event.target.querySelector('[data-review-form][data-submitting="true"]')) event.preventDefault();
  });
  document.addEventListener('click', event => {
    const button = event.target.closest('[data-review-form] button[type="submit"]');
    if (button?.value === 'approve') button.form.elements.catatan_verifikasi.setCustomValidity('');
  });
  document.addEventListener('submit', async event => {
    const form = event.target.closest('[data-review-form]');
    if (!form) return;
    if(form.dataset.submitting) { event.preventDefault();return; }
    const reject = event.submitter?.value === 'reject';
    const correction = event.submitter?.value === 'correction';
    const note = form.elements.catatan_verifikasi;
    if ((reject || correction) && !note.value.trim()) {
      event.preventDefault();note.setCustomValidity('Alasan penolakan atau perbaikan wajib diisi.');note.reportValidity();
      note.addEventListener('input', () => note.setCustomValidity(''), {once:true});return;
    }
    if (!window.confirm(correction ? 'Kembalikan pengajuan agar diperbaiki pendaftar?' : reject ? 'Tolak pengajuan ini secara final? Pendaftar tidak dapat mengirim ulang.' : 'Setujui dan buat akun login untuk pendaftar ini?')) {
      event.preventDefault();return;
    }
    form.dataset.submitting='true';form.setAttribute('aria-busy','true');
    if(reject || correction) return;
    event.preventDefault();
    const payload=new FormData(form);
    const buttons=form.querySelectorAll('button');buttons.forEach(button=>button.disabled=true);
    let message=form.querySelector('[data-approval-message]');
    if(!message) {message=document.createElement('p');message.dataset.approvalMessage='';message.setAttribute('role','status');form.append(message);}
    message.textContent='Membuat anggota dan akun login...';
    const controller=new AbortController();const timeout=setTimeout(()=>controller.abort(),30000);
    try {
      const response=await fetch(form.action,{method:'POST',body:payload,credentials:'same-origin',cache:'no-store',
        headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},signal:controller.signal});
      if(response.redirected) throw new Error('Sesi berakhir. Muat ulang halaman dan login kembali.');
      let data;
      try { data=await response.json(); } catch (_) { throw new Error('Respons belum dapat dibaca. Muat ulang detail dan periksa status pengajuan.'); }
      if(data.csrf) {
        const input=form.querySelector('input[name="'+data.csrf.name+'"]');if(input)input.value=data.csrf.hash;
      }
      if(!response.ok) throw new Error(data.message || 'Persetujuan gagal. Muat ulang detail sebelum mencoba kembali.');
      if(!data.credentials?.user_id || !data.credentials?.password) throw new Error('Kredensial tidak diterima. Hubungi administrator.');
      delete form.dataset.submitting;
      message.textContent='Akun berhasil dibuat.';
      showCredentials(data.credentials);
    } catch(error) {
      delete form.dataset.submitting;
      buttons.forEach(button=>button.disabled=false);
      message.textContent=error.name==='AbortError'
        ? 'Hasil belum terkonfirmasi. Muat ulang detail untuk mengecek status sebelum mencoba lagi.'
        : error.message || 'Koneksi gagal. Muat ulang detail untuk mengecek status.';
    } finally {clearTimeout(timeout);form.removeAttribute('aria-busy');}
  });
})();
</script>
