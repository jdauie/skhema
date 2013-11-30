<?php

define('SKHEMA_DIR', __dir__.'/..');

require_once(SKHEMA_DIR.'/Util/Stopwatch.php');

$sw = Jacere\Stopwatch::StartNew('Template');

require_once(SKHEMA_DIR.'/Template.php');
require_once('_data.php');

$sw->Save("~");

$updateCache = isset($_GET['update']);
$manager = Jacere\TemplateManager::Create(__dir__.'/templates', $updateCache);
$sw->Save('loadtemplate');

$testIterations = isset($_GET['it']) ? (int)$_GET['it'] : 1;
$rootTemplateName = isset($_GET['template']) ? $_GET['template'] : 'Posts';
for ($i = 0; $i < $testIterations; $i++) {
	$output = $manager->Evaluate($rootTemplateName, $testTemplateSources[$rootTemplateName]);
}
$sw->Save("evaluate($testIterations)");

$sw->Stop();

echo $output;

echo $sw;

?>