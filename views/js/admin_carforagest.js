document.addEventListener('DOMContentLoaded', () => {

    // DASHBOARD
    const selectElement = document.getElementById('chooser');
    const modalitySelected = document.querySelector('input[name="import_modality"]'); // Indica se csv o DB
    const argumentSelected = document.querySelector('input[name="import_argument"]');  // Indica se marchi, prodotti, fornitori, legato alla select

    const csvButton = document.getElementById('csv-button');
    const dbButton = document.getElementById('db-button');

    csvButton.addEventListener('click', () => toggleImportOptions('csv'));
    dbButton.addEventListener('click', () => toggleImportOptions('db'));

    selectElement.addEventListener('change', function() {
        const selectedValue = this.value;
        argumentSelected.value = selectedValue;
        console.log("Valore selezionato:", selectedValue);
    });
    function toggleImportOptions(mode) {
        document.getElementById('import-options').classList.remove('hidden');
        modalitySelected.value = mode;
        console.log(modalitySelected, mode);
    }
    // DASHBOARD

    // CONFIGURAZIONE
    const setupInput = (input, isEnable) => input.forEach(element => {
        console.log(element);
        isEnable ? element.removeAttribute("disabled") : element.setAttribute("disabled", "");
        //element.enabled = isEnable;
        isEnable ? element.setAttribute("required", "") : element.removeAttribute("required");
    });

    console.log("loaded admin carforagest");
    let sshSwitchOn = document.querySelector('#carforagest_configuration_required_ssh_tunnel_on');
    let sshSwitchOff = document.querySelector('#carforagest_configuration_required_ssh_tunnel_off');
    let sshHost = document.querySelector("#carforagest_configuration_ssh_tunnel_host");
    let sshPort = document.querySelector("#carforagest_configuration_ssh_tunnel_port");
    let sshUser = document.querySelector("#carforagest_configuration_ssh_tunnel_user");
    let sshPass = document.querySelector("#carforagest_configuration_ssh_tunnel_pass");
    let initialValueSwitch = document.querySelector("#ssh_tunnel_configuration");
    let submitButton = document.querySelector("#configuration_form_submit_btn_1");

    let inputs = [sshHost, sshPort, sshUser, sshPass, submitButton];
    inputs.forEach(element => {console.log(element);})

    setupInput(inputs, initialValueSwitch.value !== 0);


    sshSwitchOn.addEventListener("click", () => {
        console.log("click switchOn");
        setupInput(inputs, true);
    });

    sshSwitchOff.addEventListener("click", () => {
        console.log("click switchOff");
        setupInput(inputs, false);
    })
    // CONFIGURAZIONE


    // IMPORT CSV
    function copyQuery() {
        const queryInput = document.getElementById('sql-query');
        queryInput.select();
        document.execCommand('copy');

        // Feedback visivo
        const btn = event.currentTarget;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="icon-check"></i> Copiato!';
        setTimeout(() => {
            btn.innerHTML = originalText;
        }, 2000);
    }
    // IMPORT CSV

});
