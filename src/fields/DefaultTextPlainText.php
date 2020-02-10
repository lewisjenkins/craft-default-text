<?php

namespace lewisjenkins\craftdefaulttext\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\fields\PlainText;

class DefaultTextPlainText extends PlainText
{
    public static function displayName(): string
    {
        return Craft::t('craft-default-text', parent::displayName() . ' (with default value)');
    }
    
    public $defaultValue;
    public $revertToDefault = false;
    
    public function getSettingsHtml()
    {
        return Craft::$app->getView()->renderTemplate(
            'craft-default-text/_components/fields/DefaultTextPlainText_settings',
            [
                'field' => $this,
            ]
        );
    }
    
    public function serializeValue($value, ElementInterface $element = null)
    {	
		if ($value == '' and $this->revertToDefault) {
			$value = $this->getRenderedValue($this->defaultValue);
		};
		
		return $value;
    }
    
    public function getInputHtml($value, ElementInterface $element = null): string
    {	
		$this->placeholder = $this->getRenderedValue($this->placeholder);
		
		if ($this->isFresh($element) ) {
			$value = $this->getRenderedValue($this->defaultValue);
		};
	    
        return Craft::$app->getView()->renderTemplate('craft-default-text/_components/fields/DefaultTextPlainText_input',
            [
                'name' => $this->handle,
                'value' => $value,
                'field' => $this,
            ]);
    }
    
    private function getRenderedValue($field, ElementInterface $element = null)
    {
		$view = Craft::$app->getView();
		$templateMode = $view->getTemplateMode();
		$view->setTemplateMode($view::TEMPLATE_MODE_SITE);
		
		$variables['element'] = $element;
		$variables['this'] = $this;
		
		$field = $view->renderString($field, $variables);
		
		$view->setTemplateMode($templateMode);
		
		return $field;
    }
}
