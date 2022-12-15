<?php
namespace GDO\LoC;

use GDO\Core\GDO_Module;
use GDO\Core\GDT_Checkbox;
use GDO\UI\GDT_Page;
use GDO\UI\GDT_Link;

/**
 * Lines of Code using 
 * 
 * @author gizmore
 * @version 7.0.1
 */
final class Module_LoC extends GDO_Module
{
	# Almost last
	public int $priority = 99;
	public string $license = "BSD 3-Clause";
	
	public function thirdPartyFolders() : array
	{
		return [
			'cli-parser/',
			'php-file-iterator/',
			'phploc/',
		];
	}
	
	public function getLicenseFilenames() : array
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
	public function getConfig() : array
	{
		return [
			GDT_Checkbox::make('hook_sidebar')->initial('1'),
		];
	}
	public function cfgSidebar() : bool { return $this->getConfigValue('hook_sidebar'); }
	
	############
	### Init ###
	############
	public function onLoadLanguage() : void { $this->loadLanguage('lang/loc'); }
	
	public function onInitSidebar() : void
	{
		if ($this->cfgSidebar())
		{
			$loc = LoC::total()['ncloc'];
			GDT_Page::instance()->leftBar()->addFields(
				GDT_Link::make('link_loc')->textArgs($loc)->icon('code')
					->href($this->href('Details')));
		}
	}
	
}
