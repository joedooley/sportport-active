<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit43c0695e8f5aa38110de01247f79e3eb
{
    public static $prefixLengthsPsr4 = array (
        'L' => 
        array (
            'LCache\\' => 7,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'LCache\\' => 
        array (
            0 => __DIR__ . '/..' . '/lcache/lcache/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit43c0695e8f5aa38110de01247f79e3eb::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit43c0695e8f5aa38110de01247f79e3eb::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
