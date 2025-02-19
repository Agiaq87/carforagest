$(document).ready(function(){
    mode = document.getElementById("mode");
    nextButton = document.getElementById("next-button");
    returnButton = document.getElementById('return-button');
    postUrl = document.getElementById('admin_url');
    token = document.getElementById('admin_token');

    progressBar = document.getElementById('import-progress');
    progressText = document.getElementById('progress-text');

    buttonAction = nextButton.getAttribute('name');


    console.log('mode', mode);
    console.log('postUrl', postUrl);
    console.log('postUrl.value', postUrl.value);
    console.log('token', token);
    console.log('token.value', token.value);
    console.log('returnButton', returnButton);
    console.log('nextButton', nextButton);

    console.log('progressBar', progressBar);
    console.log('progressText', progressText);

    console.log('action', buttonAction);

    const ajaxCall = async (action, data = {}) => {
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
            //console.log("JSON", jsonResponse);

            /*if (!jsonResponse.success) {
                console.error('Error:', jsonResponse.error);
            }*/

            return jsonResponse;
        } catch (error) {
            console.error('Error:', error);
            throw error;
        }
    };

    function updateProgress(percentage, message) {
        // Aggiorna la barra di progresso
        //const progressBar = document.getElementById('import-progress');
        //const progressText = document.getElementById('progress-text');

        progressBar.style.width = percentage + '%';
        progressBar.setAttribute('aria-valuenow', percentage);
        progressText.textContent = percentage + '%';

        // Aggiunge il messaggio al log
        const logArea = document.getElementById('import-log');
        const timestamp = new Date().toLocaleTimeString();
        logArea.value += `[${timestamp}] ${message}\n`;

        // Auto-scroll al fondo del textarea
        logArea.scrollTop = logArea.scrollHeight;
    }

    function startImport() {
        // Inizializza la progress bar e il log
        updateProgress(0, 'Avvio importazione...');

        // Funzione che controlla lo stato dell'importazione
        function checkProgress() {
            ajaxCall('ajax', {}).then(function(response) {
                try {
                    const data = JSON.parse(response);

                    if (data.status) {
                        // Aggiorna la progress bar
                        updateProgress(data.progress, data.message);

                        // Se l'importazione non è completata, continua a controllare
                        if (data.progress < 100) {
                            setTimeout(checkProgress, 1000); // Controlla ogni secondo
                        } else {
                            // Importazione completata
                            updateProgress(100, 'Importazione completata!');
                        }
                    } else {
                        // Gestione errori
                        updateProgress(0, 'Errore: ' + data.message);
                    }
                } catch (e) {
                    console.error('Errore nel parsing della risposta:', e);
                }
            }).catch(function(error) {
                console.error('Errore nella richiesta Ajax:', error);
                updateProgress(0, 'Errore di connessione');
            });
        }

        // Avvia il controllo del progresso
        checkProgress();
    }

    ajaxCall('ajaxCheck', {}).then(function(response) {
        console.log("AJAX RESPONSE: ", response);
    })


    $('#progressForm').on('submit', function(e){
        e.preventDefault();

        ajaxCall(nextButton.value, {}).then(function(response) {
            try {
                const data = JSON.parse(response);
                if (data.status) {
                    // File caricato con successo, avvia l'importazione
                    startImport();
                } else {
                    updateProgress(0, 'Errore: ' + data.message);
                }
            } catch (e) {
                console.error('Errore nel parsing della risposta:', e);
            }
        }).catch(function(error) {
            console.error('Errore nel caricamento del file:', error);
            updateProgress(0, 'Errore nel caricamento del file');
        });
    })

})

