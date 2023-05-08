<aside class="main-sidebar {{ config('adminlte.classes_sidebar', 'sidebar-dark-primary elevation-4') }}">
    {{-- <style>
        body {
             background-image: url('img/familia-anas-wayuu.png');
             background-repeat: no-repeat;
             background-size: cover;
             transition: background-image 0.5s ease-in-out;
           }
           
           body::before {
             content: "";
             position: absolute;
             top: 0;
             left: 0;
             width: 100%;
             height: 100%;
             background-color: rgba(0, 0, 0, 0.5); /* fondo oscuro con 50% de transparencia */
             z-index: -1; /* hacer que este pseudo-elemento esté detrás del contenido */
           }
           
           .background-image {
             position: fixed;
             top: 0;
             left: 0;
             width: 100%;
             height: 100%;
             background-repeat: no-repeat;
             background-size: cover;
             background-position: center center;
             background-image: url('tu-imagen-de-fondo.png');
           }
           
       
       </style> --}}
    {{-- Sidebar brand logo --}}
    @if(config('adminlte.logo_img_xl'))
        @include('adminlte::partials.common.brand-logo-xl')
    @else
        @include('adminlte::partials.common.brand-logo-xs')
    @endif

    {{-- Sidebar menu --}}
    <div class="sidebar">
        <nav class="pt-2">
            <ul class="nav nav-pills nav-sidebar flex-column {{ config('adminlte.classes_sidebar_nav', '') }}"
                data-widget="treeview" role="menu"
                @if(config('adminlte.sidebar_nav_animation_speed') != 300)
                    data-animation-speed="{{ config('adminlte.sidebar_nav_animation_speed') }}"
                @endif
                @if(!config('adminlte.sidebar_nav_accordion'))
                    data-accordion="false"
                @endif>
                {{-- Configured sidebar links --}}
                @each('adminlte::partials.sidebar.menu-item', $adminlte->menu('sidebar'), 'item')
            </ul>
        </nav>
    </div>

</aside>
