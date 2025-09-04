import { api } from "../libs/api.js";
import { see } from "./seeStructin.js";
import { seeForm } from "./seeStructinForms.js";
import { func } from "./commonFunc.js";

class Scrutin{

    #container; 
    #dialog

    constructor(){
        this.#container = document.createElement("div"); 
        this.#dialog = document.querySelector("dialog");

        this.#handleClickEvents();
    }

    async #openDialog(content){
      this.#dialog.innerHTML = ""; 
      await this.#dialog.appendChild(content);
      this.#dialog.showModal();
    }

    /**
     * send the request to change the state of the candidate with the idCand id
     * @param {*} idCand 
     * @param {*} state 
     */
    async setCandidateState(idCand,idPost, state, callback){
      if (!idCand || !state && !func.isNumber(idCand)) return;

      const formData = new FormData();
      formData.append("idCand", idCand);
      formData.append("state", state);
      formData.append("idPost", idPost);
      await api.post("candidate/state/change", formData).then((data)=>{
        console.log("done",data);
        callback(data);
      });

    }

    async #handleClickEvents(){
      this.#container.addEventListener("click", async (e)=>{

        const target = e.target;

        console.log(target)

        if(target.id == "election-add-poll"){
          this.#openDialog(this.formAddPoll());
        }

        // see the structin section
        if(target.classList.contains("see")){
          const idPost = target.closest(".election-card").id
          this.#container.innerHTML = await see.render(idPost);
        }

        if(target.id == "refresh-see-pool"){
            const idPoll = target.closest(".scrutin-container").id;
            this.#container.innerHTML = await see.render(idPoll);
        }

        // the reject button of the structin section section
        if(target.classList.contains("reject-btn")){
          const id = target.closest("li").id;
          const idPost = target.closest(".post-card").id; 
          await this.setCandidateState(id,idPost, -1, ()=>{
            target.closest("li").remove();
          });
        }

        // add new post
        if(target.classList.contains("add-post-btn")){
          const idPoll = target.closest(".scrutin-container").id;

          this.#openDialog(seeForm.renderFormAddPost(idPoll));
        }

        if(target.classList.contains("add-candidate-btn")){
          const idPoll = target.closest(".scrutin-container").id;
          const idPost = target.closest(".post-card").id;
          this.#openDialog( await seeForm.renderFormAddCandidate(idPoll, idPost));
        }

        if(target.classList.contains("btn-edit")){
          const cardPoll = target.closest(".election-card"); 

          const id = cardPoll.id; 
          const rep = api.get(`poll/${id}`);

          rep.then((data)=>{
            if(data.message){
              const oldTitle = data.message.title; 
              const oldDescription= data.message.description;
              // const oldStartDate= data.messgage.dateStart;
              // const oldEndDate= data.messgage.dateEnd;
              const dates= data.message;
              this.#openDialog(this.formUpdatePoll(id, oldTitle,oldDescription, dates.dateStart.date, dates.dateEnd.date))
            }
          });
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
        
        await api.post("poll", formData).then((data)=>{
          console.log(data)
        }); 

      });
                        
      formContainer.appendChild(form); 

      return formContainer;
    }

    formUpdatePoll(idPoll,oldTitle, oldDescription, oldStartDate, oldEndDate){
      const formContainer = document.createElement("div");

      formContainer.classList.add("election-form-section");
      formContainer.innerHTML = `<h2 class="text-title">Modification structin</h2>`;

      const form = document.createElement("form"); 

      form.classList.add("election-form");

      form.innerHTML = `<input type="text" class="input" name="title" placeholder="Titre du scrutin" value="${oldTitle}" required />
                            <textarea class="textarea" name="description" placeholder="Description du scrutin" rows="3">${oldDescription}</textarea>

                            <label class="label" for="start-date">Date de début</label>
                            <input type="datetime-local" value="${api.datePhpToDateJs(oldStartDate)}" name="date_start" id="start-date" class="input" required />

                            <label class="label" for="end-date">Date de fin</label>
                            <input type="datetime-local" value="${api.datePhpToDateJs(oldEndDate)}" name="date_end" id="end-date" class="input" required />

                            <button type="submit" class="btn btn-primary">Créer le scrutin</button>
                        `; 

      // submit listener

      form.addEventListener("submit", async(e)=>{
        e.preventDefault()
        console.log(form)
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
        console.log(data);
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


      const model = `
          <div class="election-card" id=${id}>
            <h3 class="text-bold">${title}</h3>
            <p>Du ${dateStart.trim().split(" ")[0]} au ${dateEnd.trim().split(" ")[0]}</p>

            <div class="election-actions">
              <button class="btn see btn-success">Voir</button>
            <!--<button class="btn btn-edit">Modifier</button>
              <button class="btn btn-delete">Supprimer</button>
              <button class="btn btn-success">Ajouter un candidat</button> -->
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
