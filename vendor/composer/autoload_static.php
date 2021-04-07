<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInite663ae82517329a020fa1e8699b13e15
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'Picqer\\Barcode\\' => 15,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Picqer\\Barcode\\' => 
        array (
            0 => __DIR__ . '/..' . '/picqer/php-barcode-generator/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInite663ae82517329a020fa1e8699b13e15::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInite663ae82517329a020fa1e8699b13e15::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInite663ae82517329a020fa1e8699b13e15::$classMap;

        }, null, ClassLoader::class);
    }
}
