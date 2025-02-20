<div class="panel">
    <div class="panel-heading">
        <i class="icon-home"></i> {l s='Dashboard Importazione' mod='carforagest'}
    </div>
    <div class="panel-body text-center">
        <button id="csv-button" class="btn btn-primary btn-lg"">
            <i class="icon-file-text"></i> {l s='Importa da CSV' mod='carforagest'}
        </button>
        <button id="db-button" class="btn btn-info btn-lg" onclick="toggleImportOptions('db')">
            <i class="icon-database"></i> {l s='Importa da DB' mod='carforagest'}
        </button>

        <div id="import-options" class="hidden mt-4">
            <h4>{l s='Seleziona cosa importare' mod='carforagest'}</h4>
            <form method="post">
                <input type="hidden" name="admin_url" value="{$url}">
                <input type="hidden" name="admin_token" value="{$token}">
                <input type="hidden" name="import_argument" value="{$selection[0]}">
                <input type="hidden" name="import_modality" value="">
                <input type="hidden" name="import_step" value="{$step}">

                <select id="chooser" name="import_type" class="form-control mb-3">
                    {foreach from=$selection item=select}
                        <option name="{$select}" value="{$select}">{l s={$select} mod='carforagest'}</option>
                    {/foreach}
                </select>

                <button id="next_step_button" name="next_step_button" type="submit" class="btn btn-success btn-block">
                    {l s='Conferma Importazione' mod='carforagest'}
                </button>
            </form>
        </div>
    </div>
</div>

<script>

</script>