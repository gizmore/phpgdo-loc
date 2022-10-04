<?php
namespace GDO\LoC\tpl\page;

use GDO\LoC\LoC;
use GDO\Core\ModuleLoader;
use GDO\Core\GDT_UInt;
use GDO\UI\GDT_Card;
use GDO\UI\GDT_Accordeon;

function printLoC(array $loc, string $titleRaw)
{
	$cont = GDT_Accordeon::make()->titleRaw($titleRaw);
	foreach ($loc as $key => $value)
	{
		$name = "loc_{$key}";
		$int = GDT_UInt::make()->value($value)->icon('amt')->label($name);
		$cont->addFields($int);
	}
	return $cont;
}

$cont = GDT_Card::make();

# Total
$total = LoC::total();
$cont->addFields(printLoC($total, t('total')));

# Each
foreach (ModuleLoader::instance()->getEnabledModules() as $module)
{
	$data = Loc::module($module);
	$cont->addFields(printLoC($data, $module->gdoHumanName()));
}

# out
echo $cont->render();
