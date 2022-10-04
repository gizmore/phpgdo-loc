<?php
namespace GDO\LoC;

use GDO\Core\GDO_Module;
use GDO\DB\Cache;
use GDO\Core\ModuleLoader;
use GDO\Util\Arrays;
use GDO\Util\Strings;
use SebastianBergmann\PHPLOC\ArgumentsBuilder;

/**
 * LoC API
 * 
 * @author gizmore
 * @version 7.0.1
 */
final class LoC
{
	private static bool $INITED = false;
	
	###########
	### API ###
	###########
	public static function total() : array
	{
		$key = 'loc__total';
		if (null === ($cache = Cache::get($key)))
		{
			$cache = self::doLoCTotal();
			Cache::set($key, $cache);
		}
		return $cache;
	}
	
	public static function module(GDO_Module $module) : array
	{
		$key = "loc_{$module->getModuleName()}";
		if (null === ($cache = Cache::get($key)))
		{
			$cache = self::doLoCModule($module);
			Cache::set($key, $cache);
		}
		return $cache;
	}

	############
	### Init ###
	############
	public static function init() : void
	{
		if (!self::$INITED)
		{
			spl_autoload_register([self::class, 'autoloadPHPLOC']);
			self::$INITED = true;
		}
	}
	
	public static function autoloadPHPLOC(string $class) : void
	{
		self::autoloadFrom('SebastianBergmann\\PHPLOC\\', 'phploc/src/', $class);
		self::autoloadFrom('SebastianBergmann\\CliParser\\', 'cli-parser/src/', $class);
	}
	
	private static function autoloadFrom(string $prefix, string $path, string $class) : bool
	{
		if (str_starts_with($class, $prefix))
		{
			$class = Strings::substrFrom($class, $prefix);
			$class = str_replace('\\', '/', $class) . '.php';
			$class = Module_LoC::instance()->filePath($path.$class);
			require_once $class;
			return true;
		}
		return false;
	}
	
	###############
	### Private ###
	###############
	private static function doLoCTotal() : array
	{
		$loader = ModuleLoader::instance();
		$data = [];
		foreach ($loader->getEnabledModules() as $module)
		{
			$loc = self::module($module);
			$data = Arrays::sumEach([$loc, $data]);
		}
		return $data;
	}
	
	
	private static function doLoCModule(GDO_Module $module) : array
	{
		self::init();
		$argv = [
			
		];
		
		foreach ($module->thirdPartyFolders() as $path)
		{
			$argv[] = '--exclude=' . $path;
		}
		
		$argv[] = $module->filePath();
		
		$arguments = (new ArgumentsBuilder)->build($argv);
		
		$files = (new Facade)->getFilesAsArray(
			$arguments->directories(),
			$arguments->suffixes(),
			'',
			$arguments->exclude()
			);
		
		if (empty($files)) {
			print 'No files found to scan' . PHP_EOL;
			
			return 1;
		}
		
		$result = (new Analyser)->countFiles($files, $arguments->countTests());
		
		(new TextPrinter)->printResult($result, $arguments->countTests());
		
		if ($arguments->csvLogfile()) {
			$printer = new CsvPrinter;
			
			$printer->printResult($arguments->csvLogfile(), $result);
		}
		
		if ($arguments->jsonLogfile()) {
			$printer = new JsonPrinter;
			
			$printer->printResult($arguments->jsonLogfile(), $result);
		}
		
		if ($arguments->xmlLogfile()) {
			$printer = new XmlPrinter;
			
			$printer->printResult($arguments->xmlLogfile(), $result);
		}
		
		return 0;
		
	}
	
}
