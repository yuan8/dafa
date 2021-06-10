<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Events\Dispatcher;
use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;
use Illuminate\Support\Facades\Schema;

use Illuminate\Pagination\Paginator;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(Dispatcher $events)
    {
         Schema::defaultStringLength(191);
           Paginator::defaultView('pagination::bootstrap-4');

        $events->listen(BuildingMenu::class, function (BuildingMenu $event) {
            // Add some items to the menu...

            $event->menu->add('MAIN NAVIGATION');
            $event->menu->add([
                'text' => 'Master Data Tamu',
                'url' => route('g.daftar_tamu'),
                // 'can'=>'provos_and_gate',
            ]);

            $event->menu->add([
                'text' => 'Checkin',
                'url' => route('p.input'),
                'can'=>'is_provos',
                'icon'=>'fas fa-user-check'
            ]);

            $event->menu->add([
                'text' => 'Data Tamu Hari Ini',
                'url' => route('g.index'),
                'can'=>'is_provos',
                'icon'=>'fas fa-list-alt'
            ]);
            // $event->menu->add([
            //     'text' => 'Master Kategori',
            //     'url' => route('a.k.index'),
            //     'can'=>'is_admin',
            //     'icon'=>"fas fa-circle"

            // ]);

            $event->menu->add('ADMIN MENU');
             $event->menu->add([
                'text' => 'Master Bagian',
                'url' => route('a.b.index'),
                'can'=>'is_admin',
                'icon'=>"fas fa-clone"

            ]);

            $event->menu->add([
                'text' => 'Master Pengguna',
                'url' => route('a.u.index'),
                'can'=>'is_admin',
                'icon'=>"fas fa-users"
            ]);
             $event->menu->add([
                'text' => 'Laporan',
                'can'=>'is_admin',
                'icon'=>"fas fa-copy",
                'submenu'=>[
                    [
                        'text' => 'Pengunjung',
                        'url' => route('g.report'),
                        'can'=>'is_admin'
                    ]

                ]
            ]);

        });
    }
}
