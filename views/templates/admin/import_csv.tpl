<div class="panel">
    <div class="panel-heading">
        <i class="icon-info-circle"></i> {l s='Istruzioni per l\'importazione' mod='carforagest'}
    </div>
    <div class="panel-body">
        <div class="alert alert-info">
            <h4>{l s='Come preparare il file CSV:' mod='carforagest'}</h4>
            <ol>
                <li>
                    <p>{l s='Usa la seguente query SQL per estrarre i dati:' mod='carforagest'}</p>
                    <div class="form-group">
                        <div class="input-group">
                            <input type="text"
                                   id="sql-query"
                                   class="form-control"
                                   value="{$sql}"
                                   readonly>
                            <span class="input-group-btn">
                                <button class="btn btn-default" onclick="copyQuery()">
                                    <i class="icon-copy"></i> {l s='Copia Query' mod='carforagest'}
                                </button>
                            </span>
                        </div>
                    </div>
                </li>
                <li>{l s='Assicurati che le colonne nel CSV siano nell\'ordine corretto:' mod='carforagest'}
                    <ul>
                        <li><strong>1.</strong> name (nome del marchio)</li>
                        <li><strong>2.</strong> enabled (stato attivo: 0 o 1)</li>
                        <li><strong>3.</strong> description (descrizione)</li>
                        <li><strong>4.</strong> meta_title (titolo meta)</li>
                        <li><strong>5.</strong> meta_keyword (parole chiave meta)</li>
                    </ul>
                </li>
                <li>{l s='IMPORTANTE: Il file non verrà elaborato se non rispetta esattamente questo numero e ordine di colonne.' mod='carforagest'}</li>
                <li>{l s='Usare come separatore di valori la virgola e il classico carriage return per indicare il fine riga' mod='carforagest'}</li>
                <li>{l s='Il file CSV deve essere salvato in formato UTF-8' mod='carforagest'}</li>
                <li>{l s='IMPORTANTE: deve essere presente l\'header nel csv' mod='carforagest'}</li>
            </ol>
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-heading">
        <i class="icon-upload"></i> {l s='Importa Marchi da CSV' mod='carforagest'}
    </div>
    <div class="panel-body">
        <form method="post" enctype="multipart/form-data">
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
                <input type="hidden" name="import_argument" value="{$argument}">
                <input type="hidden" name="import_modality" value="{$mode}">
                <input type="hidden" name="import_step" value="{$step}">
                <label for="csv_file">{l s='Seleziona un file CSV' mod='carforagest'}</label>
                <input type="file" name="csv_file" id="csv_file" class="form-control">
            </div>
            <button type="submit" name="next_step_button" class="btn btn-success">
                <i class="icon-cloud-upload"></i> {l s='Carica CSV' mod='carforagest'}
            </button>
            <button type="submit" name="dashboard_button" class="btn btn-default pull-right">
                <i class="icon-back"></i> {l s='Torna alla home' mod='carforagest'}
            </button>
        </form>
    </div>
</div>