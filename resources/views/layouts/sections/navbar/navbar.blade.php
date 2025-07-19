@php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
$containerNav = $containerNav ?? 'container-fluid';
$navbarDetached = ($navbarDetached ?? '');
@endphp

<!-- Navbar -->
@if(isset($navbarDetached) && $navbarDetached == 'navbar-detached')
<nav class="layout-navbar {{$containerNav}} navbar navbar-expand-xl {{$navbarDetached}} align-items-center bg-navbar-theme" id="layout-navbar">
@endif
@if(isset($navbarDetached) && $navbarDetached == '')
<nav class="layout-navbar navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
  <div class="{{$containerNav}}">
    @endif

      <!--  Brand demo (display only for navbar-full and hide on below xl) -->
      @if(isset($navbarFull))
      <div class="navbar-brand app-brand demo d-none d-xl-flex py-0 me-4">
        <a href="{{url('/')}}" class="app-brand-link gap-2">
          <span class="app-brand-logo demo">@include('_partials.macros',["width"=>25,"withbg"=>'var(--bs-primary)'])</span>
          <span class="app-brand-text demo menu-text fw-bold text-heading">{{config('variables.templateName')}}</span>
        </a>
      </div>
      @endif

      <!-- ! Not required for layout-without-menu -->
      @if(!isset($navbarHideToggle))
      <div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0{{ isset($menuHorizontal) ? ' d-xl-none ' : '' }} {{ isset($contentNavbar) ?' d-xl-none ' : '' }}">
        <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
          <i class="bx bx-menu bx-md"></i>
        </a>
      </div>
      @endif

      <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
        <div class="navbar-nav align-items-center">
          <h4 class="mb-0">@yield('title')</h4>
        </div>
        <ul class="navbar-nav flex-row align-items-center ms-auto">
          <!-- User -->
          <li class="nav-item navbar-dropdown dropdown-user dropdown" style="border: 2px solid #6063ea;border-radius: 100%;">
            <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
              <div class="avatar rounded-circle" style="display: flex;flex-direction: row;justify-content: center;align-items: center;">
                 @include('_partials.macros',["width"=>25,"withbg"=>'var(--bs-primary)'])
              </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li>
                <a class="dropdown-item" href="javascript:void(0);">
                  <div class="d-flex">
                    <div class="flex-shrink-0 me-3">
                      <div class="avatar rounded-circle" style="display: flex;flex-direction: row;justify-content: center;align-items: center;">
                        @include('_partials.macros',["width"=>25,"withbg"=>'var(--bs-primary)'])
                      </div>
                    </div>
                    <div class="flex-grow-1">
                      <h6 class="mb-0">{{session('data')->name}}</h6>
                      <small class="text-muted">{{session('data')->level == 1 ? 'Super Admin' : 'Admin'}}</small>
                    </div>
                  </div>
                </a>
              </li>
              <li>
                <a class="dropdown-item" href="/auth/logout">
                  <i class="bx bx-power-off bx-md me-3"></i><span>Log Out</span>
                </a>
              </li>
            </ul>
          </li>
          <!--/ User -->
        </ul>
      </div>

      @if(!isset($navbarDetached))
    </div>
    @endif
  </nav>
  <!-- / Navbar -->
