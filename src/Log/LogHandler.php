<?php

namespace srag\Plugins\MailLogger\Log;

use ilMailLoggerPlugin;
use ilMailMimeSenderSystem;
use ilMimeMail;
use ilObject;
use ilObjectFactory;
use ilObjUser;
use srag\Plugins\MailLogger\Config\Config;
use srag\Plugins\MailLogger\Utils\MailLoggerTrait;
use srag\DIC\MailLogger\DICTrait;

/**
 * Class LogHandler
 *
 * @package srag\Plugins\MailLogger\Log
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
final class LogHandler {

	use DICTrait;
	use MailLoggerTrait;
	const PLUGIN_CLASS_NAME = ilMailLoggerPlugin::class;
	/**
	 * @var self
	 */
	protected static $instance = NULL;


	/**
	 * @return self
	 */
	public static function getInstance(): self {
		if (self::$instance === NULL) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	/**
	 * LogHandler constructor
	 */
	private function __construct() {

	}


	/**
	 * @param array $mail
	 */
	public function handleSendInternalEmail(array $mail)/*: void*/ {
		$from_user = new ilObjUser($mail["from_user_id"]);
		$to_user = new ilObjUser($mail["to_user_id"]);

		$this->log(strval($mail["subject"]), strval($mail["body"]), $mail["is_system"], $from_user, $to_user, (!empty($mail["context_ref_id"]) ? intval($mail["context_ref_id"]) : NULL));
	}


	/**
	 * @param ilMimeMail $mail
	 */
	public function handleSendExternalEmail(ilMimeMail $mail)/*: void*/ {
		$from_user = new ilObjUser(ilObjUser::_lookupId(current(ilObjUser::_getUserIdsByEmail($mail->getFrom()->getReplyToAddress()))));

		foreach ($mail->getTo() as $to) {

			if (count(ilObjUser::_getUserIdsByEmail($to)) > 0) {
				ilObjUser::_getUserIdsByEmail(ilObjUser::_getUserIdsByEmail($to));
			} else {
				$to_user = new ilObjUser();
				$to_user->setEmail($to);
			}

			$this->log(strval($mail->getSubject()), strval($mail->getFinalBody()), ($mail->getFrom() instanceof
				ilMailMimeSenderSystem), $from_user, $to_user, NULL);
		}
	}


	/**
	 * @param string    $subject
	 * @param string    $body
	 * @param bool      $is_system
	 * @param ilObjUser $from
	 * @param ilObjUser $to
	 * @param int|null  $context_ref_id
	 */
	protected function log(string $subject, string $body, bool $is_system, ilObjUser $from, ilObjUser $to, /*?int*/
		$context_ref_id)/*: void*/ {
		if ($this->shouldLog($is_system, $from)) {
			$log = new Log();

			$log->setSubject($subject);

			$log->setBody($body);

			$log->setIsSystem($is_system);

			$log->setFromEmail(strval($from->getEmail()));
			$log->setFromFirstname(strval($from->getFirstname()));
			$log->setFromLastname(strval($from->getLastname()));
			$log->setFromUserId(intval($from->getId()));

			$log->setToEmail(strval($to->getEmail()));
			$log->setToFirstname(strval($to->getFirstname()));
			$log->setToLastname(strval($to->getLastname()));
			$log->setToUserId(intval($to->getId()));

			/**
			 * @var ilObject|false $context
			 */
			$context = ilObjectFactory::getInstanceByRefId($context_ref_id, false);
			if ($context !== false) {
				$context_title = $log->setContextTitle($context->getTitle());
			} else {
				$context_title = NULL;
			}
			$log->setContextTitle($context_title);
			$log->setContextRefId($context_ref_id);

			$time = time();
			$log->setTimestamp($time);

			$log->store();
		}
	}


	/**
	 * @param bool      $is_system
	 * @param ilObjUser $from
	 *
	 * @return bool
	 */
	protected function shouldLog(bool $is_system, ilObjUser $from): bool {
		if ($is_system) {
			return Config::getField(Config::KEY_LOG_SYSTEM_EMAILS);
		} else {
			$log_email_of_users = Config::getField(Config::KEY_LOG_EMAIL_OF_USERS);

			return in_array($from->getId(), $log_email_of_users);
		}
	}
}