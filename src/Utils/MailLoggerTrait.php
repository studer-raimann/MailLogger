<?php

namespace srag\AVL\Plugins\MailLogger\Utils;

use srag\AVL\Plugins\MailLogger\Access\Access;
use srag\AVL\Plugins\MailLogger\Log\LogHandler;

/**
 * Trait MailLoggerTrait
 *
 * @package srag\AVL\Plugins\MailLogger\Utils
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
trait MailLoggerTrait {

	/**
	 * @return Access
	 */
	protected static function access(): Access {
		return Access::getInstance();
	}


	/**
	 * @return LogHandler
	 */
	protected static function logHandler(): LogHandler {
		return LogHandler::getInstance();
	}
}
