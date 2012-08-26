<?php
/***************************************************************
 *  Copyright notice
 *
 * (c) 2010-present Nico de Haen <typo3@ndh-websolutions.de>
 * (c) 2010-present Steffen Mueller <typo3@t3node.com>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * IfViewHelper to perform some more powerful conditions in Fluid
 */
class Tx_VhCollection_ViewHelper_IfViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper implements Tx_Fluid_Core_ViewHelper_Facets_ChildNodeAccessInterface{

	/**
	 * Comparison operators
	 *
	 * @var array
	 */
	protected $compareOperators = array('>=','<=','!=','>','<','==');

	/**
	 * Logical operators
	 *
	 * @var array
	 */
	protected $booleanOperators = array('&&!','&&','||!','||');

	/**
	 * Template Parser Instance
	 *
	 * @var Tx_Fluid_Core_Parser_TemplateParser
	 */
	protected $templateParser = NULL;


	/**
	 * inject TemplateParser
	 *
	 * @param Tx_Fluid_Core_Parser_TemplateParser $templateParser
	 * @return void
	 */
	public function injectTemplateParser(Tx_Fluid_Core_Parser_TemplateParser $templateParser) {
		$this->templateParser = $templateParser;
	}

	/**
	 * Render the ViewHelper
	 *
	 * @param string $condition The condition example: {project.id} == 42
	 * @return string Rendered string if condition was true
	 */
	public function render($condition) {

			// Special case: if no condition isset, always render content
		if (empty($condition)) {
			return $this->renderChildren();
		}

		$conditionResult = FALSE;
		$compareOperator = NULL;
		$boolConditionParts = array();
		$condition = str_replace(' ', '', $condition);

		foreach ($this->booleanOperators as $booleanOperator) {
			if (strpos($condition, $booleanOperator) > 0) {
				$boolConditionParts = explode($booleanOperator, $condition);
				$compareOperator = $booleanOperator;
				break;
			}
		}

		if ($compareOperator) {
			$res1 = $this->testCondition($boolConditionParts[0]);
			$res2 = $this->testCondition($boolConditionParts[1]);
			switch ($compareOperator) {
				default:
					break;
				case '&&!': $conditionResult = ($res1 && !$res2);
					break;
				case '&&': $conditionResult =  ($res1 && $res2);
					break;
				case '||!': $conditionResult =  ($res1 || !$res2);
					break;
				case '||': $conditionResult =  ($res1 || $res2);
					break;
			}
		} else {
			$conditionResult = $this->testCondition($condition);
		}
		if ($conditionResult) {
			$result = $this->renderChildren();
		} else {
			$result = '';
		}

		return $result;
	}

	/**
	 * Executes the condition test
	 *
	 * @param string $condition
	 * @return bool
	 */
	protected function testCondition($condition) {

		$conditionParts = array();
		$compareOperator = '';
		$conditionResult = false;

		foreach($this->compareOperators as $operator) {
			if (strpos($condition, $operator) > 0) {
				$conditionParts = explode($operator,$condition);
				$compareOperator = $operator;
				break;
			}
		}

		if(!empty($compareOperator) && count($conditionParts) == 2) {
			$test = $this->templateParser->parse($conditionParts[0])->render($this->getRenderingContext());
			switch ($compareOperator) {
				default:
					break;
				case '==': $conditionResult = ($test == $conditionParts[1]);
					break;
				case '!=': $conditionResult = ($test == $conditionParts[1]);
					break;
				case '>': $conditionResult = (floatval($test) > floatval($conditionParts[1]));
					break;
				case '>=': $conditionResult = (floatval($test) >= floatval($conditionParts[1]));
					break;
				case '<': $conditionResult = (floatval($test) < floatval($conditionParts[1]));
					break;
				case '<=': $conditionResult = (floatval($test) <= floatval($conditionParts[1]));
			}
		} else {
			$test = $this->templateParser->parse($condition)->render($this->getRenderingContext());
			if(empty($test)){
				$conditionResult = FALSE;
			} else {
				$conditionResult = TRUE;
			}
		}

		return $conditionResult;
	}

	/**
	 * Required to implement Accessor interface
	 *
	 * @param array $childNodes
	 * @return void
	 */
	public function setChildNodes(array $childNodes) {}

}

?>