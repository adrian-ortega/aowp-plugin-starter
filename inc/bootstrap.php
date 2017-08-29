<?php

namespace AOD;

use AOD\Admin\Scripts;

$plugin = new Plugin();

$plugin->init('AOD Plugin');
$plugin->load('admin_scripts', new Scripts);

$plugin->run();