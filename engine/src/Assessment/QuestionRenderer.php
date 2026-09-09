<?php

namespace PWB\Assessment;

class QuestionRenderer
{
    public function render(array $page, array $answers = [], array $errors = []): string
    {
        $html = "<section class='pwb-page' id='{$page['id']}'>";

        if (!empty($page['title'])) {
            $html .= "<h1>" . $this->e($page['title']) . "</h1>";
        }

        if (!empty($page['subtitle'])) {
            $html .= "<p class='subtitle'>" . $this->e($page['subtitle']) . "</p>";
        }

        foreach ($page['components'] ?? [] as $component) {
            $html .= $this->renderComponent($component, $answers, $errors);
        }

        $html .= "</section>";

        return $html;
    }

    private function renderComponent(array $component, array $answers, array $errors): string
    {
        return match ($component['type']) {

            'heading' =>
                "<h2>" . $this->e($component['text']) . "</h2>",

            'paragraph' =>
                "<p>" . $this->e($component['text']) . "</p>",

            'profile' =>
                $this->renderProfileField(
                    $component['field'],
                    $answers[$component['field']['id']] ?? null,
                    $errors[$component['field']['id']] ?? null
                ),

		'question' =>
		    $this->renderQuestion(
		        $component['question'],
		        $answers[$component['question']['id']] ?? null
    		),
            default => ''
        };
    }

    private function renderProfileField(?array $field, mixed $value = null, ?array $error = null): string
    {
        if (!$field) {
            return '';
        }

        $id          = $field['id'];
        $label       = $this->e($field['label']);
        $required    = !empty($field['required']) ? 'required' : '';
        $star        = !empty($field['required']) ? ' *' : '';
        $placeholder = $this->e($field['placeholder'] ?? '');

        $invalid = $error ? ' invalid' : '';

        $html = "<div class='field'>";
        $html .= "<label for='{$id}'>{$label}{$star}</label>";

        switch ($field['type']) {

            case 'text':
            case 'email':
            case 'tel':

                $val = $this->e((string)($value ?? ''));

                $html .= "<input class='{$invalid}' type='{$field['type']}' id='{$id}' name='{$id}' value='{$val}' placeholder='{$placeholder}' {$required}>";

                break;

            case 'year':

                $val = $this->e((string)($value ?? ''));

                $min = $field['min'] ?? 1900;
                $max = $field['max'] ?? date('Y');

                $html .= "<input class='{$invalid}' type='number' id='{$id}' name='{$id}' value='{$val}' min='{$min}' max='{$max}' {$required}>";

                break;

            case 'single_select':

                foreach ($field['options'] ?? [] as $option) {

                    $checked = ($value === $option['value']) ? 'checked' : '';

                    $html .= "
                    <label class='radio'>
                        <input type='radio'
                               name='{$id}'
                               value='{$this->e($option['value'])}'
                               {$checked}
                               {$required}>
                        {$this->e($option['label'])}
                    </label>";
                }

                break;

            case 'multi_select':

                $selected = is_array($value) ? $value : [];

                foreach ($field['options'] ?? [] as $option) {

                    $checked = in_array($option['value'], $selected, true)
                        ? 'checked'
                        : '';

                    $html .= "
                    <label class='checkbox'>
                        <input type='checkbox'
                               name='{$id}[]'
                               value='{$this->e($option['value'])}'
                               {$checked}>
                        {$this->e($option['label'])}
                    </label>";
                }

                if (!empty($field['helper'])) {
                    $html .= "<p class='helper'>" . $this->e($field['helper']) . "</p>";
                }

                break;

            case 'checkbox':

                $checked = ((string)$value === '1') ? 'checked' : '';

                $html .= "
                <label class='checkbox'>
                    <input type='checkbox'
                           id='{$id}'
                           name='{$id}'
                           value='1'
                           {$checked}
                           {$required}>
                    {$label}
                </label>";

                break;
        }

        if ($error) {
            $html .= "<div class='field-error'>{$this->e($error['message'])}</div>";
        }

        $html .= "</div>";

        return $html;
    }

/**
 * Render an assessment question.
 */
private function renderQuestion(?array $question, mixed $answer = null): string
{
    if (!$question) {
        return '';
    }

    $id    = $question['id'];
    $title = $this->e($question['question']);

    $html = "<div class='question'>";
    
    $visibleIf = $question['visible_if'] ?? null;

if ($visibleIf) {

$depends  = $visibleIf['question'];
$operator = $visibleIf['operator'] ?? 'equals';
$value    = htmlspecialchars((string)$visibleIf['value'], ENT_QUOTES);

$html .= "
    <div class='conditional'
         data-question='{$depends}'
         data-operator='{$operator}'
         data-value='{$value}'>";
}

    $html .= "<h3>{$title}</h3>";

    $labels = $question['answer_options'] ?? [];
    $values = $question['stored_values'] ?? [];

switch ($question['answer_type']) {

    case 'radio':
    case 'single_select':

        foreach ($labels as $index => $label) {

            $value = (string)($values[$index] ?? $label);
            $checked = ((string)$answer === $value) ? 'checked' : '';

            $html .= "
                <label class='radio'>
                    <input
                        type='radio'
                        name='{$id}'
                        value='{$this->e($value)}'
                        {$checked}>
                    {$this->e($label)}
                </label>";
        }

        break;

    case 'multi_select':

        $selected = is_array($answer)
            ? array_map('strval', $answer)
            : [];

        foreach ($labels as $index => $label) {

            $value = (string)($values[$index] ?? $label);
            $checked = in_array($value, $selected, true)
                ? 'checked'
                : '';

            $html .= "
                <label class='checkbox'>
                    <input
                        type='checkbox'
                        name='{$id}[]'
                        value='{$this->e($value)}'
                        {$checked}>
                    {$this->e($label)}
                </label>";
        }

        break;
        
case 'frequency_3':
case 'frequency_4':
case 'frequency_5':
case 'quality_5':

    // Find selected position from stored value
    $selectedIndex = 0;

    foreach ($values as $i => $stored) {
        if ((string)$stored === (string)$answer) {
            $selectedIndex = $i;
            break;
        }
    }

    $jsonValues = htmlspecialchars(
        json_encode($values),
        ENT_QUOTES,
        'UTF-8'
    );
    
    $jsonLabels = htmlspecialchars(
    json_encode($labels),
    ENT_QUOTES,
    'UTF-8'
	);

    $html .= "
        <div class='slider-question' data-values='{$jsonValues}' data-option-labels='{$jsonLabels}'>
            <div class='slider-current'>
                {$this->e($labels[$selectedIndex])}
            </div>

            <input
                class='likert-slider'
                type='range'
                min='0'
                max='" . (count($labels) - 1) . "'
                step='1'
                value='{$selectedIndex}'>

            <input
                type='hidden'
                name='{$id}'
                value='{$values[$selectedIndex]}'>

            <div class='slider-ends'>
                <span>{$this->e($labels[0])}</span>
                <span>{$this->e(end($labels))}</span>
            </div>
        </div>";

        break;
}

    $html .= "</div>";

if ($visibleIf) {
    $html .= "</div>";
}

    return $html;
}




    private function e(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}