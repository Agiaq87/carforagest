    /**
     * Classe di appoggio per le request
     */
    class PrestashopApi {
        constructor(shopUrl, apiKey) {
            this.shopUrl = shopUrl;
            this.apiKey = apiKey;
            this.baseUrl = `${shopUrl}/api`;
        }


    /**
     * Esegue una richiesta all'API di PrestaShop
     * @param {string} endpoint - L'endpoint da chiamare (es. 'products')
     * @param {string} method - Il metodo HTTP (GET, POST, PUT, DELETE)
     * @param {object} data - I dati da inviare (opzionale)
     * @returns {Promise<object>} - La risposta JSON
     */
    async request(endpoint, method = 'GET', data = null) {
        const url = `${this.baseUrl}/${endpoint}`;

        const headers = {
            'Authorization': `Basic ${btoa(`${this.apiKey}:`)}`,
            'Accept': 'application/json'
        };

        const options = {
            method,
            headers,
            mode: 'cors',
            credentials: 'include'
        };

        if (data && (method === 'POST' || method === 'PUT')) {
            headers['Content-Type'] = 'application/json';
            options.body = JSON.stringify(data);
        }

        try {
            const response = await fetch(url, options);

            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(`PrestaShop API Error (${response.status}): ${errorText}`);
            }

            return await response.json();
        } catch (error) {
            console.error('PrestaShop API request failed:', error);
            throw error;
        }
    }

    /**
     * Ottiene un elenco di risorse
     * @param {string} resource - Il tipo di risorsa (es. 'products')
     * @param {object} params - Parametri opzionali (es. filtri, limit, ecc.)
     * @returns {Promise<Array>} - Array di risorse
     */
    async getList(resource, params = {}) {
        // Costruire la query string dai parametri
        const queryString = Object.keys(params)
            .map(key => `${encodeURIComponent(key)}=${encodeURIComponent(params[key])}`)
            .join('&');

        const endpoint = `${resource}${queryString ? '?' + queryString : ''}`;
        return this.request(endpoint);
    }

    /**
     * Ottiene una singola risorsa per ID
     * @param {string} resource - Il tipo di risorsa (es. 'products')
     * @param {number} id - L'ID della risorsa
     * @returns {Promise<object>} - La risorsa richiesta
     */
    async get(resource, id) {
        return this.request(`${resource}/${id}`);
    }

    /**
     * Crea una nuova risorsa
     * @param {string} resource - Il tipo di risorsa (es. 'products')
     * @param {object} data - I dati per la nuova risorsa
     * @returns {Promise<object>} - La risorsa creata
     */
    async create(resource, data) {
        return this.request(resource, 'POST', data);
    }

    /**
     * Aggiorna una risorsa esistente
     * @param {string} resource - Il tipo di risorsa (es. 'products')
     * @param {number} id - L'ID della risorsa
     * @param {object} data - I dati aggiornati
     * @returns {Promise<object>} - La risorsa aggiornata
     */
    async update(resource, id, data) {
        return this.request(`${resource}/${id}`, 'PUT', data);
    }

    /**
     * Elimina una risorsa
     * @param {string} resource - Il tipo di risorsa (es. 'products')
     * @param {number} id - L'ID della risorsa
     * @returns {Promise<object>} - Risposta dell'eliminazione
     */
    async delete(resource, id) {
        return this.request(`${resource}/${id}`, 'DELETE');
    }

    // Metodi specifici per le risorse comuni

    /**
     * Ottiene l'elenco dei prodotti
     * @param {object} params - Parametri di filtraggio opzionali
     * @returns {Promise<Array>} - Array di prodotti
     */
    async getProducts(params = {}) {
        return this.getList('products', params);
    }

    /**
     * Ottiene un singolo prodotto per ID
     * @param {number} id - ID del prodotto
     * @returns {Promise<object>} - Dati del prodotto
     */
    async getProduct(id) {
        return this.get('products', id);
    }

    /**
     * Crea un nuovo prodotto
     * @param {object} productData - Dati del prodotto
     * @returns {Promise<object>} - Il prodotto creato
     */
    async createProduct(productData) {
        return this.create('products', productData);
    }

    /**
     * Aggiorna un prodotto esistente
     * @param {number} id - ID del prodotto
     * @param {object} productData - Dati aggiornati del prodotto
     * @returns {Promise<object>} - Il prodotto aggiornato
     */
    async updateProduct(id, productData) {
        return this.update('products', id, productData);
    }

    /**
     * Ottiene l'elenco dei clienti
     * @param {object} params - Parametri di filtraggio opzionali
     * @returns {Promise<Array>} - Array di clienti
     */
    async getCustomers(params = {}) {
        return this.getList('customers', params);
    }

    /**
     * Ottiene un singolo cliente per ID
     * @param {number} id - ID del cliente
     * @returns {Promise<object>} - Dati del cliente
     */
    async getCustomer(id) {
        return this.get('customers', id);
    }

    /**
     * Crea un nuovo cliente
     * @param {object} customerData - Dati del cliente
     * @returns {Promise<object>} - Il cliente creato
     */
    async createCustomer(customerData) {
        return this.create('customers', customerData);
    }

    /**
     * Ottiene l'elenco dei produttori
     * @param {object} params - Parametri di filtraggio opzionali
     * @returns {Promise<Array>} - Array di produttori
     */
    async getManufacturers(params = {}) {
        return this.getList('manufacturers', params);
    }

    /**
     * Ottiene un singolo produttore per ID
     * @param {number} id - ID del produttore
     * @returns {Promise<object>} - Dati del produttore
     */
    async getManufacturer(id) {
        return this.get('manufacturers', id);
    }

    /**
     * Crea un nuovo produttore
     * @param {object} manufacturerData - Dati del produttore
     * @returns {Promise<object>} - Il produttore creato
     */
    async createManufacturer(manufacturerData) {
        return this.create('manufacturers', manufacturerData);
    }
}

    async function loadProducts() {
        try {
            const productsContainer = document.getElementById('products-container');
            if (!productsContainer) return;

            productsContainer.innerHTML = '<p>Caricamento prodotti in corso...</p>';

            // Ottieni i prodotti con alcuni parametri di filtraggio
            const products = await api.getProducts({
                display: 'full',  // Ottieni tutti i dettagli
                limit: 10         // Limita a 10 prodotti
            });

            if (products && products.length > 0) {
                productsContainer.innerHTML = '';

                products.forEach(product => {
                    const productCard = document.createElement('div');
                    productCard.className = 'product-card';
                    productCard.innerHTML = `
            <h3>${product.name}</h3>
            <p>${product.description_short}</p>
            <p>Prezzo: ${product.price} €</p>
            <button class="edit-product" data-id="${product.id}">Modifica</button>
          `;
                    productsContainer.appendChild(productCard);
                });

                // Aggiungi event listener ai pulsanti di modifica
                document.querySelectorAll('.edit-product').forEach(button => {
                    button.addEventListener('click', function() {
                        const productId = this.getAttribute('data-id');
                        openEditForm(productId);
                    });
                });
            } else {
                productsContainer.innerHTML = '<p>Nessun prodotto trovato.</p>';
            }
        } catch (error) {
            console.error('Errore durante il caricamento dei prodotti:', error);
            document.getElementById('products-container').innerHTML =
                `<p class="error">Errore durante il caricamento dei prodotti: ${error.message}</p>`;
        }
    }

    // Esempio: Apri il form di modifica di un prodotto
    async function openEditForm(productId) {
        try {
            const product = await api.getProduct(productId);

            if (product) {
                const formContainer = document.getElementById('edit-form-container');
                formContainer.innerHTML = `
          <h2>Modifica Prodotto</h2>
          <form id="edit-product-form">
            <input type="hidden" name="product_id" value="${product.id}">
            
            <div class="form-group">
              <label for="product-name">Nome:</label>
              <input type="text" id="product-name" name="name" value="${product.name}" required>
            </div>
            
            <div class="form-group">
              <label for="product-price">Prezzo:</label>
              <input type="number" id="product-price" name="price" step="0.01" value="${product.price}" required>
            </div>
            
            <div class="form-group">
              <label for="product-quantity">Quantità:</label>
              <input type="number" id="product-quantity" name="quantity" value="${product.quantity}" required>
            </div>
            
            <div class="form-group">
              <label for="product-description">Descrizione:</label>
              <textarea id="product-description" name="description">${product.description}</textarea>
            </div>
            
            <button type="submit">Salva Modifiche</button>
            <button type="button" id="cancel-edit">Annulla</button>
          </form>
        `;

                // Mostra il form
                formContainer.style.display = 'block';

                // Gestisci il submit del form
                document.getElementById('edit-product-form').addEventListener('submit', function(e) {
                    e.preventDefault();
                    updateProduct(new FormData(this));
                });

                // Gestisci il pulsante Annulla
                document.getElementById('cancel-edit').addEventListener('click', function() {
                    formContainer.style.display = 'none';
                });
            }
        } catch (error) {
            console.error('Errore durante il caricamento del prodotto:', error);
            alert(`Errore: ${error.message}`);
        }
    }

    // Esempio: Aggiorna un prodotto
    async function updateProduct(formData) {
        try {
            const productId = formData.get('product_id');

            const productData = {
                name: formData.get('name'),
                price: formData.get('price'),
                quantity: formData.get('quantity'),
                description: formData.get('description')
            };

            // Esegui l'aggiornamento
            await api.updateProduct(productId, productData);

            // Chiudi il form e ricarica i prodotti
            document.getElementById('edit-form-container').style.display = 'none';
            loadProducts();

            // Mostra messaggio di successo
            alert('Prodotto aggiornato con successo!');
        } catch (error) {
            console.error('Errore durante l\'aggiornamento del prodotto:', error);
            alert(`Errore: ${error.message}`);
        }
    }

    // Esempio: Crea un nuovo prodotto
    document.getElementById('new-product-form')?.addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        try {
            const productData = {
                name: formData.get('name'),
                price: formData.get('price'),
                active: 1,
                id_category_default: formData.get('category_id'),
                description: formData.get('description'),
                description_short: formData.get('description_short')
            };

            await api.createProduct(productData);

            // Resetta il form
            this.reset();

            // Ricarica la lista dei prodotti
            loadProducts();

            // Mostra messaggio di successo
            alert('Nuovo prodotto creato con successo!');
        } catch (error) {
            console.error('Errore durante la creazione del prodotto:', error);
            alert(`Errore: ${error.message}`);
        }
    });

    // Carica i prodotti all'avvio
    if (document.getElementById('products-container')) {
        loadProducts();
    }

    $(document).ready(function() {

        const progressCircle = document.querySelector('.progress-spinner');
        const progressText = document.getElementById('progress_text');
        const progressPercentage = document.getElementById('progress_percentage');
        let importing = false;
        const circumference = 2 * Math.PI * 45; // 2πr
        progressCircle.style.strokeDasharray = circumference;
        progressCircle.style.strokeDashoffset = circumference;

        postUrl = document.getElementById('admin_url').value;
        token = document.getElementById('admin_token').value;
        nextButton = document.getElementById('next_step_button');
        returnButton = document.getElementById('dashboard_button');
        count = 1;
        console.log('postUrl', postUrl, 'token', token, 'step', step, 'nextButton', nextButton, 'returnButton', returnButton, 'progressText', progressText, 'count', count)

        const api = new PrestashopApi(postUrl, token);

        console.log("LEGGI QUI", jsonData);

        function updateProgress(percent, message) {
            const offset = circumference - (percent / 100 * circumference);
            progressCircle.style.strokeDashoffset = offset;
            progressPercentage.textContent = percent + '%';
            if (message) {
                progressText.textContent = message;
            }
        }

        $('#start_import').on('click', function(e) {
            e.preventDefault();
            if (importing) return;

            importing = true;
            $('.panel-body').addClass('importing');

            const formData = $('#progressForm').serialize();
            const adminUrl = $('#admin_url').val();
            const token = $('#admin_token').val();

            // Simulating the import process with status checks
            updateProgress(0, 'Inizializzazione...');

            // Start the import process

                /*$.ajax({
                    url: adminUrl + '&token=' + token + '&ajax=1&action=startImport',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            checkImportStatus();
                        } else {
                            updateProgress(0, 'Errore: ' + response.message);
                            importing = false;
                        }
                    },
                    error: function() {
                        updateProgress(0, 'Errore di connessione');
                        importing = false;
                    }
                });*/
                });

        function checkImportStatus() {
            $.ajax({
                url: $('#admin_url').val() + '&token=' + $('#admin_token').val() + '&ajax=1&action=checkStatus',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.completed) {
                        updateProgress(100, 'Completato!');
                        setTimeout(function() {
                            window.location.href = $('#admin_url').val() + '&token=' + $('#admin_token').val() + '&importComplete=1';
                        }, 1000);
                    } else {
                        updateProgress(response.progress, response.message || 'Importazione in corso...');

                        if (response.progress < 100) {
                            setTimeout(checkImportStatus, 2000);
                        }
                    }
                },
                error: function() {
                    setTimeout(checkImportStatus, 5000); // Try again after error
                }
            });
        }

        // Fallback for demo purposes - this simulates progress if no AJAX endpoints exist
        // Remove this section in production and use the real AJAX implementation above
        if (document.location.href.indexOf('demo') > -1) {
            $('#start_import').on('click', function(e) {
                e.preventDefault();
                simulateImport();
            });

            function simulateImport() {
                importing = true;
                $('.panel-body').addClass('importing');
                updateProgress(0, 'Inizializzazione...');

                let progress = 0;
                const messages = [
                    'Inizializzazione...',
                    'Connessione al server...',
                    'Lettura file CSV...',
                    'Elaborazione dati...',
                    'Aggiornamento database...',
                    'Finalizzazione...',
                    'Completato!'
                ];

                const interval = setInterval(function() {
                    progress += Math.floor(Math.random() * 5) + 1;
                    if (progress > 100) progress = 100;

                    var messageIndex = Math.floor(progress / (100/messages.length));
                    if (messageIndex >= messages.length) messageIndex = messages.length - 1;

                    updateProgress(progress, messages[messageIndex]);

                    if (progress === 100) {
                        clearInterval(interval);
                        setTimeout(function() {
                            alert('Importazione completata con successo!');
                            importing = false;
                        }, 1000);
                    }
                }, 500);
            }
        }

        $('#dashboard_button').on('click', function() {
            if (importing && !confirm('L\'importazione è in corso. Sei sicuro di voler tornare alla dashboard?')) {
                return false;
            }
        });
    });

