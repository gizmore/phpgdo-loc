<?php
namespace GDO\LoC\Method;

use GDO\UI\MethodPage;

/**
 * Show details about lines of code.
 *
 * @author gizmore
 *
 */
final class Details extends MethodPage
{

	public function isTrivial(): bool
	{
		return false;
	}

	public function getMethodTitle(): string
	{
		return t('module_loc');
	}

}
