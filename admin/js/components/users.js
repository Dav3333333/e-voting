
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

    render(){
        const model = `<!-- users list -->
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
        <tbody>
          <tr>
            <td>Jean Kabila</td>
            <td>jean@vote.com</td>
            <td>Utilisateur</td>
            <td>Actif</td>
            <td>
              <button class="btn-modify">Modifier</button>
              <button class="btn-delete">Supprimer</button>
              <button class="btn-admin">Définir comme Admin</button>
            </td>
          </tr>
          <tr>
            <td>Amina Ndala</td>
            <td>amina@vote.com</td>
            <td>Admin</td>
            <td>Actif</td>
            <td>
              <button class="btn-modify">Modifier</button>
              <button class="btn-delete">Supprimer</button>
            </td>
          </tr>
          <!-- D'autres utilisateurs... -->
        </tbody>
      </table>
    </div>
`;
        this.#container.innerHTML = model;
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

