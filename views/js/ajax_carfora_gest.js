$(document).ready(function(){
    postUrl = document.getElementById('admin_url').value;
    token = document.getElementById('admin_token').value;
    nextButton = document.getElementById('next_step_button');
    returnButton = document.getElementById('dashboard_button');
    step = document.getElementById('import_step');
    message = document.getElementById('message');
    form = document.getElementById('form');

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

    /*if (step.value === 'importer') {
        alert = document.getElementById('alert');
        nextButton.addEventListener('click', function(event){
            alert.classList.add('hidden');
            progressText.classList.remove('hidden');
            message.classList.remove('hidden');
            console.log("cliccato");
            returnButton.disabled = true;
            nextButton.disabled = true;
            ajaxCall('next_step_button');

            setInterval(updateProcess, 5000);
        });
    }*/

    /*const ajaxCall = async (action, data = {}) => {
        try {
            const response = await fetch(postUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    ajax: 1,
                    action: action,
                    token: token,
                    ...data
                })
            });

            const result = await response.text();
            //console.log("RESULT", result);
            let jsonResponse;
            try {
                jsonResponse = JSON.parse(result);
            } catch (e) {
                console.error('Failed to parse JSON:', response.text());
                throw e;
            }

            return jsonResponse;
        } catch (error) {
            console.error('Error:', error);
            throw error;
        }
    };*/
})

