<?php

namespace Evavel\Resources\Fields;

use Evavel\Http\Request\Request;

class Text extends Field
{
    public $component = 'text-field';

    public $suggestions;

    public function asHtml()
    {
        return $this->withMeta(['asHtml' => true]);
    }

    public function suggestions($suggestions)
    {
        $this->suggestions = $suggestions;
        return $this;
    }

    public function resolveSuggestions(Request $request)
    {
        if (is_callable($this->suggestions)) {
            $result = call_user_func($this->suggestions, $request);
            return $result ? $result : null;
        }

        return $this->suggestions;
    }

    public function toJsonSerialize()
    {
        $request = evavel_make('request');
        // @todo: depends of type of request

        return array_merge(parent::toJsonSerialize(),
            ['suggestions' => $this->resolveSuggestions($request)]
        );
    }
}
