@php( $logout_url = View::getSection('logout_url') ?? config('adminlte.logout_url', 'logout') )
@php( $profile_url = View::getSection('profile_url') ?? config('adminlte.profile_url', 'logout') )

@if (config('adminlte.usermenu_profile_url', false))
    @php( $profile_url = Auth::user()->adminlte_profile_url() )
@endif

@if (config('adminlte.use_route_url', false))
    @php( $profile_url = $profile_url ? route($profile_url) : '' )
    @php( $logout_url = $logout_url ? route($logout_url) : '' )
@else
    @php( $profile_url = $profile_url ? url($profile_url) : '' )
    @php( $logout_url = $logout_url ? url($logout_url) : '' )
@endif
@php( $unread_novedades = \App\Models\Novedad::unreadCountForUser((int) Auth::id()) )
@php( $iframe_mode_enabled = (bool) (Auth::user()->pref_iframe_mode ?? false) )

<li class="nav-item dropdown user-menu">

    {{-- User menu toggler --}}
    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
        @if(config('adminlte.usermenu_image'))
            <img src="{{ Auth::user()->adminlte_image() }}"
                 class="user-image img-circle elevation-2"
                 alt="{{ Auth::user()->name }}">
            @if($unread_novedades > 0)
                <span class="novedades-badge">{{ $unread_novedades > 99 ? '99+' : $unread_novedades }}</span>
            @endif
        @endif
        <span @if(config('adminlte.usermenu_image')) class="d-none d-md-inline" @endif>
            {{ Auth::user()->name }}
        </span>
    </a>

    {{-- User menu dropdown --}}
    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right aw-user-dropdown">

        {{-- User menu header --}}
        @if(!View::hasSection('usermenu_header') && config('adminlte.usermenu_header'))
            <li class="user-header {{ config('adminlte.usermenu_header_class', 'bg-primary') }}
                @if(!config('adminlte.usermenu_image')) h-auto @endif">
                @if(config('adminlte.usermenu_image'))
                    <img src="{{ Auth::user()->adminlte_image() }}"
                         class="img-circle elevation-2"
                         alt="{{ Auth::user()->name }}">
                @endif
                <p class="@if(!config('adminlte.usermenu_image')) mt-0 @endif">
                    {{ Auth::user()->name }}
                    @if(config('adminlte.usermenu_desc'))
                        <small>{{ Auth::user()->adminlte_desc() }}</small>
                    @endif
                </p>
            </li>
        @else
            @yield('usermenu_header')
        @endif

        {{-- Configured user menu links --}}
        @each('adminlte::partials.navbar.dropdown-item', $adminlte->menu("navbar-user"), 'item')

        <li class="dropdown-divider"></li>
        <li>
            <a href="{{ route('novedades.index') }}" class="dropdown-item d-flex justify-content-between align-items-center">
                <span><i class="fas fa-bell mr-2 text-info"></i>Novedades</span>
                @if($unread_novedades > 0)
                    <span class="badge badge-danger">{{ $unread_novedades > 99 ? '99+' : $unread_novedades }}</span>
                @endif
            </a>
        </li>
        <li>
            <form method="POST" action="{{ route('ui.iframe.toggle') }}">
                @csrf
                <input type="hidden" name="enabled" value="{{ $iframe_mode_enabled ? '0' : '1' }}">
                <button type="submit" class="dropdown-item d-flex justify-content-between align-items-center">
                    <span>
                        <i class="fas fa-window-restore mr-2 text-primary"></i>
                        {{ $iframe_mode_enabled ? 'Desactivar modo pestanas' : 'Activar modo pestanas' }}
                    </span>
                    <span class="badge {{ $iframe_mode_enabled ? 'badge-success' : 'badge-secondary' }}">
                        {{ $iframe_mode_enabled ? 'ON' : 'OFF' }}
                    </span>
                </button>
            </form>
        </li>

        {{-- User menu body --}}
        @hasSection('usermenu_body')
            <li class="user-body">
                @yield('usermenu_body')
            </li>
        @endif

        {{-- User menu footer --}}
        <li class="user-footer">
            @if($profile_url)
                <a href="{{ $profile_url }}" class="btn btn-default btn-flat">
                    <i class="fa fa-fw fa-user text-lightblue"></i>
                    Perfil
                </a>
            @endif
            <a class="btn btn-default btn-flat float-right @if(!$profile_url) btn-block @endif"
               href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="fa fa-fw fa-power-off text-red"></i>
                {{ __('adminlte::adminlte.log_out') }}
            </a>
            <form id="logout-form" action="{{ $logout_url }}" method="POST" style="display: none;">
                @if(config('adminlte.logout_method'))
                    {{ method_field(config('adminlte.logout_method')) }}
                @endif
                {{ csrf_field() }}
            </form>
        </li>

    </ul>

</li>

@once
    <style>
            .navbar-nav > .user-menu > .aw-user-dropdown{
                display:block;
                opacity:0;
                visibility:hidden;
                pointer-events:none;
                transform:translateY(-10px) scale(.94);
                transform-origin:top right;
                filter:blur(7px);
                border:1px solid rgba(79, 142, 219, .28);
                box-shadow:0 20px 44px rgba(31, 75, 116, .18), 0 0 0 1px rgba(255,255,255,.55);
                transition:
                    opacity .22s ease,
                    visibility .22s ease,
                    transform .34s cubic-bezier(.16,.84,.28,1),
                    filter .28s ease;
                overflow:hidden;
                will-change:opacity, transform, filter;
            }

            .navbar-nav > .user-menu.show > .aw-user-dropdown{
                opacity:1;
                visibility:visible;
                pointer-events:auto;
                transform:translateY(0) scale(1);
                filter:blur(0);
                animation:awDropdownGlow .46s ease both;
            }

            .navbar-nav > .user-menu > .aw-user-dropdown::before{
                content:"";
                position:absolute;
                inset:0;
                z-index:0;
                background:
                    radial-gradient(circle at 28% 0%, rgba(105,212,255,.2), transparent 38%),
                    linear-gradient(120deg, rgba(255,255,255,.18), transparent 34%);
                opacity:0;
                transform:translateY(-8px);
                transition:opacity .28s ease, transform .28s ease;
                pointer-events:none;
            }

            .navbar-nav > .user-menu.show > .aw-user-dropdown::before{
                opacity:1;
                transform:translateY(0);
            }

            .navbar-nav > .user-menu > .aw-user-dropdown > li,
            .navbar-nav > .user-menu > .aw-user-dropdown > .dropdown-divider{
                position:relative;
                z-index:1;
                opacity:0;
                transform:translateY(8px);
            }

            .navbar-nav > .user-menu.show > .aw-user-dropdown > li,
            .navbar-nav > .user-menu.show > .aw-user-dropdown > .dropdown-divider{
                animation:awDropdownItemIn .34s cubic-bezier(.16,.84,.28,1) forwards;
            }

            .navbar-nav > .user-menu.show > .aw-user-dropdown > li:nth-child(1){animation-delay:.04s}
            .navbar-nav > .user-menu.show > .aw-user-dropdown > li:nth-child(2){animation-delay:.08s}
            .navbar-nav > .user-menu.show > .aw-user-dropdown > li:nth-child(3){animation-delay:.12s}
            .navbar-nav > .user-menu.show > .aw-user-dropdown > li:nth-child(4){animation-delay:.16s}
            .navbar-nav > .user-menu.show > .aw-user-dropdown > li:nth-child(5){animation-delay:.20s}
            .navbar-nav > .user-menu.show > .aw-user-dropdown > li:nth-child(6){animation-delay:.24s}

            .navbar-nav > .user-menu > .aw-user-dropdown .dropdown-item,
            .navbar-nav > .user-menu > .aw-user-dropdown .user-footer .btn{
                transition:transform .18s ease, background .18s ease, box-shadow .18s ease;
            }

            .navbar-nav > .user-menu > .aw-user-dropdown .dropdown-item:hover,
            .navbar-nav > .user-menu > .aw-user-dropdown .user-footer .btn:hover{
                transform:translateX(2px);
                box-shadow:0 8px 20px rgba(63, 139, 226, .12);
            }

            @keyframes awDropdownGlow{
                0%{box-shadow:0 12px 26px rgba(31,75,116,.1), 0 0 0 rgba(105,212,255,0)}
                54%{box-shadow:0 22px 50px rgba(31,75,116,.2), 0 0 30px rgba(105,212,255,.2)}
                100%{box-shadow:0 20px 44px rgba(31,75,116,.18), 0 0 0 1px rgba(255,255,255,.55)}
            }

            @keyframes awDropdownItemIn{
                to{
                    opacity:1;
                    transform:translateY(0);
                }
            }

            @media (prefers-reduced-motion: reduce){
                .navbar-nav > .user-menu > .aw-user-dropdown,
                .navbar-nav > .user-menu > .aw-user-dropdown::before,
                .navbar-nav > .user-menu > .aw-user-dropdown > li,
                .navbar-nav > .user-menu > .aw-user-dropdown > .dropdown-divider{
                    transition:none!important;
                    animation:none!important;
                    filter:none!important;
                }

                .navbar-nav > .user-menu.show > .aw-user-dropdown,
                .navbar-nav > .user-menu.show > .aw-user-dropdown > li,
                .navbar-nav > .user-menu.show > .aw-user-dropdown > .dropdown-divider{
                    opacity:1;
                    transform:none;
                }
            }
    </style>
@endonce
