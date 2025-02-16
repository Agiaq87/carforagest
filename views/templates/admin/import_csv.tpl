<div class="panel">
    <div class="panel-heading">
        <i class="icon-upload"></i> {l s='Importa Marchi da CSV' mod='carforagest'}
    </div>
    <div class="panel-body">
        <form action="" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <input type="hidden"
                       name="mode"
                       id="mode"
                       value="csv">
                <input type="hidden"
                       name="admin_url"
                       id="admin_url"
                       value="{$url}">
                <input type="hidden"
                       name="admin_token"
                       id="admin_token"
                       value="{$token}">
                <label for="csv_file">{l s='Seleziona un file CSV' mod='carforagest'}</label>
                <input type="file" name="csv_file" id="csv_file" class="form-control">
            </div>
            <button type="submit" name="csv_upload" class="btn btn-success">
                <i class="icon-cloud-upload"></i> {l s='Carica CSV' mod='carforagest'}
            </button>
            <button type="submit" name="return" class="btn btn-default pull-right">
                <i class="icon-back"></i> {l s='Torna alla home' mod='carforagest'}
            </button>
        </form>
    </div>
</div>