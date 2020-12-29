<?php
/**
 * module.config.php - User Config
 *
 * Main Config File for Application Module
 *
 * @category Config
 * @package Application
 * @author Verein onePlace
 * @copyright (C) 2020  Verein onePlace <admin@1plc.ch>
 * @license https://opensource.org/licenses/BSD-3-Clause
 * @version 1.0.0
 * @since 1.0.0
 */

namespace OnePlace\Foodorder;

use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use Laminas\ServiceManager\Factory\InvokableFactory;

return [
    'router' => [
        'routes' => [
            'home' => [
                'type'    => Literal::class,
                'options' => [
                    'route'    => '/',
                    'defaults' => [
                        'controller' => Controller\WebController::class,
                        'action'     => 'home',
                    ],
                ],
            ],
            # Module Basic Route
            'food-tracking' => [
                'type'    => Segment::class,
                'options' => [
                    'route' => '/food-tracking/[:id]',
                    'constraints' => [
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\WebController::class,
                        'action'     => 'tracking',
                    ],
                ],
            ],
            'checkout' => [
                'type'    => Literal::class,
                'options' => [
                    'route'    => '/kasse',
                    'defaults' => [
                        'controller' => Controller\WebController::class,
                        'action'     => 'checkout',
                    ],
                ],
            ],
            # Module Basic Route
            'food-zip' => [
                'type'    => Segment::class,
                'options' => [
                    'route' => '/somerandom-seo-[:zip]',
                    'constraints' => [
                        'zip'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\WebController::class,
                        'action'     => 'list',
                    ],
                ],
            ],
            # Module Basic Route
            'food-single' => [
                'type'    => Segment::class,
                'options' => [
                    'route' => '/random-singleseo-[:contact]-[:zip]',
                    'constraints' => [
                        'contact'     => '[0-9]+',
                        'zip'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\WebController::class,
                        'action'     => 'view',
                    ],
                ],
            ],
            'foodorder-api' => [
                'type'    => Segment::class,
                'options' => [
                    'route' => '/foodorder/api[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\ApiController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
        ],
    ],

    'view_manager' => [
        'template_map' => [
            'layout/web'           => __DIR__ . '/../view/layout/web.phtml',
        ],
        'template_path_stack' => [
            'foodorder' => __DIR__ . '/../view',
        ],
    ],

    'plc_x_user_plugins' => [

    ],

    # Translator
    'translator' => [
        'locale' => 'de_DE',
        'translation_file_patterns' => [
            [
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ],
        ],
    ],
];
