class Api {
    #baseUrl;

    constructor() {
        this.#baseUrl = "../api/";
    }

    /**
     * @param {string} endpoint 
     * @returns {Promise<any>} return a response from the api
     */
    async get(endpoint) {
        try {
            const response = await fetch(`${this.#baseUrl}${endpoint}`);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('API GET Error:', error);
            return { message: 'fail', error: error.message };
        }
    }

    /**
     * @param {string} endpoint 
     * @returns {Promise<Blob>} return a blob response from the api
     */
    async getBlob(endpoint) {
        try {
            const response = await fetch(`${this.#baseUrl}${endpoint}`);
            
            console.log('Response status:', response.status);
            console.log('Response ok:', response.ok);
            
            if (!response.ok) {
                // Essayer de lire comme texte d'abord pour voir l'erreur
                const errorText = await response.text();
                console.log('Error response text:', errorText);
                
                let errorMessage = `Erreur ${response.status}`;
                try {
                    const errorData = JSON.parse(errorText);
                    errorMessage = errorData.error || errorData.message || errorMessage;
                } catch (e) {
                    // Ce n'est pas du JSON, utiliser le texte brut
                    if (errorText && errorText.length < 100) {
                        errorMessage = errorText;
                    }
                }
                throw new Error(errorMessage);
            }

            const contentType = response.headers.get('content-type');
            console.log('Content-Type:', contentType);
            
            // Vérifier si c'est un PDF
            if (contentType && !contentType.includes('application/pdf')) {
                const text = await response.text();
                console.log('Non-PDF response:', text.substring(0, 200));
                throw new Error('Le serveur n\'a pas retourné un PDF');
            }

            const blob = await response.blob();
            console.log('Blob size:', blob.size, 'type:', blob.type);
            
            if (blob.size === 0) {
                throw new Error('Le fichier PDF est vide');
            }

            return blob;
            
        } catch (error) {
            console.error('API GET Blob Error:', error);
            throw error;
        }
    }

    /**
     * Print PDF from endpoint
     * @param {string} fileEndpoint 
     */
    async printPollPDF(fileEndpoint) {
        let loadingIndicator = null;
        
        try {
            loadingIndicator = this.createLoadingIndicator();
            document.body.appendChild(loadingIndicator);

            console.log('Tentative de récupération PDF:', fileEndpoint);
            const blob = await this.getBlob(fileEndpoint);
            console.log('PDF récupéré avec succès, taille:', blob.size);

            const url = window.URL.createObjectURL(blob);
            await this.printWithIframe(url);
            
        } catch (error) {
            console.error('Print PDF Error:', error);
            this.showError(error.message);
            throw error; // Propager l'erreur
        } finally {
            this.removeLoadingIndicatorSafe(loadingIndicator);
        }
    }

    /**
     * Improved PDF printing with iframe
     * @param {string} url 
     * @returns {Promise<void>}
     */
    async printWithIframe(url) {
        return new Promise((resolve, reject) => {
            const iframe = document.createElement("iframe");
            iframe.style.display = "none";
            iframe.src = url;
            document.body.appendChild(iframe);

            let printAttempted = false;

            const cleanup = () => {
                if (document.body.contains(iframe)) {
                    document.body.removeChild(iframe);
                }
                window.URL.revokeObjectURL(url);
            };

            const attemptPrint = () => {
                if (printAttempted) return;
                printAttempted = true;
                
                try {
                    iframe.contentWindow.focus();
                    iframe.contentWindow.print();
                    resolve();
                } catch (error) {
                    reject(new Error('Impossible d\'imprimer: ' + error.message));
                } finally {
                    // Nettoyage après un délai
                    setTimeout(cleanup, 1000);
                }
            };

            iframe.onload = () => {
                setTimeout(attemptPrint, 500);
            };

            iframe.onerror = () => {
                if (!printAttempted) {
                    printAttempted = true;
                    cleanup();
                    reject(new Error('Erreur de chargement du PDF'));
                }
            };

            // Timeout de sécurité
            setTimeout(() => {
                if (!printAttempted) {
                    printAttempted = true;
                    cleanup();
                    reject(new Error('Timeout: Le PDF a mis trop de temps à charger'));
                }
            }, 30000);
        });
    }

    /**
     * Create loading indicator
     * @returns {HTMLElement}
     */
    createLoadingIndicator() {
        const loader = document.createElement('div');
        loader.setAttribute('data-pdf-loader', 'true');
        loader.innerHTML = `
            <div style="
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: white;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                z-index: 10000;
                text-align: center;
                border: 1px solid #ccc;
                min-width: 200px;
            ">
                <div style="margin-bottom: 10px; font-weight: bold;">Génération du PDF</div>
                <div style="color: #666;">Veuillez patienter...</div>
            </div>
        `;
        return loader;
    }

    /**
     * Safe removal of loading indicator
     * @param {HTMLElement} loadingIndicator 
     */
    removeLoadingIndicatorSafe(loadingIndicator) {
        if (loadingIndicator && document.body.contains(loadingIndicator)) {
            try {
                document.body.removeChild(loadingIndicator);
            } catch (error) {
                console.warn('Error removing loader:', error);
                loadingIndicator.style.display = 'none';
            }
        }
        
        // Nettoyer tous les loaders existants au cas où
        this.cleanupExistingLoaders();
    }

    /**
     * Cleanup any existing loaders
     */
    cleanupExistingLoaders() {
        const existingLoaders = document.querySelectorAll('[data-pdf-loader="true"]');
        existingLoaders.forEach(loader => {
            if (document.body.contains(loader)) {
                try {
                    document.body.removeChild(loader);
                } catch (error) {
                    loader.style.display = 'none';
                }
            }
        });
    }

    /**
     * Show error message to user
     * @param {string} message 
     */
    showError(message) {
        // Vous pouvez remplacer par votre système de notification préféré
        alert(`Erreur: ${message}`);
    }

    /**
     * Post data to endpoint
     * @param {string} endpoint 
     * @param {FormData|Object} postData 
     * @returns {Promise<any>}
     */
    async post(endpoint, postData) {
        try {
            const options = {
                method: "POST",
            };

            // Gérer FormData vs JSON
            if (postData instanceof FormData) {
                options.body = postData;
            } else {
                options.body = JSON.stringify(postData);
                options.headers = {
                    'Content-Type': 'application/json'
                };
            }

            const response = await fetch(`${this.#baseUrl}${endpoint}`, options);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            return data;
        } catch (error) {
            console.error('API POST Error:', error);
            return { fail: true, errormessage: error.message ,error : error };
        }
    }

    /**
     * delete endpoint 
     */
    async delete(endpoint){
        try {
            const options = {
                method : "delete"
            }

            const response = await fetch(`${this.#baseUrl}${endpoint}`, options); 

            if(!response.ok){
                throw new Error(`Http error while executing call api ${response.status}`);
            }

            const data = await response.json(); 
            return data;
        } catch (error) {
            return {status:"fail", error:error.message}
        }   
    }

    /**
     * Convert PHP date string to JS date string
     * @param {string} dateString 
     * @returns {string} formatted date string
     */
    datePhpToDateJs(dateString) {
        try {
            const date = new Date(dateString);
            
            if (isNaN(date.getTime())) {
                throw new Error('Date invalide');
            }

            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');

            return `${year}-${month}-${day}T${hours}:${minutes}`;
        } catch (error) {
            console.error('Date conversion error:', error);
            return dateString; // Retourner la chaîne originale en cas d'erreur
        }
    }
}

export const api = new Api();