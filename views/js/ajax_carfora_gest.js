$(document).ready(function(){
    postUrl = document.getElementById('admin_url').value;
    token = document.getElementById('admin_token').value;
    nextButton = document.getElementById('next_step_button');
    returnButton = document.getElementById('dashboard_button');
    step = document.getElementById('step');

    progressText = document.getElementById('progress_text');
    count = 1;

    console.log('postUrl', postUrl, 'token', token, 'step', step, 'nextButton', nextButton, 'returnButton', returnButton, 'progressText', progressText, 'count', count)


    function updateProcess() {
        switch(count) {
            case 1: {
                progressText.innerHTML = "Importazione in corso...";
                count = 2;
                break;
            }
            case 2: {
                progressText.innerHTML = "Per favore resta in attesa e non chiudere la pagina....";
                count = 3;
                break;
            }
            case 3: {
                progressText.innerHTML = "Il processo potrebbe richiedere alcuni minuti...";
                count = 4;
                break;
            }
            case 4: {
                progressText.innerHTML = "nel frattempo puoi andare a prenderti un caffè";
                count = 1;
                break;
            }
        }
    }

    if (step.value === 'importer') {
        alert = document.getElementById('alert');
        alert.classList.add('hidden');
        progressText.classList.remove('hidden');
        nextButton.addEventListener('click', function(event){
            console.log("cliccato");
            returnButton.disabled = true;
            nextButton.disabled = true;
            setInterval(updateProcess, 5000);
        });
    }


})

