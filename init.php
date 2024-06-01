<?php
use Siberian\Assets;
use Siberian\Translation;
use Siberian_Module as Module;

$init = function($bootstrap) {
    
    Assets::registerScss([
        '/app/local/modules/Listcheckin/features/listcheckin/scss/listcheckin.scss'
    ]);
    
    Translation::registerExtractor(
        'listcheckin',
        'Listcheckin',
        '/app/local/modules/Listcheckin/resources/translations/default/listcheckin.po');

};

