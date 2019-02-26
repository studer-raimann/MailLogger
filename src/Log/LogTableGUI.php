<?php

namespace srag\Plugins\MailLogger\Log;

use ilAdvancedSelectionListGUI;
use ilDatePresentation;
use ilDateTime;
use ilMailLoggerPlugin;
use ilTextInputGUI;
use srag\CustomInputGUIs\MailLogger\DateDurationInputGUI\DateDurationInputGUI;
use srag\CustomInputGUIs\MailLogger\PropertyFormGUI\PropertyFormGUI;
use srag\CustomInputGUIs\MailLogger\TableGUI\TableGUI;
use srag\Plugins\MailLogger\Utils\MailLoggerTrait;

/**
 * Class LogTableGUI
 *
 * @package srag\Plugins\MailLogger\Log
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class LogTableGUI extends TableGUI {

	use MailLoggerTrait;
	const PLUGIN_CLASS_NAME = ilMailLoggerPlugin::class;
	const LANG_MODULE = LogGUI::LANG_MODULE_LOG;


	/**
	 * @inheritdoc
	 */
	protected function getColumnValue(/*string*/
		$column, /*array*/
		$row, /*bool*/
		$raw_export = false): string {
		switch ($column) {
			case "timestamp":
				if ($raw_export) {
					$column = $row[$column];
				} else {
					$column = ilDatePresentation::formatDate(new ilDateTime($row[$column], IL_CAL_UNIX));
				}
				break;

			default:
				$column = $row[$column];
				break;
		}

		return strval($column);
	}


	/**
	 * @inheritdoc
	 */
	public function getSelectableColumns2(): array {
		$columns = [
			"subject" => "subject",
			"from_email" => "from_email",
			"from_firstname" => "from_firstname",
			"from_lastname" => "from_lastname",
			"from_user_id" => "from_user_id",
			"to_email" => "to_email",
			"to_firstname" => "to_firstname",
			"to_lastname" => "to_lastname",
			"to_user_id" => "to_user_id",
			/*"context_title" => "context_title",
			"context_ref_id" => "context_ref_id",*/
			"timestamp" => "timestamp"
		];
		$default = [
			"subject",
			"from_email",
			"to_email",
			//"context_title",
			"timestamp"
		];

		$columns = array_map(function (string $key) use (&$default): array {
			return [
				"id" => $key,
				"default" => in_array($key, $default),
				"sort" => true
			];
		}, $columns);

		return $columns;
	}


	/**
	 * @inheritdoc
	 */
	protected function initColumns()/*: void*/ {
		parent::initColumns();

		$this->addColumn(self::plugin()->translate("actions", self::LANG_MODULE));

		$this->setDefaultOrderField("timestamp");
		$this->setDefaultOrderDirection("desc");
	}


	/**
	 * @inheritdoc
	 */
	protected function initData()/*: void*/ {
		$filter = $this->getFilterValues();

		$subject = $filter["subject"];
		$body = $filter["body"];
		$from_email = $filter["from_email"];
		$from_firstname = $filter["from_firstname"];
		$from_lastname = $filter["from_lastname"];
		$to_email = $filter["to_email"];
		$to_firstname = $filter["to_firstname"];
		$to_lastname = $filter["to_lastname"];
		/*$context_title = $filter["context_title"];
		$context_ref_id = $filter["context_ref_id"];
		if (!empty($context_ref_id)) {
			$context_ref_id = intval($context_ref_id);
		} else {
			$context_ref_id = NULL;
		}*/
		$context_title = "";
		$context_ref_id = NULL;
		$timestamp_start = $filter["timestamp"]["start"];
		if (!empty($timestamp_start)) {
			$timestamp_start = intval($timestamp_start);
		} else {
			$timestamp_start = NULL;
		}
		$timestamp_end = $filter["timestamp"]["end"];
		if (!empty($timestamp_end)) {
			$timestamp_end = intval($timestamp_end);
		} else {
			$timestamp_end = NULL;
		}

		$this->setData(self::logs()
			->getLogs($subject, $body, $from_email, $from_firstname, $from_lastname, $to_email, $to_firstname, $to_lastname, $context_title, $context_ref_id, $timestamp_start, $timestamp_end));
	}


	/**
	 * @inheritdoc
	 */
	protected function initExport()/*: void*/ {
		$this->setExportFormats([ self::EXPORT_CSV, self::EXPORT_EXCEL ]);
	}


	/**
	 * @inheritdoc
	 */
	protected function initFilterFields()/*: void*/ {
		self::dic()->language()->loadLanguageModule("form");

		$this->filter_fields = [
			"subject" => [
				PropertyFormGUI::PROPERTY_CLASS => ilTextInputGUI::class
			],
			"body" => [
				PropertyFormGUI::PROPERTY_CLASS => ilTextInputGUI::class
			],
			"from_email" => [
				PropertyFormGUI::PROPERTY_CLASS => ilTextInputGUI::class
			],
			"from_firstname" => [
				PropertyFormGUI::PROPERTY_CLASS => ilTextInputGUI::class
			],
			"from_lastname" => [
				PropertyFormGUI::PROPERTY_CLASS => ilTextInputGUI::class
			],
			"to_email" => [
				PropertyFormGUI::PROPERTY_CLASS => ilTextInputGUI::class
			],
			"to_firstname" => [
				PropertyFormGUI::PROPERTY_CLASS => ilTextInputGUI::class
			],
			"to_lastname" => [
				PropertyFormGUI::PROPERTY_CLASS => ilTextInputGUI::class
			],
			/*"context_title" => [
				PropertyFormGUI::PROPERTY_CLASS => ilTextInputGUI::class
			],
			"context_ref_id" => [
				PropertyFormGUI::PROPERTY_CLASS => NumberInputGUI::class,
				"setMinValue" => 0
			]*/
			"timestamp" => [
				PropertyFormGUI::PROPERTY_CLASS => DateDurationInputGUI::class,
				"setShowTime" => true
			]
		];
	}


	/**
	 * @inheritdoc
	 */
	protected function initId()/*: void*/ {
		$this->setId("maillogger_log");
	}


	/**
	 * @inheritdoc
	 */
	protected function initTitle()/*: void*/ {
		$this->setTitle(self::plugin()->translate("log", LogGUI::CMD_LOG));
	}


	/**
	 * @param array $row
	 */
	protected function fillRow(/*array*/
		$row)/*: void*/ {
		self::dic()->ctrl()->setParameter($this->parent_obj, "log_id", $row["id"]);

		parent::fillRow($row);

		$actions = new ilAdvancedSelectionListGUI();
		$actions->setListTitle(self::plugin()->translate("actions", self::LANG_MODULE));
		$actions->addItem(self::plugin()->translate("show_email", self::LANG_MODULE), "", self::dic()->ctrl()
			->getLinkTarget($this->parent_obj, LogGUI::CMD_SHOW_EMAIL));
		$this->tpl->setVariable("COLUMN", self::output()->getHTML($actions));
		$this->tpl->parseCurrentBlock();

		self::dic()->ctrl()->setParameter($this->parent_obj, "log_id", NULL);
	}
}
