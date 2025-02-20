{* views/templates/admin/import_progress.tpl *}

<div class="panel">
    <h3><i class="icon-cogs"></i> {l s='Import CSV' mod='carforagest'}</h3>
    <div class="row">
        <div class="col-md-6">
        </div>
    </div>

    <div class="panel-body">
        <div class="progress-container mb-3">
            <div id="progress-bar" class="progress" style="height: 25px;">
                <div id="import-progress"
                     class="progress-bar progress-bar-striped progress-bar-animated"
                     role="progressbar"
                     style="width: 0%"
                     aria-valuenow="0"
                     aria-valuemin="0"
                     aria-valuemax="100">
                    <span id="progress-text">0%</span>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="import-log">{l s='Log Importazione' mod='carforagest'}</label>
            <textarea id="import-log"
                      class="form-control"
                      rows="10"
                      readonly
                      style="font-family: monospace; background-color: #f5f5f5;">
            </textarea>
            <form id="progressForm" method="post" action="">
                <div class="form-wrapper">
                    <div class="form-group">
                        <input type="hidden"
                               name="admin_url"
                               id="admin_url"
                               value="{$url}">
                        <input type="hidden"
                               name="admin_token"
                               id="admin_token"
                               value="{$token}"
                        >
                        <input type="hidden"
                               name="mode"
                               id="mode"
                               value="{$mode}">
                        <input type="hidden"
                               name="argument"
                               id="argument"
                               value="{$argument}">
                        <input type="hidden"
                               name="step"
                               id="step"
                               value="{$step}">
                        <button type="submit" id="start_import" name="start_import" class="btn btn-success">
                            <i class="icon-cloud-upload"></i> Avvia Importazione
                        </button>
                        <button type="submit" id="dashboard_button" name="dashboard_button" class="btn btn-default pull-right">
                            <i class="icon-back"></i> Torna alla dashboard
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
