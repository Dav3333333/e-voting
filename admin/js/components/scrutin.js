import { api } from "../libs/api.js";

class Scrutin{

    #container; 
    #dialog

    constructor(){
        this.#container = document.createElement("div"); 
        this.#dialog = document.querySelector("dialog");

        this.#handleClickEvents();
    }

    #handleClickEvents(){
      this.#container.addEventListener("click", (e)=>{

        const target = e.target;

        console.log(target)

        if(target.id == "election-add-poll"){
          this.#dialog.innerHTML = ""; 
          this.#dialog.appendChild(this.formAddPoll());
          this.#dialog.showModal();
        }

      });
    }

    formAddPoll(){
      const formContainer = document.createElement("div");

      formContainer.classList.add("election-form-section");
      formContainer.innerHTML = `<h2 class="text-title">Créer un nouveau scrutin</h2>`;

      const form = document.createElement("form"); 

      form.classList.add("election-form");

      form.innerHTML = `<input type="text" class="input" name="title" placeholder="Titre du scrutin" required />
                            <textarea class="textarea" name="description" placeholder="Description du scrutin" rows="3"></textarea>

                            <label class="label" for="start-date">Date de début</label>
                            <input type="datetime-local" name="date_start" id="start-date" class="input" required />

                            <label class="label" for="end-date">Date de fin</label>
                            <input type="datetime-local" name="date_end" id="end-date" class="input" required />

                            <button type="submit" class="btn btn-primary">Créer le scrutin</button>
                        `; 

      // submit listener

      form.addEventListener("submit", async(e)=>{
        e.preventDefault(); 
        const formData =new FormData(form);
        formData.append("user_id", 1);
        console.log(formData);
        
        const rep = api.post("poll", formData); 

        rep.then((data)=>{
          if(rep.message[1] == "done"){
            form.querySelector("textarea").value = ""; 
          }
        })

      });
                        
      formContainer.appendChild(form); 

      return formContainer;
    }



    getPollList(){

      const containerList = document.createElement("div");

      containerList.classList.add("election-list-section");
      // create a add scrutin button and the title element form the scrutin element
      containerList.innerHTML = `<div class="election-head"><h2 class="text-title">Scrutins</h2> <button id="election-add-poll" class="btn btn-success">NOUVEAU</button> </div>`;

      const ps = api.get("polls"); 
      ps.then((data)=>{
        data.message.forEach(poll => {
          containerList.insertAdjacentHTML("beforeend", this.renderPoll(poll))
        });
      });

      return containerList;

    }

    async renderPollDisplay(){

    }

    renderPoll(poll){

      const id = poll.id;
      const title = poll.title; 
      const description = poll.description;
      const dateStart = poll.dateStart.date;
      const dateEnd = poll.dateEnd.date; 
      const status = poll.status; 
      const posts = poll.posts


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
