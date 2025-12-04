<?php

use DAMA\DoctrineTestBundle\DAMADoctrineTestBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle;
use Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle;
use EasyCorp\Bundle\EasyAdminBundle\EasyAdminBundle;
use Knp\Bundle\MenuBundle\KnpMenuBundle;
use Knp\Bundle\PaginatorBundle\KnpPaginatorBundle;
use League\FlysystemBundle\FlysystemBundle;
use Liip\ImagineBundle\LiipImagineBundle;
use Nelmio\CorsBundle\NelmioCorsBundle;
use Symfony\Bundle\DebugBundle\DebugBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\MakerBundle\MakerBundle;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Bundle\WebProfilerBundle\WebProfilerBundle;
use Symfony\UX\TwigComponent\TwigComponentBundle;
use Symfony\WebpackEncoreBundle\WebpackEncoreBundle;
use Twig\Extra\TwigExtraBundle\TwigExtraBundle;
use Vich\UploaderBundle\VichUploaderBundle;

return [
    FrameworkBundle::class          => [
        'all' => true,
    ],
    DoctrineBundle::class           => [
        'all' => true,
    ],
    DoctrineMigrationsBundle::class => [
        'all' => true,
    ],
    TwigBundle::class               => [
        'all' => true,
    ],
    SecurityBundle::class           => [
        'all' => true,
    ],
    EasyAdminBundle::class          => [
        'all' => true,
    ],
    KnpMenuBundle::class            => [
        'all' => true,
    ],
    KnpPaginatorBundle::class       => [
        'all' => true,
    ],
    TwigExtraBundle::class          => [
        'all' => true,
    ],
    MakerBundle::class              => [
        'dev' => true,
    ],
    WebProfilerBundle::class        => [
        'dev'  => true,
        'test' => true,
    ],
    NelmioCorsBundle::class         => [
        'all' => true,
    ],
    DebugBundle::class              => [
        'dev' => true,
    ],
    VichUploaderBundle::class       => [
        'all' => true,
    ],
    FlysystemBundle::class          => [
        'all' => true,
    ],
    LiipImagineBundle::class        => [
        'all' => true,
    ],
    DoctrineFixturesBundle::class   => [
        'dev'  => true,
        'test' => true,
    ],
    WebpackEncoreBundle::class      => [
        'all' => true,
    ],
    TwigComponentBundle::class      => [
        'all' => true,
    ],
    MonologBundle::class            => [
        'all' => true,
    ],
    DAMADoctrineTestBundle::class   => [
        'test' => true,
    ],
];
