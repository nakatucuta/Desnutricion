<?php

return [
    
   
    /*
    |--------------------------------------------------------------------------
    | Title
    |--------------------------------------------------------------------------
    |
    | Here you can change the default title of your admin panel.
    |
    | For detailed instructions you can look the title section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'title' => 'Anas Wayuu',
    'title_prefix' => '',
    'title_postfix' => '',
    
    /*
    |--------------------------------------------------------------------------
    | Favicon
    |--------------------------------------------------------------------------
    |
    | Here you can activate the favicon.
    |
    | For detailed instructions you can look the favicon section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_ico_only' => true,
    'use_full_favicon' => false,

    /*
    |--------------------------------------------------------------------------
    | Google Fonts
    |--------------------------------------------------------------------------
    |
    | Here you can allow or not the use of external google fonts. Disabling the
    | google fonts may be useful if your admin panel internet access is
    | restricted somehow.
    |
    | For detailed instructions you can look the google fonts section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'google_fonts' => [
        'allowed' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Logo
    |--------------------------------------------------------------------------
    |
    | Here you can change the logo of your admin panel.
    |
    | For detailed instructions you can look the logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

'logo' => '<span class="brand-text font-weight-bold">Anas <b>Wayuu</b></span>',

'logo_img' => 'vendor/adminlte/dist/img/logo.png',

'logo_img_class' => 'brand-image custom-logo', // Cambia a una clase personalizada

    'logo_img_xl' => null,
    'logo_img_xl_class' => 'brand-image-xs',
    'logo_img_alt' => 'Anas Wayuu',

    /*
    |--------------------------------------------------------------------------
    | Authentication Logo
    |--------------------------------------------------------------------------
    |
    | Here you can setup an alternative logo to use on your login and register
    | screens. When disabled, the admin panel logo will be used instead.
    |
    | For detailed instructions you can look the auth logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'auth_logo' => [
        'enabled' => true,
        'img' => [
            'path' => 'vendor/adminlte/dist/img/logo.png',
            'alt' => 'Auth Logo',
            'class' => 'img-circle',
            'width' => 130,
            'height' => 130,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Preloader Animation
    |--------------------------------------------------------------------------
    |
    | Here you can change the preloader animation configuration.
    |
    | For detailed instructions you can look the preloader section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'preloader' => [
        'enabled' => true,
        'img' => [
            'path' => 'vendor/adminlte/dist/img/logo.png',
            'alt' => 'AdminLTE Preloader Image',
            'effect' => 'animation__shake',
            'width' => 250,
            'height' => 250,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Menu
    |--------------------------------------------------------------------------
    |
    | Here you can activate and change the user menu.
    |
    | For detailed instructions you can look the user menu section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'usermenu_enabled' => true,
    'usermenu_header' => true,
    'usermenu_header_class' => 'bg-primary',
    'usermenu_image' => false,
    'usermenu_desc' => false,
    'usermenu_profile_url' => false,   
    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    |
    | Here we change the layout of your admin panel.
    |
    | For detailed instructions you can look the layout section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'layout_topnav' => null,
    'layout_boxed' => null,
    'layout_fixed_sidebar' => true,
    'layout_fixed_navbar' => true,
    'layout_fixed_footer' => true, 
    'layout_dark_mode' => null,

    /*
    |--------------------------------------------------------------------------
    | Authentication Views Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the authentication views.
    |
    | For detailed instructions you can look the auth classes section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_auth_card' => '',
    'classes_auth_header' => 'bg-gradient-green',
    'classes_auth_body' => '',
    'classes_auth_footer' => 'text-center',
    'classes_auth_icon' => 'fa-lg text-success ',
    'classes_auth_btn' => 'btn-flat btn-success ',



    // 'classes_auth_card' => 'card-outline card-primary',
    // 'classes_auth_header' => '',
    // 'classes_auth_body' => '',
    // 'classes_auth_footer' => '',
    // 'classes_auth_icon' => '',
    // 'classes_auth_btn' => 'btn-flat btn-primary',

//     'classes_auth_card' => 'bg-gradient-dark',
// 'classes_auth_header' => '',
// 'classes_auth_body' => 'bg-gradient-dark',
// 'classes_auth_footer' => 'text-center',
// 'classes_auth_icon' => 'fa-fw text-light',
// 'classes_auth_btn' => 'btn-flat btn-light',

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the admin panel.
    |
    | For detailed instructions you can look the admin panel classes here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_body' => '',   //agrega un cale a todo el contenido en general
    'classes_brand' => '',  //bg-success  le da u estailo a la pparte superior izquierda
    'classes_brand_text' => '', //text-primary  le cambia el color al textu superior izquierdo
    'classes_content_wrapper' => '', //es todo el contenindo dentro de la plantilla ojo
    'classes_content_header' => '',  //estilos al header de la pgina 
    'classes_content' => '',
    'classes_sidebar' => 'sidebar-dark-success elevation-4',
    'classes_sidebar_nav' => '',
    'classes_topnav' => 'navbar-dark-info ', //AQUI CAMBIAS  DE COLOR LAS LESTRAS AZULES DEL LOGIN navbar-white navbar-light
    'classes_topnav_nav' => 'navbar-expand',
    'classes_topnav_container' => 'container',

    /*
    |--------------------------------------------------------------------------
    | Sidebar
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar of the admin panel.
    |
    | For detailed instructions you can look the sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'sidebar_mini' => 'lg',
    'sidebar_collapse' => false,
    'sidebar_collapse_auto_size' => false,
    'sidebar_collapse_remember' => false,
    'sidebar_collapse_remember_no_transition' => true,
    'sidebar_scrollbar_theme' => 'os-theme-light',
    'sidebar_scrollbar_auto_hide' => 'l',
    'sidebar_nav_accordion' => true,
    'sidebar_nav_animation_speed' => 450,

    /*
    |--------------------------------------------------------------------------
    | Control Sidebar (Right Sidebar)
    |--------------------------------------------------------------------------
    |
    | Here we can modify the right sidebar aka control sidebar of the admin panel.
    |
    | For detailed instructions you can look the right sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'right_sidebar' => false,
    'right_sidebar_icon' => 'fas fa-cogs',
    'right_sidebar_theme' => 'dark',
    'right_sidebar_slide' => true,
    'right_sidebar_push' => true,
    'right_sidebar_scrollbar_theme' => 'os-theme-light',
    'right_sidebar_scrollbar_auto_hide' => 'l',

    /*
    |--------------------------------------------------------------------------
    | URLs
    |--------------------------------------------------------------------------
    |
    | Here we can modify the url settings of the admin panel.
    |
    | For detailed instructions you can look the urls section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_route_url' => false,
    'dashboard_url' => 'home',
    'logout_url' => 'logout',
    'login_url' => 'login',
    'register_url' => 'register',
    'password_reset_url' => 'password/reset',
    'password_email_url' => 'password/email',
    'profile_url' => false,

    /*
    |--------------------------------------------------------------------------
    | Laravel Mix
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Laravel Mix option for the admin panel.
    |
    | For detailed instructions you can look the laravel mix section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'enabled_laravel_mix' => false,
    'laravel_mix_css_path' => 'css/app.css',
    'laravel_mix_js_path' => 'js/app.js',

    /*
    |--------------------------------------------------------------------------
    | Menu Items
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar/top navigation of the admin panel.
    |
    | For detailed instructions you can look here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

    'menu' => [
        // Navbar items:
        // [
        //     'type'         => 'navbar-search',
        //     'text'         => 'search',
        //     'topnav_right' => true,
        // ],
        [
            'type'         => 'fullscreen-widget',
            'topnav_right' => true,
        ],

        // Sidebar items:
        [
            'type' => 'sidebar-menu-search',
            'text' => 'search',
        ],
        [
            'text' => 'blog',
            'url'  => 'admin/blog',
            'can'  => 'manage-blog',
        ],


       
        [
            'text'    => 'Evento 113',
            'icon'    => 'fas fa-fw fa-share',
            'submenu' => [
                [
                    'text'        => 'Sivigila',
                    'url'         => 'sivigila',
                    'icon'        => 'fas fa-fw fa-home',
                    'label'       => '',  
                    'label_color' => 'success',
                    // 'icon_color'  => 'red'
                ],
                [
                    'text'        => 'Control',
                    'url'         => 'revision',
                    'icon'        => 'fas fa-fw fa-eye',
                   'label'       => 'REVISAR', 
                   'label_color' => 'danger ',
                   //  'icon_color'  => 'red'
                ],
                [
                    'text'        => 'Seguimiento',
                    'url'         => 'Seguimiento',
                     'icon'        => 'fas fa-fw fa-user',
                     'label'       => 'ZONA PARA IPS',
                     'label_color' => 'warning',
                     'icon_color'  => 'yellow',
                    //  'icon_color'  => 'green'
                ],
            ],
        ],



        [
            'text'    => 'Evento 412',
            'icon'    => 'fas fa-clinic-medical',
            'submenu' => [
                [
                    'text'        => 'Cargue',
                    'url'         => 'import-excel',
                    'icon'        => 'fas fa-upload',
                    'label'       => '',  
                    'label_color' => 'success',
                    // 'icon_color'  => 'red'
                ],
                 [
                    'text'        => 'Seguimiento',
                     'url'         => 'new412_seguimiento',
                    'icon'        => 'fas fa-fw fa-eye',
                   'label'       => 'REVISAR', 
                    'label_color' => 'danger ',
                   //  'icon_color'  => 'red'
                 ],
                // [
                //     'text'        => 'Seguimiento',
                //     'url'         => 'Seguimiento',
                //      'icon'        => 'fas fa-fw fa-user',
                //      'label'       => 'ZONA PARA IPS',
                //      'label_color' => 'warning',
                //      'icon_color'  => 'yellow',
                //     //  'icon_color'  => 'green'
                // ],
            ],
        ],
        


        
        [
            'text'    => 'Registro Diario (PAI)',
            'icon'    => 'fas fa-syringe',
            'submenu' => [
                [
                    'text'        => 'Cargue',
                    'url'         => 'afiliado',
                    'icon'        => 'fas fa-upload',
                    'label'       => '',  
                    'label_color' => 'success',
                    // 'icon_color'  => 'red'
                ],
                //  [
                //     'text'        => 'seguimiento',
                //      'url'         => 'new412_seguimiento',
                //     'icon'        => 'fas fa-fw fa-eye',
                //    'label'       => 'REVISAR', 
                //     'label_color' => 'danger ',
                //    //  'icon_color'  => 'red'
                //  ],
                // [
                //     'text'        => 'Seguimiento',
                //     'url'         => 'Seguimiento',
                //      'icon'        => 'fas fa-fw fa-user',
                //      'label'       => 'ZONA PARA IPS',
                //      'label_color' => 'warning',
                //      'icon_color'  => 'yellow',
                //     //  'icon_color'  => 'green'
                // ],
            ],
        ],
       
        [
            'text'    => 'Tamizajes (cargue)',
            'icon'    => 'fas fa-notes-medical', // Aquí reemplazas el icono
            'submenu' => [
                [
                    'text'        => 'Cargue',
                    'url'         => 'excel-import',
                    'icon'        => 'fas fa-upload',
                    'label'       => '',  
                    'label_color' => 'success',
                ],
                // Aquí puedes agregar más elementos de menú si deseas
            ],
        ],

        

           [
            'text'    => 'Gestantes (cargue)',
            'icon' => 'fas fa-baby-carriage',

            'submenu' => [

                  [
                    'text'        => 'Cargue',
                    'url'         => 'gestantes/import',
                    'icon'        => 'fas fa-upload',
                    'label'       => '',  
                    'label_color' => 'success',
                ],
                [
                    'text'        => 'Tipo 3',
                    'url'         => 'gestantes/tipo3/import',
                    'icon'        => 'fas fa-upload',
                    'label'       => '',  
                    'label_color' => 'success',
                ],
                // Aquí puedes agregar más elementos de menú si deseas
            ],
        ],
        
       
         
     
        ['header' => 'account_settings'],
        // [
        //     'text' => 'profile',
        //     'url'  => 'login',
        //     'icon' => 'fas fa-fw fa-user',
        // ],
        [
            'text' => 'change_password',
            'url'  => 'password/reset',
            'icon' => 'fas fa-fw fa-lock',
        ],
        // [
        //     'text'    => 'multilevel',
        //     'icon'    => 'fas fa-fw fa-share',
        //     'submenu' => [
        //         [
        //             'text' => 'level_one',
        //             'url'  => '#',
        //         ],
        //         [
        //             'text'    => 'level_one',
        //             'url'     => '#',
        //             'submenu' => [
        //                 [
        //                     'text' => 'level_two',
        //                     'url'  => '#',
        //                 ],
        //                 [
        //                     'text'    => 'level_two',
        //                     'url'     => '#',
        //                     'submenu' => [
        //                         [
        //                             'text' => 'level_three',
        //                             'url'  => '#',
        //                         ],
        //                         [
        //                             'text' => 'level_three',
        //                             'url'  => '#',
        //                         ],
        //                     ],
        //                 ],
        //             ],
        //         ],
        //         [
        //             'text' => 'level_one',
        //             'url'  => '#',
        //         ],
        //     ],
        // ],
        ['header' => 'labels'],
        [
            'text'       => 'ALERTAS',
            'icon_color' => 'red',
            'url'        => 'alert',
            'label'       => '***',  
            'label_color' => 'danger',
            'icon_color'  => 'red',
            'icon'        => 'fas fa-exclamation-triangle fa-2x ',
            
        ],

        [
            'text'       => 'ESTADISTICAS',
            'icon_color' => 'blue',
            'url'        => 'grafica-barras',
             'icon'        => 'fas fa-chart-line fa-2x ',
            'label'       => '***',  
            'label_color' => 'primary',
            'icon_color'  => 'blue',
            // 'can'         => 'view-statistics', 
           
        ],

        [
            'text'       => 'MANUAL DE USUARIO',
            'icon_color' => 'yellow',
            'url'        => 'download-pdf',
             'icon'        => 'fas fa-download fa-2x ',
           
            'label_color' => 'primary',
            'icon_color'  => 'yellow',
        
        ],
        // [
        //     'text'       => 'warning',
        //     'icon_color' => 'yellow',
        //     'url'        => '#',
        // ],
        // [
        //     'text'       => 'information',
        //     'icon_color' => 'cyan',
        //     'url'        => '#',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Menu Filters
    |--------------------------------------------------------------------------
    |
    | Here we can modify the menu filters of the admin panel.
    |
    | For detailed instructions you can look the menu filters section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

    'filters' => [
        JeroenNoten\LaravelAdminLte\Menu\Filters\GateFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\HrefFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\SearchFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ActiveFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ClassesFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\LangFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\DataFilter::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugins Initialization
    |--------------------------------------------------------------------------
    |
    | Here we can modify the plugins used inside the admin panel.
    |
    | For detailed instructions you can look the plugins section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Plugins-Configuration
    |
    */

  'plugins' => [
    'Datatables' => [
        'active' => false,
        'files' => [
            [
                'type' => 'js',
                'asset' => false,
                'location' => '//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js',
            ],
            [
                'type' => 'js',
                'asset' => false,
                'location' => '//cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js',
            ],
            [
                'type' => 'css',
                'asset' => false,
                'location' => '//cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css',
            ],
        ],
    ],
    'Select2' => [
        'active' => false,
        'files' => [
            [
                'type' => 'js',
                'asset' => false,
                'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js',
            ],
            [
                'type' => 'css',
                'asset' => false,
                'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.css',
            ],
        ],
    ],
    'Chartjs' => [
        'active' => false,
        'files' => [
            [
                'type' => 'js',
                'asset' => false,
                'location' => '//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.0/Chart.bundle.min.js',
            ],
        ],
    ],
    'Sweetalert2' => [
        'active' => true,
        'files' => [
            [
                'type' => 'js',
                'asset' => false,
                'location' => '//cdn.jsdelivr.net/npm/sweetalert2@8',
            ],
        ],
    ],
    'Pace' => [
        'active' => false,
        'files' => [
            [
                'type' => 'css',
                'asset' => false,
                'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/themes/blue/pace-theme-center-radar.min.css',
            ],
            [
                'type' => 'js',
                'asset' => false,
                'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/pace.min.js',
            ],
        ],
    ],
    // Agregar el plugin para CSS personalizado y cambiar de color la barra lateral izquierda
    'CustomCSS' => [
        'active' => true, // Habilitar el CSS personalizado
        'files' => [
            [
                'type' => 'css',
                'asset' => true,
                'location' => 'vendor/adminlte/dist/css/custom.css', // Nueva ubicación del CSS
            ],
        ],
    ],
],


    /*
    |--------------------------------------------------------------------------
    | IFrame
    |--------------------------------------------------------------------------
    |
    | Here we change the IFrame mode configuration. Note these changes will
    | only apply to the view that extends and enable the IFrame mode.
    |
    | For detailed instructions you can look the iframe mode section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/IFrame-Mode-Configuration
    |
    */

    'iframe' => [
        'default_tab' => [
            'url' => null,
            'title' => null,
        ],
        'buttons' => [
            'close' => false,
            'close_all' => false,
            'close_all_other' => false,
            'scroll_left' => true,
            'scroll_right' => true,
            'fullscreen' => true,
        ],
        'options' => [
            'loading_screen' => 1000,
            'auto_show_new_tab' => true,
            'use_navbar_items' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Livewire
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Livewire support.
    |
    | For detailed instructions you can look the livewire here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'livewire' => false,
];
