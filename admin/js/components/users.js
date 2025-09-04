import { api } from "../libs/api.js";

class Users{

    #container;

    constructor(){
        this.#container = document.createElement("div")
    }

    #handleClickEvent(){
        this.#container.addEventListener("click", (e)=>{
            console.log(e, "clicked")
        });
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
    <h2 class="text-center text-bold">Liste des utilisateurs</h2>
    
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

