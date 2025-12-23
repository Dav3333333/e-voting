import { api } from "./api.js";

class PdfPrint {
    constructor() {}
    
    async imprimerPDF(idPoll) {
        let loadingIndicator = null;
        
        try {
            // Afficher un indicateur de chargement
            loadingIndicator = this.createLoadingIndicator();
            document.body.appendChild(loadingIndicator);

            // Utiliser directement l'API pour l'impression
            await api.printPollPDF(`poll/${idPoll}/cards/pdf`);
            
        } catch (error) {
            console.error('Erreur impression PDF:', error);
            this.showError(error.message || 'Erreur lors de l\'impression');
        } finally {
            // Retirer l'indicateur de chargement de façon sécurisée
            this.removeLoadingIndicatorSafe(loadingIndicator);
        }
    }

    // Alternative si vous voulez garder le contrôle dans cette classe
    async imprimerPDFManuel(idPoll) {
        let loadingIndicator = null;
        
        try {
            // Afficher un indicateur de chargement
            loadingIndicator = this.createLoadingIndicator();
            document.body.appendChild(loadingIndicator);

            const blob = await api.getBlob(`poll/${idPoll}/cards/pdf`);
            const url = window.URL.createObjectURL(blob);
            
            await this.printWithIframe(url);
            
        } catch (error) {
            console.error('Erreur impression PDF:', error);
            this.showError(error.message || 'Erreur lors de l\'impression');
        } finally {
            this.removeLoadingIndicatorSafe(loadingIndicator);
        }
    }
    
    // Méthode d'impression via iframe CORRIGÉE
    printWithIframe(url) {
        return new Promise((resolve, reject) => {
            const iframe = document.createElement('iframe');
            iframe.style.display = 'none';
            iframe.src = url; // ICI: utilisation de l'URL passée en paramètre
            document.body.appendChild(iframe);

            let printAttempted = false;

            const cleanup = () => {
                if (!printAttempted) return;
                if (document.body.contains(iframe)) {
                    document.body.removeChild(iframe);
                }
                window.URL.revokeObjectURL(url);
            };

            iframe.onload = () => {
                try {
                    iframe.contentWindow.focus();
                    
                    // Attendre que le PDF soit complètement chargé
                    setTimeout(() => {
                        iframe.contentWindow.print();
                        printAttempted = true;
                        resolve();
                        
                        // Nettoyage après impression
                        setTimeout(cleanup, 1000);
                    }, 500);
                    
                } catch (error) {
                    printAttempted = true;
                    cleanup();
                    reject(error);
                }
            };

            iframe.onerror = () => {
                if (!printAttempted) {
                    printAttempted = true;
                    cleanup();
                    reject(new Error('Erreur de chargement du PDF'));
                }
            };

            // Timeout de sécurité après 30 secondes
            setTimeout(() => {
                if (!printAttempted) {
                    printAttempted = true;
                    cleanup();
                    reject(new Error('Timeout de chargement du PDF'));
                }
            }, 30000);
        });
    }
    
    // Méthodes utilitaires AMÉLIORÉES
    createLoadingIndicator() {
        const loader = document.createElement('div');
        loader.setAttribute('data-pdf-loader', 'true'); // Ajout d'un attribut pour identification
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
                <div style="margin-bottom: 10px; font-weight: bold;">Génération du PDF en cours...</div>
                <div style="color: #666; margin-top: 10px;">Veuillez patienter</div>
            </div>
        `;
        return loader;
    }
    
    // Version SÉCURISÉE de removeLoadingIndicator
    removeLoadingIndicatorSafe(loadingIndicator) {
        // Méthode 1: Supprimer l'indicateur spécifique passé en paramètre
        if (loadingIndicator && document.body.contains(loadingIndicator)) {
            try {
                document.body.removeChild(loadingIndicator);
            } catch (error) {
                console.warn('Erreur lors de la suppression du loader:', error);
                // Fallback: cacher l'élément
                loadingIndicator.style.display = 'none';
            }
        }
        
        // Méthode 2: Nettoyer tous les loaders existants (sécurité)
        this.cleanupAllLoaders();
    }
    
    // Nettoyage de tous les loaders
    cleanupAllLoaders() {
        const loaders = document.querySelectorAll('[data-pdf-loader="true"]');
        loaders.forEach(loader => {
            if (document.body.contains(loader)) {
                try {
                    document.body.removeChild(loader);
                } catch (error) {
                    loader.style.display = 'none';
                }
            }
        });
    }
    
    showError(message) {
        // Vous pouvez personnaliser ceci selon votre système de notification
        alert(`Erreur: ${message}`);
    }
    
    // Alternative: Téléchargement direct
    async telechargerPDF(idPoll) {
        let loadingIndicator = null;
        
        try {
            loadingIndicator = this.createLoadingIndicator();
            document.body.appendChild(loadingIndicator);

            const blob = await api.getBlob(`poll/${idPoll}/cards/pdf`);
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            
            a.href = url;
            a.download = `scrutin-${idPoll}-cartes.pdf`;
            document.body.appendChild(a);
            a.click();
            
            // Nettoyage
            setTimeout(() => {
                if (document.body.contains(a)) {
                    document.body.removeChild(a);
                }
                window.URL.revokeObjectURL(url);
            }, 1000);
            
        } catch (error) {
            console.error('Erreur téléchargement PDF:', error);
            this.showError(error.message);
        } finally {
            this.removeLoadingIndicatorSafe(loadingIndicator);
        }
    }

    // Méthode de diagnostic pour résoudre le problème "PDF vide"
    async debugPDF(idPoll) {
        try {
            console.log('=== DÉBUGAGE PDF ===');
            
            // Test direct avec fetch pour voir la réponse
            const response = await fetch(`../api/poll/${idPoll}/cards/pdf`);
            console.log('Status:', response.status);
            console.log('OK:', response.ok);
            
            const headers = {};
            response.headers.forEach((value, key) => {
                headers[key] = value;
            });
            console.log('Headers:', headers);
            
            // Lire le contenu comme texte d'abord
            const text = await response.text();
            console.log('Taille réponse:', text.length);
            console.log('Début réponse:', text.substring(0, 200));
            
            // Vérifier si c'est une erreur JSON
            if (text.startsWith('{') || text.startsWith('[')) {
                try {
                    const errorData = JSON.parse(text);
                    console.log('Erreur JSON:', errorData);
                    return { error: errorData.error || 'Erreur inconnue du serveur' };
                } catch (e) {
                    console.log('Pas du JSON');
                }
            }
            
            // Vérifier si c'est un PDF (doit commencer par "%PDF")
            if (text.startsWith('%PDF')) {
                console.log('✓ Format PDF détecté');
                const blob = new Blob([text], { type: 'application/pdf' });
                return { success: true, blob };
            } else {
                console.log('✗ Format PDF non détecté');
                return { error: 'Le serveur n\'a pas retourné un PDF valide' };
            }
            
        } catch (error) {
            console.error('Erreur débugage:', error);
            return { error: error.message };
        }
    }
}

export const pdfPrint = new PdfPrint();