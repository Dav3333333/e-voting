import { api } from "../libs/api.js";

class Scrutin{

    #container

    constructor(){
        this.#container = document.createElement("div")
    }

    async #getPolls(){
      const data = await api.get("poll"); 
    }

    formAddPoll(){
      const formContainer = document.createElement("div");

      formContainer.classList.add("election-form-section");
      formContainer.innerHTML = `<h2 class="text-title">Créer un nouveau scrutin</h2>`;

      const form = document.createElement("form"); 

      form.classList.add("election-form");

      form.innerHTML = `<input type="text" class="input" placeholder="Titre du scrutin" required />
                            <textarea class="textarea" placeholder="Description du scrutin" rows="3"></textarea>

                            <label class="label" for="start-date">Date de début</label>
                            <input type="date" id="start-date" class="input" required />

                            <label class="label" for="end-date">Date de fin</label>
                            <input type="date" id="end-date" class="input" required />

                            <button type="submit" class="btn btn-primary">Créer le scrutin</button>
                        `      

    }



    getPollList(){

      const containerList = document.createElement("div");

      containerList.classList.add("election-list-section");
      containerList.innerHTML = `<h2 class="text-title">Scrutins existants</h2>`;

      const ps = api.get("polls"); 
      ps.then((data)=>{
        console.log(data)
        data.message.forEach(poll => {
          containerList.insertAdjacentHTML("beforeend", this.renderPoll(poll))
        });
      });

      return containerList;

    }

    renderPoll(poll){

      const id = poll.id;
      const title = poll.title; 
      const description = poll.description;
      const dateStart = poll.dateStart.date;
      const dateEnd = poll.dateEnd.date; 
      const status = poll.status; 
      const posts = poll.posts

      console.log(posts.length);

      const model = `
          <div class="election-card" id=${id}>
            <h3 class="text-bold">${title}</h3>
            <p>Du ${dateStart.trim().split(" ")[0]} au ${dateEnd.trim().split(" ")[0]}</p>

            <div class="candidats">
              <p><strong>Postes :</strong></p>
              <ul>
                <li>Marie Lusa</li>
                <li>Joseph Mbala</li>
              </ul>
            </div>

            <div class="election-actions">
              <button class="btn btn-edit">Modifier</button>
              <button class="btn btn-delete">Supprimer</button>
              <button class="btn btn-success">Ajouter un candidat</button>
            </div>
          </div>`;

      return model;
      
    }

    render(){

      this.#container.innerHTML = "";
      this.#container.appendChild(this.getPollList()) 

      return this.#container;

    }    

}

export const scrutin = new Scrutin();
