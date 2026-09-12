<?php

namespace PWB\Assessment;

class QuestionRenderer
{

public function render(array $page, array $answers = [], array $errors = [],array $reviewData = []): string
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
    $html .= $this->renderComponent(
        $component,
        $answers,
        $errors,
        $reviewData
    );
}

    $html .= "</section>";

    return $html;
}

    private function renderComponent(
    array $component,
    array $answers,
    array $errors,
    array $reviewData = []
): string
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
        $answers,
        $errors
    ),
'review' => $this->renderReview(
    $reviewData['profile'] ?? [],
    $reviewData['answers'] ?? [],
    $reviewData
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
                        <input
    type='checkbox'
    name='<?= $id ?>[]'
    value='<?= $storedValue ?>'
    <?= $checked ? 'checked' : '' ?>
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
private function renderQuestion(
    ?array $question,
    array $answers = [],
    array $errors = []
): string
{
    if (!$question) {
        return '';
    }

$id     = $question['id'];
$title  = $this->e($question['question']);
$answer = $answers[$id] ?? null;

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
     data-value='{$this->e($visibleIf['value'])}'>";
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
            $isNone = ($value === "none") ? " data-none-option" : "";
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
$isUnset = ($answer === null || $answer === '');

$selectedIndex = 0;

if (!$isUnset) {
    foreach ($values as $i => $stored) {
        if ((string)$stored === (string)$answer) {
            $selectedIndex = $i;
            break;
        }
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

$html .= "<div class='slider-question' data-values='{$jsonValues}' data-option-labels='{$jsonLabels}'>";

$html .= "<div class='slider-current'>"
    . ($isUnset ? "Not set" : $this->e($labels[$selectedIndex]))
    . "</div>";

$html .= "
    <input
        class='likert-slider ".($isUnset ? "is-unset" : "")."'
        type='range'
        min='0'
        max='" . (count($labels) - 1) . "'
        step='1'
        value='{$selectedIndex}'>

    <input
        type='hidden'
        name='{$id}'
        value='" . ($isUnset ? "" : $values[$selectedIndex]) . "'>

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
    . htmlspecialchars((string)($answers[$fu['id']] ?? ''), ENT_QUOTES)
. "</textarea>";

if (isset($errors[$fu['id']])) {
    $html .= "<div class='field-error'>"
        . $this->e($errors[$fu['id']]['message'])
        . "</div>";
}

$html .= "</div>";
}

$html .= "</div>"; // closes .question

if ($visibleIf) {
    $html .= "</div>"; // closes .conditional
}

    return $html;
}

private function renderReview(
    array $profile,
    array $answers,
    array $reviewData
): string

{

$sections = [
    ['id'=>'profile',   'page'=>'PROFILE',   'title'=>'About You',            'icon'=>'user-round',  'colour'=>'green'],
    ['id'=>'energy',    'page'=>'ENERGY',    'title'=>'Energy & Foundations', 'icon'=>'zap',         'colour'=>'lime'],
    ['id'=>'nutrition', 'page'=>'NUTRITION', 'title'=>'Nutrition',            'icon'=>'leaf',        'colour'=>'gold'],
    ['id'=>'mind',      'page'=>'MIND',      'title'=>'Mind',                 'icon'=>'trees',       'colour'=>'teal'],
    ['id'=>'body',      'page'=>'BODY',      'title'=>'Body',                 'icon'=>'mountain',    'colour'=>'indigo'],
    ['id'=>'sleep',     'page'=>'SLEEP',     'title'=>'Sleep',                'icon'=>'moon',        'colour'=>'violet'],
    ['id'=>'lifestyle', 'page'=>'LIFESTYLE', 'title'=>'Lifestyle',            'icon'=>'tree',        'colour'=>'brown'],
    ['id'=>'medical',   'page'=>'MEDICAL',   'title'=>'Medical',              'icon'=>'heart-pulse', 'colour'=>'rose'],
];

    $html = "
    <div class='review-header'>
        <div class='review-success'>
            ✓
        </div>

        <div>
            <h2>Assessment Complete</h2>
            <p>Review your answers before generating your Personal Wellness Blueprint.</p>

            <div class='review-tip'>
                Expand a section to review or edit your answers.
            </div>
        </div>
    </div>

    <div class='review-list'>";

    foreach ($sections as $i => $section) {
    
    $editPage = match ($section['id']) {
    'profile'   => 'PROFILE',
    'energy'    => 'ENERGY',
    'nutrition' => 'NUTRITION',
    'mind'      => 'MIND',
    'body'      => 'BODY',
    'sleep'     => 'SLEEP',
    'lifestyle' => 'LIFESTYLE',
    'medical'   => 'MEDICAL',
    default     => 'PROFILE',
};

        $open = $i === 0 ? " open" : "";        
        
$summary = $this->calculateSectionProgress(
    $section['page'],
    $reviewData['pages'],
    $answers,
    $reviewData['profile']
);

$countText = "{$summary['answered']} / {$summary['total']}";
$countClass = $summary['status'];
        
        $reviewContent = '';

if ($section['id'] === 'profile') {
    foreach ($reviewData['pages'] as $page) {
        if (($page['id'] ?? '') === 'PROFILE') {
            $reviewContent = $this->renderPageAnswers($page, $answers);
            break;
        }
    }
}

$pageId = $page['id'];

$missing = max(0, $summary['total'] - $summary['answered']);

$statusBadge = $missing === 0
    ? "<span class='review-status review-status-complete'>Complete</span>"
    : "<span class='review-status review-status-missing'>{$missing} missing</span>";

$html .= "
<div class='review-section{$open}' data-review='{$section['id']}'>

    <button type='button' class='review-summary'>

        <div class='review-left'>

            {$this->renderSectionIcon($section['icon'], $section['colour'])}

            <div>
                <h3>{$section['title']}</h3>

                <div class='review-tip'>
                    <i class='icon-chevrons-down'></i>
                    <span>Expand a section to review or edit your answers.</span>
                </div>
            </div>

        </div>

<div class='review-right'>
    ".$statusBadge."
    <span class='review-count {$countClass}'>{$countText}</span>
<span class='review-chevron'>▾</span>
</div>

    </button>

    <div class='review-content'>
        {$reviewContent}
        <a class='review-edit' href='?page={$editPage}'>Edit this section</a>
    </div>

</div>";

    }

    $html .= "
    </div>

    <button class='next' type='button'>
        Generate My Wellness Blueprint
    </button>";

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

private function renderPageAnswers(array $page, array $answers): string
{
    $html = '';

    foreach ($page['components'] ?? [] as $component) {

        if (($component['type'] ?? '') !== 'profile') {
            continue;
        }

        $field = $component['field'] ?? [];
        $id    = $field['id'] ?? '';

        if ($id === '' || !isset($answers[$id])) {
            continue;
        }

        $label = $field['label'] ?? '';
        $type  = $field['type'] ?? '';
        $value = $answers[$id];

        // Checkbox
        if ($type === 'checkbox') {
            $value = ((string)$value === '1') ? 'Yes' : 'No';
        }

        // Multi-select
        elseif ($type === 'multi_select') {

            $labels = [];

            foreach ($field['options'] ?? [] as $option) {
                if (in_array($option['value'], (array)$value, true)) {
                    $labels[] = $option['label'];
                }
            }

            $value = implode(', ', $labels);
        }

        // Single select
        elseif ($type === 'single_select') {

            foreach ($field['options'] ?? [] as $option) {
                if (($option['value'] ?? '') === $value) {
                    $value = $option['label'];
                    break;
                }
            }
        }

$optionalBadge = empty($field['required'])
    ? "<span class='review-optional'>Optional</span>"
    : "";

$displayValue = trim((string)$value) !== ''
    ? $this->e((string)$value)
    : "<em>Not provided</em>";

$html .= "
<div class='review-answer'>
    <div class='review-answer-label'>
        <strong>{$this->e($label)}</strong>
        {$optionalBadge}
    </div>
    <span>{$displayValue}</span>
</div>";

    }

    return $html;
}

private function renderSectionIcon(string $icon, string $colour): string
{
    $svg = match ($icon) {

       'user-round' => '
<circle cx="24" cy="16" r="6"/>
<path d="M12 36c2-7 22-7 24 0"/>',

        'zap' => '
        <path d="M26 10L15 26h8l-1 12 11-16h-8z"/>',

        'leaf' => '
        <path d="M16 30c4-8 12-12 16-10-2 8-8 14-16 10z"/>
        <path d="M20 28c2 0 6-4 8-8" stroke="white" stroke-width="1.5" fill="none"/>',

        'trees' => '
        <circle cx="24" cy="16" r="5"/>
        <circle cx="17" cy="23" r="4"/>
        <circle cx="31" cy="23" r="4"/>
        <circle cx="21" cy="30" r="4"/>
        <circle cx="28" cy="30" r="4"/>
        <rect x="23" y="14" width="2" height="18"/>',

        'mountain' => '
        <path d="M12 32L24 14l12 18z"/>',

        'moon' => '
        <path d="M29 12a11 11 0 1 0 7 20A13 13 0 1 1 29 12z"/>',

        'tree' => '
        <circle cx="24" cy="14" r="5"/>
        <circle cx="17" cy="20" r="5"/>
        <circle cx="31" cy="20" r="5"/>
        <circle cx="20" cy="28" r="5"/>
        <circle cx="28" cy="28" r="5"/>
        <rect x="23" y="18" width="2" height="16"/>',

        'heart-pulse' => '
        <path d="M24 34s-9-5-9-12a5 5 0 0 1 9-3 5 5 0 0 1 9 3c0 7-9 12-9 12z"/>
        <path d="M20 22h8M24 18v8" stroke="white" stroke-width="1.5" fill="none"/>',

        default => '
        <circle cx="24" cy="24" r="8"/>'
    };

    return "
    <div class='review-icon review-{$colour}'>
        <svg viewBox='0 0 48 48' class='review-icon-svg'>
            <g fill='currentColor'>
                {$svg}
            </g>
        </svg>
    </div>";
}

private function isQuestionVisible(array $question, array $answers): bool
{
    if (empty($question['visible_if'])) {
        return true;
    }

    $rule = $question['visible_if'];
    $answer = $answers[$rule['question']] ?? null;

    switch ($rule['operator']) {

        case 'equals':
            return (string)$answer === (string)$rule['value'];

        case 'contains':
            return is_array($answer) && in_array($rule['value'], $answer);
        case 'not_contains':
            return is_array($answer) && !in_array($rule['value'], $answer, true);

        default:
            return true;
    }
}

private function calculateSectionProgress(
    string $pageId,
    array $pages,
    array $answers,
    array $profile
): array
{

    $page = null;

    foreach ($pages as $candidate) {
        if (($candidate['id'] ?? '') === $pageId) {
            $page = $candidate;
            break;
        }
    }

    if (!$page) {
        return [
            'answered' => 0,
            'total'    => 0,
            'status'   => 'missing'
        ];
    }

    $answered = 0;
    $total = 0;

foreach ($page['components'] ?? [] as $component) {

    $type = $component['type'] ?? '';

    if (!in_array($type, ['question', 'profile'], true)) {
        continue;
    }

if ($type === 'profile') {

    $field = $component['field'] ?? [];
    $lookup = $field['key'] ?? null;
    $required = $field['required'] ?? false;

    if (!$lookup) {
        continue;
    }

    $value = $profile[$lookup] ?? '';


} else {

    $question = $component['question'] ?? [];

    $lookup   = $question['id'] ?? null;
    $required = $question['required'] ?? false;
    if (!$this->isQuestionVisible($question, $answers)) {
    continue;
}

    if (!$lookup) {
        continue;
    }

    $value = $answers[$lookup] ?? null;
}

    if (!$lookup) {
        continue;
    }

if ($required) {

    $total++;

    if (is_array($value)) {
        if (count(array_filter($value, fn($v) => $v !== '' && $v !== null)) > 0) {
            $answered++;
        }
    } elseif (is_bool($value)) {
        if ($value) {
            $answered++;
        }
    } elseif (trim((string)$value) !== '') {
        $answered++;
    }

}


}

    $ratio = $total > 0 ? ($answered / $total) : 0;

    $status = $ratio >= 1
        ? 'complete'
        : ($ratio >= 0.75 ? 'partial' : 'missing');

    return [
        'answered' => $answered,
        'total'    => $total,
        'status'   => $status
    ];
}

// PUT CLASSES etc ABOVE HERE

}