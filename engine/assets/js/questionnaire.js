document.addEventListener('DOMContentLoaded', () => {

    const goals = document.querySelectorAll('input[name="PF014[]"]');

    if (!goals.length) return;

    function updateGoalLimit(){

        const selected = document.querySelectorAll(
            'input[name="PF014[]"]:checked'
        );

        goals.forEach(box => {

            if(selected.length >= 3 && !box.checked){
                box.disabled = true;
            }else{
                box.disabled = false;
            }

        });

    }

    goals.forEach(box => {
        box.addEventListener('change', updateGoalLimit);
    });

    updateGoalLimit();

});

document.querySelectorAll('.slider-question').forEach(question => {

    const slider = question.querySelector('.likert-slider');
    const hidden = question.querySelector('input[type=hidden]');
    const current = question.querySelector('.slider-current');

    const values = JSON.parse(question.dataset.values);

    const labels = Array.from(
        question.querySelectorAll('.slider-ends span')
    );

    const allLabels = JSON.parse(
        JSON.stringify(
            question.dataset.labels || []
        )
    );

    slider.addEventListener('input', () => {

        const index = Number(slider.value);

        hidden.value = values[index];

        // Update centre label from the original option labels
        const optionLabels = question.dataset.optionLabels
            ? JSON.parse(question.dataset.optionLabels)
            : [];

        if (optionLabels[index]) {
            current.textContent = optionLabels[index];
        }

    });

});

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
   Conditional question visibility
----------------------------------------*/

function updateConditionalQuestions() {

    document.querySelectorAll('.conditional').forEach(section => {

        const parentId = section.dataset.question;
        const selected = document.querySelector(
            `input[name="${parentId}"]:checked`
        );

const operator = section.dataset.operator;
const expected = section.dataset.value;

let visible = false;

if (operator === 'equals') {
    visible = selected && selected.value === expected;
}

        section.style.display = visible ? 'block' : 'none';

        // Disable hidden inputs so they are not submitted
        section.querySelectorAll('input').forEach(input => {
            input.disabled = !visible;
        });

    });

}

// Listen for every radio change
document.addEventListener('change', event => {

    if (event.target.matches('input[type=radio]')) {
        updateConditionalQuestions();
    }

});

// Initial page load
updateConditionalQuestions();