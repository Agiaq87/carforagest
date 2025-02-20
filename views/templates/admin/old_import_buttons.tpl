
<div class="panel">
    <div class="panel-heading">
        <i class="icon-home"></i> {l s='Home' mod='carforagest'}
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-6">
                <form method="post" action="">
                    <div class="form-wrapper">
                        <div class="form-group">
                            <input type="hidden"
                                   name="mode"
                                   id="mode"
                                   value="null">
                            <input type="hidden"
                                   name="admin_url"
                                   id="admin_url"
                                   value="{$url}">
                            <input type="hidden"
                                   name="admin_token"
                                   id="admin_token"
                                   value="{$token}">
                            <button
                                    type="submit"
                                    name="submit_carforagest_csv"
                                    class="btn btn-default pull-right">
                                <i class="icon-file-text"></i> {l s='Importa da CSV'}
                            </button>
                            <button
                                    type="submit"
                                    name="submit_carforagest_db"
                                    class="btn btn-default pull-right">
                                <i class="icon-database"></i> {l s='Importa da DB'}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>