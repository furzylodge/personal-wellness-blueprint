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

            } else if (operator === 'contains') {

                const checked = document.querySelectorAll(
                    `input[name="${parentId}[]"]:checked`
                );

                visible = Array.from(checked).some(
                    input => input.value === expected
                );
            }

            section.classList.toggle('visible', visible);
        });
    }

    document.addEventListener('change', event => {

        if (
            event.target.matches('input[type=radio]') ||
            event.target.matches('input[type=checkbox]')
        ) {
            updateConditionalQuestions();
            updateFollowUps();
        }
    });

    updateConditionalQuestions();
    updateFollowUps();
});

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
            noneBox.disabled = true;
        }
    }
}

document.querySelectorAll('.question[data-exclusive-none]')
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

}

// =====================================================
// Theme picker
// =====================================================

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