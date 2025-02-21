<div class="panel">
    <h3><i class="icon-cogs"></i> {l s='Import CSV' mod='carforagest'}</h3>
    <div class="row">
        <div class="col-md-6"></div>
    </div>

    <div class="panel-body text-center">
        <!-- Circular Progress Spinner -->
        <div class="progress-circle-container">
            <svg class="progress-circle" width="120" height="120" viewBox="0 0 100 100">
                <circle class="progress-background" cx="50" cy="50" r="45"></circle>
                <circle class="progress-spinner" cx="50" cy="50" r="45"></circle>
            </svg>
            <div class="progress-text">
                <span id="progress_text">Pronto</span>
            </div>
        </div>

        <form id="progressForm" method="post" action="">
            <div class="form-wrapper">
                <div class="form-group">
                    <input type="hidden" name="admin_url" id="admin_url" value="{$url}">
                    <input type="hidden" name="admin_token" id="admin_token" value="{$token}">
                    <input type="hidden" name="mode" id="mode" value="{$mode}">
                    <input type="hidden" name="argument" id="argument" value="{$argument}">
                    <input type="hidden" name="step" id="step" value="{$step}">

                    <button type="submit" id="start_import" class="btn btn-success">
                        <i class="icon-cloud-upload"></i> Avvia Importazione
                    </button>
                    <button type="submit" id="dashboard_button" class="btn btn-default pull-right">
                        <i class="icon-back"></i> Torna alla dashboard
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>