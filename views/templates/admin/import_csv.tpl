<div class="panel">
    <div class="panel-heading">
        <i class="icon-info-circle"></i> {l s='Istruzioni per l\'importazione' mod='carforagest'}
    </div>
    <div class="panel-body">
        <div id="alert" class="alert alert-info">
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
                    {foreach from=$warningSteps item=step}
                        <li><strong>{l s=$step mod='carforagest'}</strong></li>
                    {/foreach}
                </ul>
                <li>{l s='IMPORTANTE: Il file non verrà elaborato se non rispetta esattamente questo numero e ordine di colonne.' mod='carforagest'}</li>
                <li>{l s='Usare come separatore di valori la virgola e il classico carriage return per indicare il fine riga' mod='carforagest'}</li>
                <li>{l s='Il file CSV deve essere salvato in formato UTF-8' mod='carforagest'}</li>
                <li>{l s='IMPORTANTE: deve essere presente l\'header nel csv' mod='carforagest'}</li>
            </ol>
        </div>
        <div id="message" class="alert alert-info hidden">
            <h4>{l s='Importazione in corso...' mod='carforagest'}</h4>
            <p id="progress_text"></p>
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-heading">
        <i class="icon-upload"></i>
        {l s=$panelHeading mod='carforagest'}
    </div>
    <div class="panel-body">
        <form id="form" method="post" enctype="multipart/form-data">
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
                <input type="hidden" id="import_step" name="import_step" value="{$step}">
                <label for="csv_file">{l s='Seleziona un file CSV' mod='carforagest'}</label>
                <input type="file" name="csv_file" id="csv_file" class="form-control">
                <p><strong>L'importazione potrebbe durare qualche minuto e partirà non appena premi il bottone Carica csv, nel frattempo prenditi un caffè...</strong></p>
            </div>
            <button type="submit" id="next_step_button" name="next_step_button" class="btn btn-success">
                <i class="icon-cloud-upload"></i> {l s='Carica CSV' mod='carforagest'}
            </button>
            <button type="submit" id="dashboard_button" name="dashboard_button" class="btn btn-default pull-right">
                <i class="icon-back"></i> {l s='Torna alla home' mod='carforagest'}
            </button>
        </form>
    </div>
</div>