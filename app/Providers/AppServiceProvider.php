<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Events\Dispatcher;
use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;
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
        $events->listen(BuildingMenu::class, function (BuildingMenu $event) {
            // Add some items to the menu...
            $event->menu->add('MAIN NAVIGATION');
            $event->menu->add([
                'text' => 'Daftar Tamu',
                'url' => route('g.daftar_tamu'),
                'can'=>'provos_and_gate',
            ]);

            $event->menu->add([
                'text' => 'Check In Provos',
                'url' => route('p.input'),
                'can'=>'is_provos',
                'icon'=>'fas fa-user-check'
            ]);

            $event->menu->add([
                'text' => 'Tamu Record',
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
            // $event->menu->add([
            //     'text' => 'Master Bagian',
            //     'url' => route('a.b.index'),
            //     'can'=>'is_admin',
            //     'icon'=>"fas fa-clone"

            // ]);
            $event->menu->add([
                'text' => 'Pengguna',
                'url' => route('a.u.index'),
                'can'=>'is_admin',
                'icon'=>"fas fa-users"
            ]);
             $event->menu->add([
                'text' => 'Report',
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
