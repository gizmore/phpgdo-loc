<?php
namespace GDO\LoC;

use GDO\Core\GDO_Module;
use GDO\Core\GDT_Checkbox;
use GDO\UI\GDT_Link;
use GDO\UI\GDT_Page;

/**
 * Lines of Code using phploc.
 * Does also a rough global gdo statistic like perf, but for code.
 *
 * @version 7.0.2
 * @since 7.0.1
 * @author gizmore
 */
final class Module_LoC extends GDO_Module
{

	# Almost last
	public int $priority = 99;
	public string $license = 'BSD 3-Clause';

	public function thirdPartyFolders(): array
	{
		return [
			'cli-parser/',
			'php-file-iterator/',
			'phploc/',
		];
	}

	public function getLicenseFilenames(): array
	{
		return [
			'phploc/LICENSE',
			'cli-parser/LICENSE',
			'php-file-iterator/LICENSE',
		];
	}

	##############
	### Config ###
	##############
	public function getConfig(): array
	{
		return [
			GDT_Checkbox::make('hook_sidebar')->initial('1'),
		];
	}

	public function onLoadLanguage(): void { $this->loadLanguage('lang/loc'); }

	############
	### Init ###
	############

	public function onInitSidebar(): void
	{
		if ($this->cfgSidebar())
		{
			$loc = LoC::total()['ncloc'];
			GDT_Page::instance()->leftBar()->addFields(
				GDT_Link::make('link_loc')->textArgs($loc)->icon('code')
						->href($this->href('Details')));
		}
	}

	public function cfgSidebar(): bool { return $this->getConfigValue('hook_sidebar'); }

}
