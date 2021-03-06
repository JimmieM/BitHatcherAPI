<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit1a4d35ec99f810cb9826e63eb104df41
{
    public static $prefixLengthsPsr4 = array (
        'A' => 
        array (
            'Ably\\' => 5,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Ably\\' => 
        array (
            0 => __DIR__ . '/..' . '/ably/ably-php/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit1a4d35ec99f810cb9826e63eb104df41::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit1a4d35ec99f810cb9826e63eb104df41::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
