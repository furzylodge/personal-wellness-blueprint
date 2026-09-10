<?php

namespace PWB\Assessment;

class QuestionRenderer
{

public function render(array $page, array $answers = [], array $errors = []): string
{
    $pageId   = $this->e($page['id'] ?? '');
    $title    = $this->e($page['title'] ?? '');
    $subtitle = $this->e($page['subtitle'] ?? '');
    $icon     = $this->e($page['icon'] ?? 'circle');
    $minutes  = $page['estimated_minutes'] ?? '';
    $section = $page['section'] ?? '';
    $total   = $page['total_sections'] ?? 7;

    $html = "<section class='pwb-page' id='{$pageId}'>";

    switch ($page['type'] ?? '') {

case 'profile':

    $html .= "<div class='section-header'>";
    $html .= $this->renderProgressBar(
        (int)($page['section'] ?? 1),
        (int)($page['total_sections'] ?? 7)
    );

    $html .= "
        <div class='section-meta'>

            <div class='section-meta-left'>
                <span class='section-badge'>Section {$section} of {$total}</span>
                <span class='section-time'>{$minutes} min</span>
            </div>

            <div class='theme-picker'>
                <span class='theme-label'>Theme</span>

                <div class='theme-options'>
<button class='theme-dot green' data-theme='green' type='button'></button>
<button class='theme-dot ocean' data-theme='ocean' type='button'></button>
<button class='theme-dot lavender' data-theme='lavender' type='button'></button>
<button class='theme-dot amber' data-theme='amber' type='button'></button>
                </div>
            </div>

        </div>

        <div class='section-intro'>

            <div class='section-icon'>
                <div class='section-icon-inner icon-{$icon}'></div>
            </div>

            <div class='section-text'>
                <h1>{$title}</h1>
                <p class='section-subtitle'>{$subtitle}</p>
            </div>

        </div>

    </div>";
    break;

case 'questions':

    $html .= "<div class='section-header'>";
    $html .= $this->renderProgressBar(
        (int)($page['section'] ?? 1),
        (int)($page['total_sections'] ?? 7)
    );

    $html .= "
        <div class='section-meta'>
            <span class='section-badge'>Section {$section} of {$total}</span>
            <span class='section-time'>{$minutes} min</span>
        </div>

        <div class='section-intro'>

            <div class='section-icon'>
                <div class='section-icon-inner icon-{$icon}'></div>
            </div>

            <div class='section-text'>
                <h1>{$title}</h1>
                <p class='section-subtitle'>{$subtitle}</p>
            </div>

        </div>

    </div>";
    break;
case 'welcome':

    $html .= "
    <div class='welcome-card'>
    ";

    if ($title !== '') {
        $html .= "<h1>{$title}</h1>";
    }

    if ($subtitle !== '') {
        $html .= "<p class='subtitle'>{$subtitle}</p>";
    }
    $html .= "</div>";

    break;
        case 'finish':

            if ($title !== '') {
                $html .= "<h1>{$title}</h1>";
            }

            if ($subtitle !== '') {
                $html .= "<p class='subtitle'>{$subtitle}</p>";
            }
            break;
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

$visibleIf = $question['visible_if'] ?? null;

$html = '';

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

$exclusive = !empty($question['exclusive_none'])
    ? " data-exclusive-none='true'"
    : '';

$html .= "<div class='question'{$exclusive}>";

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
            $isNone = ($value === "0") ? " data-none-option='true'" : '';
            $checked = in_array($value, $selected, true)
                ? 'checked'
                : '';

            $html .= "
                <label class='checkbox'>
                    <input
                        type='checkbox'
                        name='{$id}[]'
                        value='{$this->e($value)}'
                        {$checked}
                        {$isNone}>
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

if (!empty($question['follow_up'])) {

    $fu = $question['follow_up'];

    $operator = $fu['show_if']['operator'];
    $value    = htmlspecialchars((string)$fu['show_if']['value'], ENT_QUOTES);

    $required = !empty($fu['required']) ? 'required' : '';

    $html .= "
        <div class='follow-up'
             data-parent='{$id}'
             data-operator='{$operator}'
             data-value='{$value}'>

            <label for='{$fu['id']}'>{$fu['label']}</label>

            <textarea
                id='{$fu['id']}'
                name='{$fu['id']}'
                rows='3'
                {$required}>"
                . htmlspecialchars((string)($answer[$fu['id']] ?? ''), ENT_QUOTES)
            . "</textarea>

        </div>";
}

$html .= "</div>"; // closes .question

if ($visibleIf) {
    $html .= "</div>"; // closes .conditional
}

    return $html;
}




    private function e(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
    
    private function renderProgressBar(int $current, int $total): string
{
    $html = "<div class='progress-bar'>";

    for ($i = 1; $i <= $total; $i++) {

        $class = $i === $current ? "active" : "inactive";

        $html .= "<span class='progress-segment {$class}'></span>";
    }

    $html .= "</div>";

    return $html;
}

}