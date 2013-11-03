<?php

require_once('_deploy/Template.php');
require_once('_data.php');

$name = 'Posts';
$context = $testTemplateSources['Posts'];

// example of clean usage
$manager = Jacere\TemplateManager::Create();
echo $manager->Evaluate($name, $context);

?>