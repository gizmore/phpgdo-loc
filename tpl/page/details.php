<?php
namespace GDO\LoC\tpl\page;

use GDO\LoC\LoC;
use GDO\Core\ModuleLoader;
use GDO\Core\GDT_UInt;
use GDO\UI\GDT_Card;
use GDO\UI\GDT_Accordeon;
use GDO\UI\GDT_Paragraph;

function printLoC(array $loc, string $titleRaw)
{
	$cont = GDT_Accordeon::make()->titleRaw(sprintf('%s (%s %s, %s %s)', $titleRaw, $loc['files'], t('files'), $loc['ncloc'], 'LoC'));
	$card = GDT_Card::make();
	foreach ($loc as $key => $value)
	{
		$name = "loc_{$key}";
		$int = GDT_UInt::make()->value($value)->icon('amt')->label($name);
		$card->addFields($int);
	}
	$cont->addField($card);
	return $cont;
}

$cont = GDT_Card::make();

$cont->addField(GDT_Paragraph::make()->text('info_loc_details'));

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
