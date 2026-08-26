import csv

def convertir_modele_csv(fichier_entree, fichier_sortie, remplacements_poll=None):
    """
    Convertit un fichier CSV d'étudiants vers le nouveau modèle avec possibilité
    de remplacer des noms de facultés à la volée.
    
    :param fichier_entree: Chemin du fichier CSV d'origine
    :param fichier_sortie: Chemin du fichier CSV à générer
    :param remplacements_poll: Dictionnaire contenant les correspondances {ancien_nom: nouveau_nom}
    """
    # Si aucun dictionnaire n'est fourni, on initialise un dictionnaire vide
    if remplacements_poll is None:
        remplacements_poll = {}

    try:
        with open(fichier_entree, mode='r', encoding='utf-8', newline='') as f_in:
            lecteur = csv.DictReader(f_in)
            
            # Définition des entêtes du nouveau modèle
            nouveaux_headers = ['matricule', 'rfid', 'name', 'email', 'poll']
            
            with open(fichier_sortie, mode='w', encoding='utf-8', newline='') as f_out:
                ecrivain = csv.DictWriter(f_out, fieldnames=nouveaux_headers)
                ecrivain.writeheader()
                
                for ligne in lecteur:
                    # Extraction et nettoyage des données
                    matricule = ligne.get('Votre matricule', '').strip()
                    email = ligne.get('Adresse e-mail', '').strip()
                    name = ligne.get('Votre nom complet', '').strip()
                    poll_origine = ligne.get('Votre faculte', '').strip()
                    
                    # LOGIQUE DE REMPLACEMENT : 
                    # On cherche si la faculté d'origine est présente dans nos règles de remplacement.
                    # Si elle y est, on prend la nouvelle valeur, sinon on garde l'ancienne.
                    poll_modifie = remplacements_poll.get(poll_origine, poll_origine)
                    
                    # Reconstruction selon le nouveau modèle
                    nouvelle_ligne = {
                        'matricule': matricule,
                        'rfid': email,       
                        'name': name,
                        'email': email,      
                        'poll': poll_modifie  # On utilise le nom éventuellement remplacé
                    }
                    
                    ecrivain.writerow(nouvelle_ligne)
                    
        print(f"Conversion réussie ! Le fichier a été généré sous le nom : '{fichier_sortie}'")
        
    except FileNotFoundError:
        print(f"Erreur : Le fichier source '{fichier_entree}' est introuvable.")
    except Exception as e:
        print(f"Une erreur est survenue : {e}")

# --- Zone d'exécution et configuration des paramètres ---
if __name__ == "__main__":
    fichier_source = "student_data.csv"
    fichier_destination = "converted_student_file.csv"
    
    # Définissez ici vos règles de remplacement pour la colonne 'poll'
    # Format -> "Ancien Nom Exact dans le CSV": "Nouveau Nom Souhaité"
    dictionnaire_remplacements = {
        "Faculte de communication":"PRESIDENCE", 
        "Faculte de Leadership ou education":"PRESIDENCE", 
        "Faculte de Théologie":"PRESIDENCE", 
        "Faculté des sciences juridiques":"PRESIDENCE"
    }
    
    # Appel de la fonction en passant le dictionnaire en paramètre
    convertir_modele_csv(fichier_source, fichier_destination, remplacements_poll=dictionnaire_remplacements)




