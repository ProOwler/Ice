<?php
return array (
  'defaultLayoutAction' => 'ice\\action\\Layout_Main',
  'defaultViewRenderClass' => 'ice\\view\\render\\Php',
  'modules' => 
  array (
    'Ice' => '/usr/home/share/PhpstormProjects/ifacesoft/Ice/',
  ),
  'configs' => 
  array (
    'ice\\core\\Model' => 
    array (
      'Ice' => 'ice\\model\\ice',
    ),
    'ice\\core\\Action' => 
    array (
      'Ice' => 'ice\\action',
    ),
  ),
  'host' => 'development',
  'environment' => 
  array (
    'debug' => true,
    'dataProviderKeys' => 
    array (
      'ice\\core\\Loader' => 'Registry:loader/',
      'ice\\core\\Action' => 'Registry:action/',
      'ice\\core\\Route' => 'Registry:router/',
      'ice\\core\\Config' => 'Registry:config/',
      'ice\\core\\View_Render' => 'Registry:view_render/',
      'ice\\core\\Data_Source' => 'Mysqli:default/mysql',
      'ice\\core\\Query_Translator' => 'Registry:query_translator/',
      'ice\\core\\Query' => 'File:cache/query',
      'ice\\core\\Model_Mapping' => 'Registry:model_mapping/',
      'ice\\core\\Data_Mapping' => 'Registry:data_mapping/',
      'ice\\core\\Model_Scheme' => 'Registry:model_scheme/',
    ),
    'dataProviders' => 
    array (
      'Defined:model' => 
      array (
      ),
      'Factory:model' => 
      array (
      ),
      'Registry:loader' => 
      array (
      ),
      'Registry:config' => 
      array (
      ),
      'Registry:view_render' => 
      array (
      ),
      'Registry:query_translator' => 
      array (
      ),
      'Redis:cache' => 
      array (
        'host' => 'localhost',
        'port' => 6379,
      ),
      'File:cache' => 
      array (
        'path' => '/usr/home/share/PhpstormProjects/ifacesoft/cache/',
      ),
      'Registry:model_scheme' => 
      array (
      ),
      'Registry:model_mapping' => 
      array (
      ),
      'Registry:data_mapping' => 
      array (
      ),
      'Registry:router' => 
      array (
      ),
      'Registry:action' => 
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
      'Registry:model_repository' => 
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
  ),
  'viewRenders' => 
  array (
    'Php' => 
    array (
    ),
  ),
);