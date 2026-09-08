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