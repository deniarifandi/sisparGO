<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Portal Perparkiran Kota Malang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root{
            --primary:#0f4c81;
            --warning:#decb0f;
            --accent:#ffc107;
            --bg:#f6f8fb;
            --text:#17202a;
        }
        *{font-family:Inter,sans-serif}body{background:var(--bg);color:var(--text)}
        .navbar{backdrop-filter:blur(10px);background:rgba(255,255,255,.85)!important}
        .hero{padding:120px 0;background:
            radial-gradient(circle at top right,#d9ecff 0,#f6f8fb 45%,#f6f8fb 100%)}
            .badge-soft{background:#e7f1ff;color:var(--primary);padding:.6rem 1rem;border-radius:99px;font-weight:600}
            .display-3{font-weight:800;line-height:1.05}
            .lead{max-width:620px;color:#5d6773}
            .btn-primary{background:var(--primary);border:none;padding:.9rem 1.6rem;border-radius:14px}
            .btn-warning{background:var(--warning);border:none;padding:.9rem 1.6rem;border-radius:14px}
            .btn-light{padding:.9rem 1.6rem;border-radius:14px}
            .visual{height:420px;border-radius:30px;background:linear-gradient(135deg,#0f4c81,#3d86c8);position:relative;overflow:hidden}
            .circle{position:absolute;border-radius:50%;background:rgba(255,255,255,.12)}
            .c1{width:260px;height:260px;top:-40px;right:-50px}.c2{width:180px;height:180px;left:40px;bottom:35px}
            .mock{position:absolute;inset:50px;background:#fff;border-radius:24px;box-shadow:0 30px 60px rgba(0,0,0,.15);padding:28px}
            .menu-card{background:#fff;border-radius:24px;padding:34px;height:100%;transition:.25s;border:1px solid #eef2f7}
            .menu-card:hover{transform:translateY(-8px);box-shadow:0 20px 50px rgba(0,0,0,.08)}
            .icon{width:64px;height:64px;border-radius:18px;background:#eef5ff;color:var(--primary);display:flex;align-items:center;justify-content:center;font-size:28px}
            .arrow{font-size:1.4rem;color:var(--primary);text-decoration:none}
            .stat{padding:28px;text-align:center}.stat h2{font-weight:800}
            .cta{background:linear-gradient(135deg,#0f4c81,#1b6cb7);color:#fff;border-radius:30px;padding:60px}
            footer{padding:50px 0;color:#6c757d}
        </style>

        <style>
            .feature-title{
                font-size:.8rem;
                font-weight:700;
                color:#6c757d;
                text-transform:uppercase;
                letter-spacing:.05em;
                margin-bottom:.5rem;
            }

            .feature-item{
                display:flex;
                justify-content:space-between;
                align-items:center;
                gap:12px;
                padding:.7rem 0;
                border-bottom:1px solid #eef2f7;
            }

            .feature-item:last-child{
                border-bottom:none;
            }

            .feature-item span{
                display:flex;
                align-items:center;
                min-width:0;
            }

            .feature-item i:last-child{
                font-size:1.1rem;
                flex-shrink:0;
            }

            @media (max-width:991px){

                .hero{
                    padding:70px 0;
                }

                .display-3{
                    font-size:2.5rem;
                }

                .visual{
                    height:auto;
                    min-height:unset;
                }

                .mock{
                    position:relative;
                    inset:auto;
                    margin:20px;
                    padding:20px;
                }

                .circle{
                    display:none;
                }
            }

            @media (max-width:576px){

                .display-3{
                    font-size:2rem;
                }

                .mock{
                    margin:16px;
                    padding:18px;
                }

                .feature-item{
                    font-size:.9rem;
                }

                .feature-title{
                    font-size:.75rem;
                }
            }
        </style>
    </head>

    <body>
        <nav class="navbar navbar-expand-lg sticky-top">
            <div class="container py-2">
                <a class="navbar-brand fw-bold" href="#">SISPARMA</a>

            </div></nav>

            <section class="hero">
                <div class="container">
                    <div class="row align-items-center g-5">
                        <div class="col-lg-6">
                            <div class="badge-soft mb-4 text-center">Portal Informasi Perparkiran</div>
                            <h1 class="display-3 mb-4">SISPARMA</h1>
                            <p class="lead mb-4">Digitalisasi layanan perparkiran serta keterbukaan informasi publik dalam satu portal.</p>
                            <div class="d-flex gap-3 flex-wrap">
                                <a class="btn btn-primary shadow-sm" href="">Informasi Publik</a>
                                <a class="btn btn-warning" href="register/form">Pendaftaran Titik Parkir</a>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="visual">
                                <div class="circle c1"></div>
                                <div class="circle c2"></div>

                                <div class="mock">
                                    <h5 class="fw-bold mb-3">Layanan Digital Parkir Kota Malang</h5>

                                    <div class="feature-group">
                                        <div class="feature-title">Informasi Publik</div>

                                        <div class="feature-item" data-status="coming">
                                            <span><i class="bi bi-geo-alt me-2"></i>Lokasi & Zona Parkir</span>
                                        </div>

                                        <div class="feature-item" data-status="coming">
                                            <span><i class="bi bi-cash-coin me-2"></i>Informasi Tarif Parkir</span>
                                        </div>

                                        <div class="feature-item" data-status="coming">
                                            <span><i class="bi bi-exclamation-triangle me-2"></i>Layanan Pengaduan</span>
                                        </div>

                                        <div class="feature-item" data-status="coming">
                                            <span><i class="bi bi-graph-up me-2"></i>Statistik & Dashboard</span>
                                        </div>
                                    </div>

                                    <!-- <div class="feature-group mt-4">
                                        <div class="feature-title">Juru Parkir</div>

                                        <div class="feature-item" data-status="ready">
                                            <span><i class="bi bi-person-vcard me-2"></i>Pengelolaan Juru Parkir</span>
                                        </div>

                                        <div class="feature-item" data-status="coming">
                                            <span><i class="bi bi-person-plus me-2"></i>Registrasi & Perpanjangan</span>
                                        </div>

                                        <div class="feature-item" data-status="coming">
                                            <span><i class="bi bi-credit-card me-2"></i>Pembayaran Retribusi</span>
                                        </div>
                                    </div> -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>



            <section class="container py-5"><div class="row bg-white rounded-4 shadow-sm">
                <div class="col-md-3 stat"><h2>1.250</h2><div>Juru Parkir</div></div>
                <div class="col-md-3 stat"><h2>98</h2><div>Titik Parkir</div></div>
                <div class="col-md-3 stat"><h2>Rp18 M</h2><div>Pendapatan</div></div>
                <div class="col-md-3 stat"><h2>96%</h2><div>Laporan Ditindaklanjuti</div></div>
            </div></section>

            <section class="container py-5">
                <div class="cta text-center">
                    <h2 class="fw-bold mb-3">Temukan Aktivitas Ilegal?</h2>
                    <p class="mb-4">Laporkan juru parkir liar, pungutan tidak sesuai ketentuan, atau pelanggaran lainnya melalui kanal resmi.</p>
                    <a href="#" class="btn btn-warning btn-lg rounded-pill px-5">Buat Laporan</a>
                </div>
            </section>

            <footer><div class="container d-flex justify-content-between flex-wrap">
                <div><strong>Dinas Perhubungan Kota Malang</strong><br>Portal Perparkiran Digital</div>
                <div>PPID · Regulasi · Kontak</div>
            </div></footer>
            <script>
                document.querySelectorAll(".feature-item").forEach(item => {

                    const status = item.dataset.status;

                    let icon = "bi-clock-history";
                    let color = "text-warning";

                    if(status === "ready"){
                        icon = "bi-check-circle-fill";
                        color = "text-success";
                    }

                    if(status === "beta"){
                        icon = "bi-flask";
                        color = "text-primary";
                    }

                    if(status === "disabled"){
                        icon = "bi-lock-fill";
                        color = "text-secondary";
                    }

                    const statusIcon = document.createElement("i");
                    statusIcon.className = `bi ${icon} ${color}`;

                    item.appendChild(statusIcon);
                });
            </script>
        </body>
        </html>