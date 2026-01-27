# -*- coding: utf-8 -*-
"""
Created on Fri Jan 23 12:41:44 2026

@author: dls
"""

def get_file_content(path):
    with open(f"{path}", "r") as f:
        return f.read().split("\n")


def get_enroled_users_l0(form_users_path_file, local_users_path_file ):
    forms_data = {}
    
    for i in [ [ v for v in a.split(',') if len(v.strip()) != 0] for a in get_file_content(form_users_path_file) if len(a.strip()) != 0 ]:
        if i[4].strip().lower() == "l0": 
            forms_data[i[3].strip("0")] = i
    
    "data from the local files"
    local_user_data = {} 
    
    for i in [ a.split(',') for a in get_file_content(local_users_path_file) if len(a.strip()) != 0 ]: 
        current = forms_data.get(i[1])
        if not current is None: 
            current.append(i[0])
            current.append(i[1])
            local_user_data[i[1]] = current
    
    return local_user_data
    
    
def get_enroled_users_legend(form_users_path_file, local_users_path_file):
    forms_data = {}
    
    for i in [ [ v for v in a.split(',') if len(v.strip()) != 0] for a in get_file_content(form_users_path_file) if len(a.strip()) != 0 ]:
        if i[4].strip().lower() != "l0": 
            forms_data[i[3].strip("0")] = i
    
    "data from the local files"
    local_user_data = {} 
    for i in [[ v.strip('"') for v in a.split(',')] for a in get_file_content(local_users_path_file) if len(a.strip()) != 0 ]: 
        if i[-2].lower() == 'staff':
            continue
        
        current = forms_data.get(i[0])
        
        if not current is None: 
            current.append(f'{i[2]} {i[3]} {i[4]}')
            current.append(i[1])
            
            local_user_data[i[0]] = current
        
    return local_user_data


def get_all_enroled(form_users_path_file, local_users_path_file_L0, local_users_path_file_legend): 
    return {**get_enroled_users_l0(form_users_path_file, local_users_path_file_L0), 
            **get_enroled_users_legend(form_users_path_file, local_users_path_file_legend)}


def convert_dict_to_list(data_dict : dict):
    'matricule,nom_complet,email,rfid,faculte_poll'
    data_list = ["name,matricule,email,rfid,poll"]
    for matricule,data_user in data_dict.items():
        data_list.append(f"{data_user[2]},{matricule},{data_user[1]},{data_user[5]}")
        
    return data_list

def write_csv_from_double_list(content: list): 
    with open("generated_users_1.csv", "w") as file: 
        file.write("\n".join(content))
    
    return content


test_methode = get_all_enroled("test_file_internet.csv", "generated_users.csv", "etudiant.csv")

data_list_test = convert_dict_to_list(test_methode)

write_csv_from_double_list(data_list_test)
    
    


