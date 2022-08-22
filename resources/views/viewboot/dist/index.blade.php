<?php
use Illuminate\Support\Facades\Auth;
$name=Auth::user()->name;
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>@yield('title')</title>
		
		
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
        <link href="{{ URL::asset('/') }}css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>
		
	  <!-- per upload -->
	  <link href="{{ URL::asset('/') }}css/upload/jquery.dm-uploader.min.css" rel="stylesheet">
	  <!-- per upload -->  
	  <link href="{{ URL::asset('/') }}css/upload/styles.css?ver=1.1" rel="stylesheet">
  		
		
    </head>
    <body class="sb-nav-fixed">
        <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
            <!-- Navbar Brand-->
            <a class="navbar-brand ps-3" href="index.html">FilleaOffice</a>
            <!-- Sidebar Toggle-->
            <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>
            <!-- Navbar Search-->
			
            <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">
                <div class="input-group">
                   <!--
				   <input class="form-control" type="text" placeholder="Search for..." aria-label="Search for..." aria-describedby="btnNavbarSearch" />
                    <button class="btn btn-primary" id="btnNavbarSearch" type="button"><i class="fas fa-search"></i></button>
					!-->
                </div>
            </form>
            <!-- Navbar-->
            <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-user fa-fw"></i></a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
						<!--
                        <li><a class="dropdown-item" href="#!">Settings</a></li>
                        <li><a class="dropdown-item" href="#!">Activity Log</a></li>
                        <li><hr class="dropdown-divider" /></li>
						!-->
						
						<form method="POST" action="{{ route('logout') }}">
                            @csrf
							<li><a class="dropdown-item" href="#" onclick="event.preventDefault();                            this.closest('form').submit();">Logout</a></li>
						</form>
					
						
                    </ul>
                </li>
            </ul>
        </nav>
        <div id="layoutSidenav">
            <div id="layoutSidenav_nav">
                <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                    <div class="sb-sidenav-menu">
                        <div class="nav">
                            <div class="sb-sidenav-menu-heading">
								{{ $name }}
							</div>
                            <a class="nav-link" href="{{ route('dashboard') }}">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Home
                            </a>
                            
							
							<?php if (1==1) {?>
								<div class="sb-sidenav-menu-heading">OPZIONI</div>
								<a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseLayouts" aria-expanded="false" aria-controls="collapseLayouts">
									<div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
									Schemi
									<div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
								</a>
								<div class="collapse" id="collapseLayouts" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
									<nav class="sb-sidenav-menu-nested nav">
										<a class="nav-link" href="{{ route('modelli') }}">Modelli importazione</a>
										<!--
										<a class="nav-link" href="layout-sidenav-light.html">Light Sidenav</a>
										!-->
									</nav>
								</div>
							<?php } ?>
							
                        </div>
                    </div>
                    <div class="sb-sidenav-footer">
                        <div class="small">Loggato in:</div>
                        FilleaOffice-Admin
                    </div>
                </nav>
            </div>
            <div id="layoutSidenav_content">
                <main>
					<div class="container-fluid px-4">
						<h1 class="mt-4">
							
						</h1>
						<ol class="breadcrumb mb-4">
							<li class="breadcrumb-item active">Pubblicazione tabulati</li>
						</ol>

						<div class="row">


						@yield('content_table_option')
						@yield('content_riattiva')
						
						@yield('content_table_report')
						@yield('content_table_step2')
						@yield('content_table_step3')
						@yield('content_table_step4')
						
					</div>

                </main>
			
                <footer class="py-4 bg-light mt-auto">
                    <div class="container-fluid px-4">
                        <div class="d-flex align-items-center justify-content-between small">
                            <div class="text-muted">Copyright &copy; FilleaOffice</div>

                        </div>
                    </div>
                </footer>
            </div>
        </div>
		<script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>		
		<script src="{{ URL::asset('/') }}js/home.js?ver=2.67"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="{{ URL::asset('/') }}js/scripts.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>
        <script src="{{ URL::asset('/') }}js/datatables-simple-demo.js?ver=3.6"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" integrity="sha512-894YE6QWD5I59HgZOGReFYm4dnWc1Qt5NtvYSaNcOP+u1T9qYdvdihz0PPSiiqn/+/3e7Jo4EaG7TubfWGUrMQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

		<!-- per upload -->
		<script src="{{ URL::asset('/') }}js/upload/jquery.dm-uploader.min.js"></script>
		<script src="{{ URL::asset('/') }}js/upload/demo-ui.js?ver=1.1"></script>
		<script src="{{ URL::asset('/') }}js/upload/demo-config.js?ver=2.20"></script>
		<!-- fine upload -->		
    </body>
	
	
</html>
