import { api } from "../libs/api.js";
import { modal_ops } from "./modal_ops.js";

class Users{

    #container;
    #dialog;

    constructor(){
        this.#container = document.createElement("div");
        this.#dialog = document.querySelector("dialog");
        this.#handleClickEvent()
    }

    async #openDialog(content){
      this.#dialog.innerHTML = ""; 
      await this.#dialog.appendChild(content);
      this.#dialog.showModal();
    }

    async #handleUploadFile(filePicker){
      filePicker.click(); 
      filePicker.addEventListener("change", async (e)=>{
        const file = filePicker.files[0]; 
  
        const formData = new FormData(); 
        formData.append('csvfile', file);

  
        await api.post('users/create/from-csv', formData).then(async (data)=>{
          console.log(data.status);
          console.log(data);
          if(data.status == "success") modal_ops.showSuccesMessage("Upload de Utilisateur", "Utilisateur creer avec success");
          if(data.status != "success") modal_ops.showFailMessage("Upload Utilisateur", "Upload de fichier utilisateur non abouti");
          
          // render again the user list
          await this.render();
        }).catch((err)=>{
          modal_ops.showWarning("Une erreur s'est produite", `${err}`);
          console.log(err)
        });
      });
    }


    #handleClickEvent(){
        this.#container.addEventListener("click", (e)=>{
            const target = e.target; 

            // create user function open dialog
            if(target.id == "create-user"){
              this.#openDialog(this.#formCreateUser());
            }

            // uploarde csv file to the server
            if(target.id == "upload-users-csvfile"){
              const filePicker = target.closest('div').querySelector("#export-csv-file-users");
              this.#handleUploadFile(filePicker);
            }
        });
    }

    #formCreateUser(){
      const formContainer = document.createElement("div");

      formContainer.classList.add("election-form-section");
      formContainer.innerHTML = `<h2 class="text-title">Créer un nouveau Utilisateur</h2>`;

      const form = document.createElement("form");
      form.classList.add("election-form");
      form.enctype = "multipart/form-data";
      form.innerHTML = `
                  <input type="text" class="input" name="name" placeholder="Noms complet" required />
                  <input type="text" class="input" name="email" placeholder="Email" required />
                  <input type="text" class="input" name="matricule" placeholder="Entrer le matricule" required />
                  <input type="text" class="input" name="rfid" placeholder="Entrer le rfid" />

                  <input type="file" id="fileInput" accept=".jpeg,.png,.jpg" required/>

                  <button type="submit" class="btn btn-primary">Créer l'utilisateur</button>

                  <div class="error-container"> 
                    <span class="text-error hidden" id="error-message"></span>
                  </div>
                  <div class="error-success"> 
                    <span class="text-success hidden" id="succes-message"></span>
                  </div>`
                  ;
      form.addEventListener("submit", async (e)=>{
        e.preventDefault();
        const formData = new FormData(form); 
        api.post("signup", formData).then(async (data)=>{
          const error = form.querySelector("#error-message");
          const success = form.querySelector("#succes-message");

          if(data.status == "success"){
            // changing class of error if displayed
            error.classList.add("hidden");

            // create a file
            const fileInput = form.querySelector("#fileInput");
            const file = fileInput.files[0];

            
            console.log(file, fileInput)
            const fileFormData = new FormData();

            fileFormData.append("userid",data.message.id);
            fileFormData.append("image", file)
            await api.post("user/image/upload", fileFormData).then((data)=>{
              console.log(data);
            });

            success.innerText = `Utilisateur cree avec Email: ${data.message.email} et Nom: ${data.message.name}`
            success.classList.remove("hidden");
            await this.render();
          }else{
            success.classList.add("hidden");

            error.innerText = data.message;
            error.classList.remove("hidden");
          }
        });
      });

      formContainer.appendChild(form);

      return formContainer;
    }

    async rendertableUser(){
      const tbody = document.createElement("tbody");

      const data = await api.get("users/all");

      data.message.forEach(user => {

        const model = `<tr id=${user.id}>
            <td>${user.name}</td>
            <td>${user.email}</td>
            <td>Utilisateur</td>
            <td>${user.status}</td>
            <td>
              <button class="btn-modify">Modifier</button>
              <button class="btn-delete">Supprimer</button>
              <button class="btn-admin">Définir comme Admin</button>
            </td>
          </tr>`;
        
        tbody.insertAdjacentHTML("beforeend", model);
        
      });

      return tbody;

    }

    async render(){
        const users = await this.rendertableUser();
        let model = `<!-- users list -->
    
    <div class="user-container">
      <h2 class="text-center text-bold">Liste des utilisateurs</h2>
      <div>
        <button class="add-btn" id="create-user">Creer Utilisateur</button>
        <button class="upload-file-btn" id="upload-users-csvfile">Uploader Fichier utilisateurs</button>
        <input type="file" id="export-csv-file-users" style="display:none" accept=".csv"/> 
      </div>
    </div>
    
    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Nom</th>
            <th>Email</th>
            <th>Rôle</th>
            <th>Statut</th>
            <th>Actions</th>
          </tr>
        </thead>
      </table>
    </div>
`;
        this.#container.innerHTML = model;


        this.#container.querySelector("table").appendChild(users);

        return this.#container;
    }
}

export const users = new Users();


// <!-- users list -->  html model



//     <h2 class="text-center text-bold">Liste des utilisateurs</h2>
    
//     <div class="table-container">
//       <table>
//         <thead>
//           <tr>
//             <th>Nom</th>
//             <th>Email</th>
//             <th>Rôle</th>
//             <th>Statut</th>
//             <th>Actions</th>
//           </tr>
//         </thead>
//         <tbody>
//           <tr>
//             <td>Jean Kabila</td>
//             <td>jean@vote.com</td>
//             <td>Utilisateur</td>
//             <td>Actif</td>
//             <td>
//               <button class="btn-modify">Modifier</button>
//               <button class="btn-delete">Supprimer</button>
//               <button class="btn-admin">Définir comme Admin</button>
//             </td>
//           </tr>
//           <tr>
//             <td>Amina Ndala</td>
//             <td>amina@vote.com</td>
//             <td>Admin</td>
//             <td>Actif</td>
//             <td>
//               <button class="btn-modify">Modifier</button>
//               <button class="btn-delete">Supprimer</button>
//             </td>
//           </tr>
//           <!-- D'autres utilisateurs... -->
//         </tbody>
//       </table>
//     </div>

