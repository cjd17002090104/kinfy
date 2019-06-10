<?php
/**
 * Created by PhpStorm.
 * User: 渐懂
 * Date: 2019/6/8
 * Time: 0:03
 */
return [
    'status' => 'RELEASE',//网站运行状态，RELEASE,DEBUG,SHUTDOWN
    'providers' => [
        'View' => \Kinfy\View\View::class,
        'DB' => \Kinfy\DB\DB::class,
        'c' => \App\Conf::class
    ],
    'provider_interface' => [
        'View' => \Kinfy\View\IView::class,

    ]
];