<?php

declare(strict_types=1);

namespace PWB\Reports\Components;

class MechanismPopover
{
    public static function render(array $mechanism, string $groupName): string
    {
        $name = htmlspecialchars($mechanism['name'] ?? '');

        $summary = htmlspecialchars(
            $mechanism['plainEnglish'] ?? ''
        );

        $why = htmlspecialchars(
            $mechanism['whyItMatters'] ?? ''
        );

        return '
        <details class="mechanism-popover" name="'.$groupName.'">

            <summary class="mechanism-chip">
                '.$name.'
                <span class="info-icon">ⓘ</span>
            </summary>

            <div class="mechanism-card">

                <h5>'.$name.'</h5>

                <div class="mechanism-summary">
                    '.$summary.'
                </div>

                <hr class="mechanism-divider">

                <div class="mechanism-label">
                    WHY THIS PATHWAY MATTERS
                </div>

                <p>'.$why.'</p>

            </div>

        </details>';
    }
}