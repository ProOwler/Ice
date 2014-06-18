<?php
return array (
  'defaultLayoutView' => NULL,
  'defaultLayoutAction' => 'Ice:Layout_Main',
  'defaultViewRenderClass' => 'Ice:Php',
  'modules' => 
  array (
    'Ice' => '/usr/home/dp/PhpstormProjects/ice/Ice/',
  ),
  'configs' => 
  array (
    'ice\\core\\Model' => 
    array (
      'Ice' => 'ice\\model\\ice\\',
    ),
    'ice\\core\\Action' => 
    array (
      'Ice' => 'ice\\action\\',
    ),
    'ice\\core\\Validator' => 
    array (
      'Ice' => 'ice\\validator\\',
    ),
    'ice\\core\\Query_Translator' => 
    array (
      'Ice' => 'ice\\query\\translator\\',
    ),
    'ice\\core\\View_Render' => 
    array (
      'Ice' => 'ice\\view\\render\\',
    ),
  ),
  'host' => 'development',
  'environment' => 
  array (
    'dataProviderKeys' => 
    array (
      'ice\\core\\Loader' => 
      array (
        0 => 'Registry:storage/loader',
        1 => 'Registry:storage/loader',
      ),
      'ice\\core\\Action' => 
      array (
        'instance' => 
        array (
          0 => 'Registry:storage/action',
          1 => 'Registry:storage/action',
        ),
        'output' => 
        array (
          0 => 'Null:cache/action',
          1 => 'Redis:cache/action',
        ),
      ),
      'ice\\core\\Route' => 
      array (
        0 => 'Registry:storage/router',
        1 => 'Registry:storage/router',
      ),
      'ice\\core\\Config' => 
      array (
        0 => 'Registry:storage/config',
        1 => 'Registry:storage/config',
      ),
      'ice\\core\\View_Render' => 
      array (
        0 => 'Registry:storage/view_render',
        1 => 'Registry:storage/view_render',
      ),
      'ice\\core\\Query_Translator' => 
      array (
        0 => 'Registry:storage/query_translator',
        1 => 'Registry:storage/query_translator',
      ),
      'ice\\core\\Query' => 
      array (
        'sql' => 
        array (
          0 => 'Null:cache/sql',
          1 => 'Redis:cache/sql',
        ),
        'query' => 
        array (
          0 => 'Null:cache/query',
          1 => 'Redis:cache/query',
        ),
      ),
      'ice\\core\\Model_Mapping' => 
      array (
        0 => 'Registry:storage/model_mapping',
        1 => 'Registry:storage/model_mapping',
      ),
      'ice\\core\\Data_Mapping' => 
      array (
        0 => 'Registry:storage/data_mapping',
        1 => 'Registry:storage/data_mapping',
      ),
      'ice\\core\\Model_Scheme' => 
      array (
        0 => 'Registry:storage/model_scheme',
        1 => 'Registry:storage/model_scheme',
      ),
      'ice\\core\\Validator' => 
      array (
        0 => 'Registry:storage/validator',
        1 => 'Registry:storage/validator',
      ),
      'ice\\core\\Cache' => 
      array (
        0 => 'Null:cache/tags',
        1 => 'Redis:cache/tags',
      ),
      'ice\\core\\View' => 
      array (
        0 => 'Null:cache/view',
        1 => 'Redis:cache/view',
      ),
      'ice\\core\\Data_Source' => 'Mysqli:default/mysql',
    ),
    'debug' => 
    array (
      0 => true,
      1 => false,
    ),
  ),
  'viewRenders' => 
  array (
    'Php' => 
    array (
    ),
  ),
  'dataProviders' => 
  array (
    'Apc:storage' => 
    array (
    ),
    'Registry:storage' => 
    array (
    ),
    'Defined:model' => 
    array (
    ),
    'Factory:model' => 
    array (
    ),
    'Redis:cache' => 
    array (
      'host' => 'localhost',
      'port' => 6379,
    ),
    'File:cache' => 
    array (
      'path' => '/usr/home/dp/PhpstormProjects/ice/cache/',
    ),
    'File:output' => 
    array (
      'path' => NULL,
    ),
    'Null:cache' => 
    array (
    ),
    'Request:http' => 
    array (
    ),
    'Cli:prompt' => 
    array (
    ),
    'Session:php' => 
    array (
    ),
    'Registry:view_render' => 
    array (
    ),
    'Registry:model_repository' => 
    array (
    ),
    'Registry:action' => 
    array (
    ),
    'Registry:registry' => 
    array (
    ),
    'Router:route' => 
    array (
    ),
    'Mysqli:default' => 
    array (
      'host' => 'localhost',
      'username' => 'root',
      'password' => '',
      'charset' => 'utf8',
    ),
  ),
);