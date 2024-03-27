<?php

namespace lewisjenkins\craftdefaulttext\fields;

use Craft;
use craft\base\ElementInterface;
use craft\fields\PlainText;
use craft\helpers\StringHelper;

class DefaultTextPlainText extends PlainText
{
    public static function displayName(): string
    {
        return Craft::t('craft-default-text', parent::displayName() . ' (with default value)');
    }

    public $defaultValue;
    public $revertToDefault = false;

    public function getSettingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate('craft-default-text/_components/fields/DefaultTextPlainText_settings',
            [
                'field' => $this,
            ]);
    }

    public function normalizeValue(mixed $value, ?ElementInterface $element): mixed
    {
        return $this->_normalizeValueInternal($value, $element, false);
    }

    public function normalizeValueFromRequest(mixed $value, ?ElementInterface $element): mixed
    {
        return $this->_normalizeValueInternal($value, $element, true);
    }

    private function _normalizeValueInternal(mixed $value, ?ElementInterface $element, bool $fromRequest): mixed
    {

        if ($value === null) {
            if ($this->defaultValue !== null && $this->isFresh($element)) {
                $value = $this->getRenderedValue($this->defaultValue);
            }
        }
        
        if ($value !== null) {
            if (!$fromRequest) {
                $value = StringHelper::unescapeShortcodes(StringHelper::shortcodesToEmoji($value));
            }

            $value = trim(preg_replace('/\R/u', "\n", $value));
        }

        return $value !== '' ? $value : null;
    }

    public function serializeValue(mixed $value, ?ElementInterface $element): mixed
    {
        if ($value === null and $this->revertToDefault) {
            $value = $this->getRenderedValue($this->defaultValue);
        }
        
        if ($value !== null && !Craft::$app->getDb()->getSupportsMb4()) {
            $value = StringHelper::emojiToShortcodes(StringHelper::escapeShortcodes($value));
        }
        return $value;
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
