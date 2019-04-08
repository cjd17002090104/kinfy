<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit2ac9855dfc6564e1b6cacfa21b0b7e12
{
    public static $prefixLengthsPsr4 = array (
        't' => 
        array (
            'test\\' => 5,
        ),
        'K' => 
        array (
            'Kinfy\\' => 6,
        ),
        'A' => 
        array (
            'App\\' => 4,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'test\\' => 
        array (
            0 => __DIR__ . '/../..' . '/test',
        ),
        'Kinfy\\' => 
        array (
            0 => __DIR__ . '/../..' . '/Kinfy',
        ),
        'App\\' => 
        array (
            0 => __DIR__ . '/../..' . '/app',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit2ac9855dfc6564e1b6cacfa21b0b7e12::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit2ac9855dfc6564e1b6cacfa21b0b7e12::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
