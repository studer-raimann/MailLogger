<?php

require_once __DIR__ . "/../../vendor/autoload.php";

use srag\AVL\Plugins\MailLogger\Log\LogTableGUI;
use srag\AVL\Plugins\MailLogger\Utils\MailLoggerTrait;
use srag\DIC\DICTrait;

/**
 * Class MailLoggerLogGUI
 *
 * @author            studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 *
 * @ilCtrl_isCalledBy MailLoggerLogGUI: ilUIPluginRouterGUI
 */
class MailLoggerLogGUI {

	use DICTrait;
	use MailLoggerTrait;
	const PLUGIN_CLASS_NAME = ilMailLoggerPlugin::class;
	const CMD_APPLY_FILTER = "applyFilter";
	const CMD_LOG = "log";
	const CMD_RESET_FILTER = "resetFilter";
	const LANG_MODULE_LOG = "log";


	/**
	 * MailLoggerLogGUI constructor
	 */
	public function __construct() {

	}


	/**
	 *
	 */
	public function executeCommand()/*: void*/ {
		$next_class = self::dic()->ctrl()->getNextClass($this);

		switch (strtolower($next_class)) {
			default:
				if (!self::access()->hasLogAccess()) {
					ilUtil::sendInfo(self::plugin()->translate("no_access", self:: LANG_MODULE_LOG), true);
					self::dic()->ctrl()->redirectByClass(ilRepositoryGUI::class);
				}

				$cmd = self::dic()->ctrl()->getCmd();

				switch ($cmd) {
					case self::CMD_APPLY_FILTER:
					case self::CMD_LOG:
					case self::CMD_RESET_FILTER:
						$this->{$cmd}();
						break;

					default:
						break;
				}
				break;
		}
	}


	/**
	 * @param string $cmd
	 *
	 * @return LogTableGUI
	 */
	protected function getLogTable(string $cmd = self::CMD_LOG): LogTableGUI {
		$table = new LogTableGUI($this, $cmd);

		return $table;
	}


	/**
	 *
	 */
	protected function log()/*: void*/ {
		$table = $this->getLogTable();

		self::plugin()->output($table);
	}


	/**
	 *
	 */
	protected function applyFilter()/*: void*/ {
		$table = $this->getLogTable(self::CMD_APPLY_FILTER);

		$table->writeFilterToSession();

		self::dic()->ctrl()->redirect($this, self::CMD_LOG);
	}


	/**
	 *
	 */
	protected function resetFilter()/*: void*/ {
		$table = $this->getLogTable(self::CMD_RESET_FILTER);

		$table->resetFilter();

		$table->resetOffset();

		self::dic()->ctrl()->redirect($this, self::CMD_LOG);
	}
}
