<!-- resources/views/layouts/main.blade.php -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <!-- Fonts and Icons -->
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700,200" rel="stylesheet" />
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" rel="stylesheet">
    <!-- CSS Files -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/paper-dashboard.css?v=2.0.1') }}" rel="stylesheet" />
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
        }
        .btn {
            border-radius: 3px;
            padding: 6px 16px;
            font-size: 13px;
        }
        .btn-sm {
            padding: 2px 8px;
            font-size: 12px;
        }
        .highlight {
            background-color: yellow;
            color: black;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <div class="sidebar" data-color="black" data-active-color="danger">
            <div class="logo">
                <a href="#" class="simple-text logo-mini">
                    <div class="logo-image-small">
                        <img src="{{ asset('assets/img/logo_iCare.png') }}">
                    </div>
                </a>
                <a href="#" class="simple-text logo-normal">
                    iCare Service
                </a>
            </div>
            <div class="sidebar-wrapper">
                <ul class="nav">
                    <li class="nav-item @yield('dashboard_active')">
                        <a class="nav-link" href="{{ route('dashboard') }}">
                            <p>Halaman</p>
                        </a>
                    </li>
                    <li class="nav-item @yield('barang_active')">
                        <a class="nav-link" href="{{ route('barang.index') }}">
                            <p>Barang</p>
                        </a>
                    </li>
                    @if(auth()->user()->role == 'admin')
                    <li class="nav-item @yield('kategori_active')">
                        <a class="nav-link" href="{{ route('kategoris.index') }}">
                            <p>Kategori</p>
                        </a>
                    </li>
                    @endif


                    <li class="nav-item @yield('transaksi_active')">
                        <a class="nav-link" data-toggle="collapse" href="#transaksiMenu" aria-expanded="false">
                            <p>Transaksi</p>
                        </a>
                        <div class="collapse" id="transaksiMenu">
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('transaksis.index') }}">Data Transaksi</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('transaksis.create') }}">Tambah Transaksi</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    


                    <li class="nav-item @yield('laporan_active')">
                        <a class="nav-link" href="{{ route('laporans.index') }}">
                            <p>Laporan</p>
                        </a>
                    </li>
                    <li class="nav-item @yield('user_active')">
                        @if(auth()->user()->role == 'admin')
                        <a class="nav-link" href="{{ route('users.index') }}">
                            <p>Akun</p>
                        </a>
                        @else
                        <a class="nav-link" href="{{ route('users.show', auth()->user()->id_user) }}">
                            <p>Akun</p>
                        </a>
                        @endif
                    </li>
                </ul>
            </div>
        </div>
        <!-- End Sidebar -->

        <div class="main-panel">
            <!-- Navbar -->
            <nav class="navbar navbar-expand-lg navbar-absolute fixed-top navbar-transparent">
                <div class="container-fluid">
                    <div class="navbar-wrapper">
                        <a class="navbar-brand" href="#">@yield('page-title')</a>
                    </div>
                    <div class="collapse navbar-collapse justify-content-end" id="navigation">
                        <form id="search-form" action="#" method="GET">
                            <div class="input-group no-border">
                                <input type="text" class="form-control" id="search-input" placeholder="Search..." />
                                <div class="input-group-append">
                                    <div class="input-group-text">
                                        <i class="nc-icon nc-zoom-split"></i>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <form id="logout-form" action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="button" onclick="confirmLogout()" class="btn btn-danger btn-sm">Logout</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
            <!-- End Navbar -->

            <!-- Content -->
            <div class="content">
                @yield('content')
            </div>
            <!-- End Content -->

            <footer class="footer footer-black footer-white">
                <div class="container-fluid">
                    <div class="row"></div>
                </div>
            </footer>
        </div>
    </div>

    <script src="{{ asset('assets/js/core/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/js/core/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/perfect-scrollbar.jquery.min.js') }}"></script>
    <script src="{{ asset('assets/js/paper-dashboard.min.js?v=2.0.1') }}" type="text/javascript"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const searchForm = document.getElementById("search-form");
            const searchInput = document.getElementById("search-input");

            searchForm.addEventListener("submit", function(event) {
                event.preventDefault();
                const searchValue = searchInput.value.trim();

                if (!searchValue) {
                    alert("Masukkan kata kunci untuk mencari.");
                    return;
                }

                document.querySelectorAll(".highlight").forEach(el => {
                    el.classList.remove("highlight");
                    el.style.backgroundColor = "";
                    el.style.color = "";
                });

                const regex = new RegExp(searchValue, "gi");
                document.querySelectorAll("td").forEach(cell => {
                    const text = cell.textContent || cell.innerText;
                    if (regex.test(text)) {
                        cell.innerHTML = text.replace(regex, match => `<span class="highlight">${match}</span>`);
                    }
                });
            });
        });

        function confirmLogout() {
            var confirmAction = confirm("Apakah Anda yakin ingin logout?");
            if (confirmAction) {
                document.getElementById('logout-form').submit();
            }
        }
    </script>
</body>
</html>
