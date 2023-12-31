<?php
/**
 * Configuration file for 'yii message/extract' command.
 *
 * This file is automatically generated by 'yii message/config' command.
 * It contains parameters for source code messages extraction.
 * You may modify this file to suit your needs.
 *
 * You can use 'yii message/config-template' command to create
 * template configuration file with detailed description for each parameter.
 */

//run this in cmd in console folder
// cd /www/fh/site/console
//yii message ../common/config/messages/config.php
return [
    'color' => null,
    'interactive' => true,
    'help' => null,
    'sourcePath' => '@base',
    'messagePath' => '@common/messages',
    'languages' => ['en-int', 'vn-VN', 'th-TH', 'zh-MY'],
    'translator' => 'Yii::t',
    'sort' => false,
    'overwrite' => true,
    'removeUnused' => false,
    'markUnused' => true,
    'except' => [
        '.svn',
        '.git',
        '.gitignore',
        '.gitkeep',
        '.hgignore',
        '.hgkeep',
        '/messages',
        '/BaseYii.php',
        '.hgkeep',
        '/messages',
        '/vendor',
        '/storage',
        '/tests'        
    ],
    'only' => [
        '*.php',
    ],
    'format' => 'php',
    'db' => 'db',
    'sourceMessageTable' => '{{%source_message}}',
    'messageTable' => '{{%message}}',
    'catalog' => 'messages',
    'ignoreCategories' => ['yii'],
];
