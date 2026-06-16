import csv

def convertir_modele_csv(fichier_entree, fichier_sortie):
    """
    Convertit un fichier CSV d'étudiants selon le nouveau modèle demandé.
    Mappage :
    - Votre matricule -> matricule
    - Adresse e-mail -> rfid
    - Votre nom complet -> name
    - Adresse e-mail -> email
    - Votre faculte -> poll
    """
    try:
        with open(fichier_entree, mode='r', encoding='utf-8', newline='') as f_in:
            # Utilisation de DictReader pour manipuler les données par le nom des colonnes
            lecteur = csv.DictReader(f_in)
            
            # Définition des entêtes du nouveau modèle
            nouveaux_headers = ['matricule', 'rfid', 'name', 'email', 'poll']
            
            with open(fichier_sortie, mode='w', encoding='utf-8', newline='') as f_out:
                ecrivain = csv.DictWriter(f_out, fieldnames=nouveaux_headers)
                
                # Écriture de la nouvelle ligne d'entête (Header)
                ecrivain.writeheader()
                
                for ligne in lecteur:
                    # Extraction et nettoyage des données (nettoyage des espaces blancs superflus)
                    matricule = ligne.get('Votre matricule', '').strip()
                    email = ligne.get('Adresse e-mail', '').strip()
                    name = ligne.get('Votre nom complet', '').strip()
                    poll = ligne.get('Votre faculte', '').strip()
                    
                    # Reconstruction selon le nouveau modèle
                    nouvelle_ligne = {
                        'matricule': matricule,
                        'rfid': email,       # Le rfid prend la valeur de l'adresse mail
                        'name': name,
                        'email': email,      # Le mail reste l'adresse mail
                        'poll': poll         # poll prend la valeur de la faculte
                    }
                    
                    ecrivain.writerow(nouvelle_ligne)
                    
        print(f"Conversion réussie ! Le fichier a été généré sous le nom : '{fichier_sortie}'")
        
    except FileNotFoundError:
        print(f"Erreur : Le fichier source '{fichier_entree}' est introuvable.")
    except Exception as e:
        print(f"Une erreur est survenue : {e}")

# --- Zone d'exécution ---
if __name__ == "__main__":
    # Remplacez ces noms par vos vrais fichiers si nécessaire
    fichier_source = "student_data.csv"
    fichier_destination = "converted_student_file.csv"
    
    # Appel de la fonction
    convertir_modele_csv(fichier_source, fichier_destination)