document.addEventListener('DOMContentLoaded', () => {

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
});