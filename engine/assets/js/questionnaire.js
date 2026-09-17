document.addEventListener('DOMContentLoaded', () => {

    /* ---------------------------------------
       Wellness goal limit (PAGE002)
    ---------------------------------------- */

    const goals = document.querySelectorAll('input[name="PF014[]"]');

    function updateGoalLimit() {

        if (!goals.length) return;

        const selected = document.querySelectorAll(
            'input[name="PF014[]"]:checked'
        );

        goals.forEach(box => {
            box.disabled = selected.length >= 3 && !box.checked;
        });
    }

    goals.forEach(box => {
        box.addEventListener('change', updateGoalLimit);
    });

    updateGoalLimit();


    /* ---------------------------------------
       Likert sliders
    ---------------------------------------- */

    document.querySelectorAll('.slider-question').forEach(question => {

        const slider  = question.querySelector('.likert-slider');
        const hidden  = question.querySelector('input[type=hidden]');
        const current = question.querySelector('.slider-current');

        const values = JSON.parse(question.dataset.values);
        const labels = JSON.parse(question.dataset.optionLabels);

        slider.addEventListener('input', () => {

            const index = Number(slider.value);

            hidden.value = values[index];
            current.textContent = labels[index];

            slider.classList.remove('is-unset');
            current.classList.remove('is-unset');
            question.classList.remove('has-error');

            const existingError = question.querySelector('.field-error');
            if (existingError) existingError.remove();
        });
    });


    /* ---------------------------------------
       Clear error state on radio / checkbox
       questions as soon as they're answered
    ---------------------------------------- */

    document.querySelectorAll('.question.has-error').forEach(question => {

        const inputs = question.querySelectorAll(
            'input[type=radio], input[type=checkbox]'
        );

        inputs.forEach(input => {

            input.addEventListener('change', () => {

                question.classList.remove('has-error');

                const existingError = question.querySelector('.field-error');
                if (existingError) existingError.remove();
            });
        });
    });


    /* ---------------------------------------
       Conditional questions
    ---------------------------------------- */
function updateConditionalQuestions() {

    document.querySelectorAll('.conditional').forEach(section => {

        const parentId = section.dataset.question;
        const operator = section.dataset.operator;
        const expected = section.dataset.value;

        let visible = false;

        if (operator === 'equals') {

            const selected = document.querySelector(
                `input[name="${parentId}"]:checked`
            );

            visible = selected && selected.value === expected;

        } else {

            const checked = document.querySelectorAll(
                `input[name="${parentId}[]"]:checked`
            );

            const values = Array.from(checked).map(input => input.value);

            if (operator === 'contains') {
                visible = values.includes(expected);
            }

            if (operator === 'not_contains') {
                visible = values.length > 0 && !values.includes(expected);
            }
        }

        section.classList.toggle('visible', visible);
    });
}


function updateFollowUps() {

    document.querySelectorAll('.follow-up').forEach(section => {

        const parent   = section.dataset.parent;
        const operator = section.dataset.operator;
        const expected = section.dataset.value;

        let visible = false;

        if (operator === 'equals') {

            const selected = document.querySelector(
                `input[name="${parent}"]:checked`
            );

            visible = selected && selected.value === expected;
        }

        section.classList.toggle('visible', visible);
    });
}

/* ---------------------------------------
   Exclusive "None of these" groups
---------------------------------------- */

function updateExclusiveGroup(question) {

    const boxes = question.querySelectorAll('input[type=checkbox]');
    const noneBox = question.querySelector('input[data-none-option]');

    if (!noneBox) return;

    boxes.forEach(box => box.disabled = false);

    if (noneBox.checked) {

        boxes.forEach(box => {
            if (box !== noneBox) {
                box.checked = false;
                box.disabled = true;
            }
        });

    } else {

        const anyPositive = Array.from(boxes).some(
            box => box !== noneBox && box.checked
        );

        if (anyPositive) {
    noneBox.checked = false;
    noneBox.disabled = true;
}
    }
}

document.querySelectorAll('[data-exclusive-none]')
    .forEach(question => {

        question.querySelectorAll('input[type=checkbox]')
            .forEach(box => {

                box.addEventListener('change', () => {
                    updateExclusiveGroup(question);
                    updateConditionalQuestions();
                });

            });

        updateExclusiveGroup(question);
    });

updateConditionalQuestions();
updateFollowUps();

// Update conditional questions immediately when radio buttons change
document.querySelectorAll('input[type=radio]').forEach(radio => {
    radio.addEventListener('change', () => {
        updateConditionalQuestions();
        updateFollowUps();
    });
});


// =====================================================
// Theme picker
// =====================================================

document.querySelectorAll('.theme-dot').forEach(dot => {

    dot.addEventListener('click', async () => {

        const theme = dot.dataset.theme;

        await fetch('questionnaire.php', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({
                action: 'set_theme',
                theme: theme
            })
        });

        document.body.className =
            document.body.className.replace(/theme-\w+/g, '');

        document.body.classList.add(`theme-${theme}`);

    });

});

function handleExclusiveNone(changed) {

    const group = document.querySelectorAll(
        `input[name="${changed.name}"]`
    );

    if (changed.dataset.none === "1" && changed.checked) {
        group.forEach(cb => {
            if (cb !== changed) cb.checked = false;
        });
        return;
    }

    if (changed.dataset.none !== "1" && changed.checked) {
        group.forEach(cb => {
            if (cb.dataset.none === "1") cb.checked = false;
        });
    }
}


// ==========================================
// Review accordion
// ==========================================

document.querySelectorAll('.review-summary').forEach(button => {

    button.addEventListener('click', () => {

        const section = button.parentElement;

        document.querySelectorAll('.review-section').forEach(card => {
            if (card !== section) {
                card.classList.remove('open');
            }
        });

        section.classList.toggle('open');

    });

});


// ==========================================
// Required-slider submit validation
// ==========================================

document.querySelectorAll('form').forEach(form => {

    form.addEventListener('submit', event => {

        let firstInvalid = null;

        form.querySelectorAll('.slider-question[data-required="1"]').forEach(question => {

            const slider = question.querySelector('.likert-slider');

            if (!slider || !slider.classList.contains('is-unset')) {
                return;
            }

            question.classList.add('has-error');

            let message = question.querySelector('.field-error');

            if (!message) {
                message = document.createElement('div');
                message.className = 'field-error';
                message.textContent = 'Please answer this question before continuing.';
                question.appendChild(message);
            }

            if (!firstInvalid) {
                firstInvalid = question;
            }
        });

        if (firstInvalid) {
            event.preventDefault();
            firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
});

});
