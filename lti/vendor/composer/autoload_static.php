<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitee01e9bfc673b5a2eba290f0d27c8646
{
    public static $prefixLengthsPsr4 = array (
        'I' => 
        array (
            'IMSGlobal\\LTI\\' => 14,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'IMSGlobal\\LTI\\' => 
        array (
            0 => __DIR__ . '/..' . '/izumi-kun/lti/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitee01e9bfc673b5a2eba290f0d27c8646::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitee01e9bfc673b5a2eba290f0d27c8646::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitee01e9bfc673b5a2eba290f0d27c8646::$classMap;

        }, null, ClassLoader::class);
    }
}