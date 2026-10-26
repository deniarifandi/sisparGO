<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#124b8b">
  <title>Masuk | SISPARGO Kota Bogor</title>
  <style>
    :root { color-scheme: light; --green: #1559a6; --ink: #243b53; --muted: #718399; }
    * { box-sizing: border-box; }
    body { margin: 0; min-height: 100vh; min-height: 100svh; background: #f3f6fb; color: var(--ink); font-family: 'Segoe UI', Arial, sans-serif; }
    button, input { font: inherit; }
    .page { min-height: 100vh; min-height: 100svh; display: grid; grid-template-columns: 1.08fr 1fr; }
    .identity { background: #124b8b; color: #fff; padding: 52px clamp(36px, 5vw, 90px); display: flex; flex-direction: column; justify-content: space-between; position: relative; overflow: hidden; }
    .identity::before, .identity::after { content: ''; position: absolute; border: 1px solid #ffffff12; border-radius: 50%; width: 600px; height: 600px; right: -340px; bottom: -300px; pointer-events: none; }
    .identity::after { width: 780px; height: 780px; right: -430px; bottom: -390px; }
    .brand { display: flex; align-items: center; gap: 13px; position: relative; z-index: 1; }
    .brand-mark { width: 44px; height: 44px; border: 1px solid #ffffff42; border-radius: 13px; display: grid; place-items: center; color: #dbeafe; font-size: 27px; font-weight: 700; }
    .brand-name { font-size: 22px; font-weight: 750; letter-spacing: 1.4px; }
    .brand-city { margin-top: 3px; color: #b7d0ee; font-size: 12px; letter-spacing: .6px; }
    .intro { position: relative; z-index: 1; padding: 65px 0; max-width: 490px; }
    .eyebrow { color: #b8d8ff; font-size: 11px; font-weight: 650; letter-spacing: 2.5px; text-transform: uppercase; }
    h1 { font-size: clamp(34px, 3.6vw, 54px); line-height: 1.18; letter-spacing: -1.8px; font-weight: 600; margin: 22px 0; }
    h1 span { color: #dbeafe; }
    .intro p { color: #c5d9f2; font-size: 15px; line-height: 1.85; max-width: 380px; }
    .city-line { display: flex; align-items: center; gap: 12px; margin-top: 32px; font-size: 12px; color: #d5e5f7; }
    .city-line::before { content: ''; width: 30px; height: 2px; background: #b8d8ff; }
    .identity-footer { color: #adc8e8; font-size: 11px; line-height: 1.7; position: relative; z-index: 1; }
    .login-panel { display: flex; align-items: center; justify-content: center; padding: 64px 40px; }
    .login-content { width: 100%; max-width: 390px; }
    .section-label { font-size: 11px; text-transform: uppercase; letter-spacing: 2px; color: #6581a0; font-weight: 650; }
    h2 { font-size: 30px; letter-spacing: -.9px; margin: 14px 0 10px; font-weight: 650; }
    .description { color: var(--muted); font-size: 14px; line-height: 1.7; margin: 0 0 32px; }
    .field { margin-bottom: 22px; }
    .field label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 9px; }
    .field input { width: 100%; border: 1px solid #d8e2ef; border-radius: 9px; background: #fff; padding: 14px 15px; color: var(--ink); outline: none; transition: border-color .2s, box-shadow .2s; }
    .field input::placeholder { color: #94a3b5; font-size: 13px; }
    .field input:focus { border-color: #2875c7; box-shadow: 0 0 0 3px #2875c718; }
    .password-wrap { position: relative; }
    .password-wrap input { padding-right: 68px; }
    .password-toggle { position: absolute; right: 7px; top: 7px; bottom: 7px; border: 0; border-radius: 5px; padding: 0 9px; background: transparent; color: #416f9f; cursor: pointer; font-size: 11px; font-weight: 600; }
    button:focus-visible, input:focus-visible { outline: 3px solid #2875c7; outline-offset: 3px; }
    .remember { display: flex; gap: 9px; align-items: center; font-size: 12px; color: #607994; margin: 2px 0 26px; cursor: pointer; }
    .remember input { width: 16px; height: 16px; accent-color: var(--green); margin: 0; }
    .submit { width: 100%; border: 0; border-radius: 9px; padding: 15px 20px; background: var(--green); color: #fff; font-weight: 600; font-size: 14px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; transition: background .2s; }
    .submit:hover { background: #104981; }
    .submit span { font-size: 19px; line-height: 1; }
    .notice { border-top: 1px solid #dde6f1; padding-top: 22px; margin-top: 30px; font-size: 11px; line-height: 1.8; color: #7c8da2; text-align: center; }
    .alert { background: #fff0ee; border: 1px solid #efcbc5; color: #9c342a; padding: 13px 15px; border-radius: 8px; font-size: 13px; line-height: 1.6; margin-bottom: 24px; overflow-wrap: anywhere; }
    @media (min-width: 1500px) { .identity { padding-left: max(80px, calc((100vw - 1300px) / 2)); } }
    @media (max-width: 760px) { .page { grid-template-columns: 1fr; } .identity { padding: 26px 28px 30px; } .intro { padding: 34px 0 0; max-width: 100%; } h1 { font-size: 32px; letter-spacing: -1px; margin: 15px 0; } .intro p, .city-line, .identity-footer { display: none; } .login-panel { padding: 38px 28px 32px; } .login-content { max-width: 440px; } .brand-name { font-size: 20px; } }
  </style>
</head>
<body>
  <main class="page">
    <section class="identity" aria-label="SISPARGO Kota Bogor">
      <div class="brand">
        <div class="brand-mark" aria-hidden="true">P</div>
        <div><div class="brand-name">SISPARGO</div><div class="brand-city">KOTA BOGOR</div></div>
      </div>
      <div class="intro">
        <div class="eyebrow">Pengelolaan parkir Kota Bogor</div>
        <h1>Kelola parkir.<br>Wujudkan kota<br>yang <span>lebih tertib.</span></h1>
        <p>Satu akses untuk mendukung pengelolaan dan pelayanan perparkiran di Kota Bogor.</p>
        <div class="city-line">Terhubung untuk pelayanan yang lebih baik.</div>
      </div>
      <div class="identity-footer">SISPARGO &middot; Kota Bogor<br>Sistem informasi pengelolaan perparkiran</div>
    </section>
    <section class="login-panel" aria-labelledby="login-title">
      <div class="login-content">
        <div class="section-label">Portal pengelolaan</div>
        <h2 id="login-title">Selamat datang kembali.</h2>
        <p class="description">Masuk dengan akun Anda untuk mengakses dashboard SISPARGO.</p>
        <?php if ($error = session()->getFlashdata('error')): ?>
          <div class="alert" role="alert"><?= esc($error) ?></div>
        <?php endif; ?>
        <form action="<?= base_url('auth/login') ?>" method="post">
          <?= csrf_field() ?>
          <div class="field">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?= esc(old('username'), 'attr') ?>" placeholder="Masukkan username Anda" autocomplete="username" autocapitalize="none" spellcheck="false" required>
          </div>
          <div class="field">
            <label for="password">Password</label>
            <div class="password-wrap">
              <input type="password" id="password" name="password" placeholder="Masukkan password Anda" autocomplete="current-password" required>
              <button class="password-toggle" type="button" id="toggle-password" aria-controls="password" aria-label="Tampilkan password" aria-pressed="false" hidden>Lihat</button>
            </div>
          </div>
          <label class="remember" for="remember"><input type="checkbox" name="remember" id="remember" value="1" checked> Tetap masuk di perangkat ini</label>
          <button class="submit" type="submit">Masuk ke dashboard <span aria-hidden="true">&rarr;</span></button>
        </form>
        <div class="notice">Gunakan akun yang telah terdaftar.<br>Hubungi administrator jika Anda mengalami kendala akses.</div>
      </div>
    </section>
  </main>
  <script>
    const toggle = document.getElementById('toggle-password');
    const password = document.getElementById('password');
    toggle.hidden = false;
    toggle.addEventListener('click', function () {
      const visible = password.type === 'password';
      password.type = visible ? 'text' : 'password';
      this.textContent = visible ? 'Tutup' : 'Lihat';
      this.setAttribute('aria-label', visible ? 'Sembunyikan password' : 'Tampilkan password');
      this.setAttribute('aria-pressed', String(visible));
    });
  </script>
</body>
</html>
