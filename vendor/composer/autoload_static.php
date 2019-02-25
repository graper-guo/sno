<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit0a2343baf2a47a776d5eab0d8cda445d
{
    public static $prefixLengthsPsr4 = array (
        'Q' => 
        array (
            'Qcloud\\Sms\\' => 11,
        ),
        'F' => 
        array (
            'Firebase\\JWT\\' => 13,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Qcloud\\Sms\\' => 
        array (
            0 => __DIR__ . '/..' . '/qcloudsms/qcloudsms_php/src',
        ),
        'Firebase\\JWT\\' => 
        array (
            0 => __DIR__ . '/..' . '/firebase/php-jwt/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit0a2343baf2a47a776d5eab0d8cda445d::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit0a2343baf2a47a776d5eab0d8cda445d::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
