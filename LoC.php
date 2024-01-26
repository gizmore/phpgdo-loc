<?php
namespace GDO\LoC;

use GDO\Core\Debug;
use GDO\Core\GDO_Module;
use GDO\Core\ModuleLoader;
use GDO\DB\Cache;
use GDO\Util\Arrays;
use GDO\Util\Strings;
use SebastianBergmann\FileIterator\Facade;
use SebastianBergmann\PHPLOC\Analyser;
use SebastianBergmann\PHPLOC\ArgumentsBuilder;

/**
 * LoC API
 *
 * @version 7.0.1
 * @author gizmore
 */
final class LoC
{

	private static bool $INITED = false;

	# ##########
	# ## API ###
	# ##########
	private static array $AUTOLOAD_EXCEPTIONS = [
		'SebastianBergmann\\PHPLOC\\Application' => 'phploc/src/CLI/Application',
		'SebastianBergmann\\PHPLOC\\Arguments' => 'phploc/src/CLI/Arguments',
        'SebastianBergmann\\PHPLOC\\ArgumentsBuilder' => 'phploc/src/CLI/ArgumentsBuilder',
        'SebastianBergmann\Complexity\ComplexityCalculatingVisitor' => 'complexity/src/Visitor/ComplexityCalculatingVisitor',
        'SebastianBergmann\Complexity\CyclomaticComplexityCalculatingVisitor' => 'complexity/src/Visitor/CyclomaticComplexityCalculatingVisitor',
        'SebastianBergmann\LinesOfCode\NegativeValueException' => 'lines-of-code/src/Exception/NegativeValueException',
	];

	public static function gdo(): array
	{
		$key = 'loc__gdo';
		if (null === ($cache = Cache::get($key)))
		{
			$cache = self::doLoCTotal();
			Cache::set($key, $cache);
		}
		return $cache;
	}

	private static function doLoCTotal(): array
	{
		$loader = ModuleLoader::instance();
		$data = [];
		foreach ($loader->getEnabledModules() as $module)
		{
			$loc = self::module($module);
			$data = Arrays::sumEach([
				$loc,
				$data,
			]);
		}
		return $data;
	}

	# ###########
	# ## Init ###
	# ###########

	public static function module(GDO_Module $module): array
	{
		$key = "loc_{$module->getModuleName()}";
		if (null === ($cache = Cache::get($key)))
		{
			$cache = self::doLoCModule($module);
			Cache::set($key, $cache);
		}
		return $cache;
	}

	private static function doLoCModule(GDO_Module $module): array
	{
		self::init();
		$argv = [];
		foreach ($module->thirdPartyFolders() as $path)
		{
			$argv[] = '--exclude';
			$argv[] = $module->filePath(trim($path, '/') . '/');
		}
		$argv[] = $module->filePath();
		$argv[] = GDO_PATH . '*.php';
		$arguments = (new ArgumentsBuilder())->build($argv);
		$files = (new Facade())->getFilesAsArray($arguments->directories()[0], $arguments->suffixes(), '',
			$arguments->exclude());
        $old = error_reporting();
        error_reporting(0);
        try {
            $result = (new Analyser())->analyse($files, false);
            return [
                'ncloc' => $result->nonCommentLinesOfCode(),
            ];
        } catch (\Throwable) {
            return [];
        } finally {
            error_reporting($old);
        }
	}

	public static function init(): void
	{
		if (!self::$INITED)
		{
			spl_autoload_register([
				self::class,
				'autoloadPHPLOC',
			]);
			self::$INITED = true;
		}
	}

	public static function total(): array
	{
		$key = 'loc__total';
		if (null === ($cache = Cache::get($key)))
		{
			$cache = self::doLoCTotal();
			Cache::set($key, $cache);
		}
		return $cache;
	}

	public static function autoloadPHPLOC(string $class): bool
	{
        return self::autoLoadException($class) ||
            self::autoloadFrom('PhpParser\\', 'php-parser/lib/PhpParser/', $class) ||
            self::autoloadFrom('SebastianBergmann\\PHPLOC\\', 'phploc/src/', $class) ||
            self::autoloadFrom('SebastianBergmann\\Complexity\\', 'complexity/src/Complexity/', $class) ||
            self::autoloadFrom('SebastianBergmann\\CliParser\\', 'cli-parser/src/', $class) ||
            self::autoloadFrom('SebastianBergmann\\FileIterator\\', 'php-file-iterator/src/', $class) ||
            self::autoloadFrom('SebastianBergmann\\LinesOfCode\\', 'lines-of-code/src/', $class);

	}

	# ##############
	# ## Private ###
	# ##############

	private static function autoLoadException(string $class): bool
	{
		if (isset(self::$AUTOLOAD_EXCEPTIONS[$class]))
		{
			require self::$AUTOLOAD_EXCEPTIONS[$class] . '.php';
			return true;
		}
		return false;
	}

	private static function autoloadFrom(string $prefix, string $path, string $class): bool
	{
		if (str_starts_with($class, $prefix))
		{
			$class = Strings::substrFrom($class, $prefix);
			$class = str_replace('\\', '/', $class) . '.php';
			$class = Module_LoC::instance()->filePath($path . $class);
			require $class;
			return true;
		}
		return false;
	}

}
