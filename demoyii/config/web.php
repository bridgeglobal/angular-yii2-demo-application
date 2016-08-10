<?php
$params = require(__DIR__ . '/params.php');

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => '6ZY1udwt9Ddr8TkdHXz7rO62mv2bo57x',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ]
        ],
        'response' => [
            'formatters' => [
                'pdf' => [
                    'class' => 'robregonm\pdf\PdfResponseFormatter',
                ],
            ]
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => false,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => require(__DIR__ . '/db.php'),
        
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
            'class' => 'yii\web\UrlManager',
            'rules' => [
                'login'                 => 'user/login',
                'saveImage'             => 'user/saveimage',
                'listPostpeople'        => 'postpeople/listing',
                'createPostpeople'      => 'postpeople/add',
                'editPostpeople'        => 'postpeople/edit',
                'savePostpeople'        => 'postpeople/save',
                'changePostpeopleStatus'=> 'postpeople/changestatus',
                'saveDeliveryAreas'     => 'postpeople/saveareas',
                'saveActivity'          => 'postpeople/saveactivity',
                'editProfile'           => 'user/editprofile',
                'uploadImage'           => 'user/imageupload',
                'listAreas'             => 'area/listing',
                'forgotPassword'        => 'user/forgotpassword',
                'newPassword'           => 'user/newpassword',
                'emailExists'           => 'user/emailexists',
                'fetchImage'            => 'user/fbimage',
                'postpeopleCount'       => 'postpeople/getpostpeoplecount',
                'getPostpersonProfile'  => 'postpeople/getprofile'
            ],
        ],
        'html2pdf' => [
            'class' => 'yii2tech\html2pdf\Manager',
            'viewPath' => '@app/pdf',
            'converter' => [
                'class' => 'yii2tech\html2pdf\converters\Dompdf',
                'defaultOptions' => [
                    'pageSize' => 'A4'
                ],
            ]
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
    ];
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}
return $config;
