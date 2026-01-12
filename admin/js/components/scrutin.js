import { api } from "../libs/api.js";
import { see } from "./seeStructin.js";
import { seeForm } from "./seeStructinForms.js";
import { func } from "./commonFunc.js";
import { pdfPrint } from "../libs/printTask.js";
import { modal_ops } from "./modal_ops.js";

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
          const idPost = target.closest(".election-card").id;
          this.#container.innerHTML = await see.render(idPost);
        }

        // start poll section
        if(target.id == "start-poll"){
          const idPost = target.closest(".scrutin-container").id;
          if(!func.isNumber(idPost)) {return};

          const dataStart = new FormData(); 
          dataStart.append("idPoll", parseInt(idPost));

          if(await modal_ops.showConfirm("Demmarer Scrutin", `Voulez vous Demarer le scrutin ${idPost}? `) === true){
            api.post("poll/start",dataStart).then((data)=>{
              console.log(data);
              if(data.status == "success"){
                target.id = "end-poll";
                target.classList.remove("btn-success");
                target.classList.add("btn-danger")
                target.textContent = "CLOTURER LE STRUTIN"
              }else{
                alert(data.message);
              }
            });
          }
        }

        // end poll section button 
        if(target.id == "end-poll"){
          const idPost = target.closest(".scrutin-container").id; 
          console.log(idPost);
          if(!func.isNumber(idPost)){return}; 

          const dataClose = new FormData();
          dataClose.append("idPoll", parseInt(idPost));
          await api.post("poll/stop", dataClose).then((data)=>{
            console.log(data);
          });
        }

        // the back button of the structin section
        if(target.classList.contains("back-btn")){
            this.render();
        }

        // the refresh button of the structin section
        if(target.id == "refresh-see-pool"){
            const idPoll = target.closest(".scrutin-container").id;
            this.#container.innerHTML = await see.render(idPoll);
        }

        // delet poll btn
        if(target.id == "delete-card"){
          const idPost = target.closest(".scrutin-container").id; 
          if(await modal_ops.showConfirm("Suppression Scrutin", "Cette actions est irreversible voulez-vous supprimer definitivement? ") == true){
            await api.delete(`poll/${idPost}`).then((data)=>{
              if(data.status == "success"){
                this.render();
              }
              // re start 
            });
          }else{
            modal_ops.showFailMessage("Suppression", "Suppression annuller");
          }
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

        // card-mode btn event listerner
        if(target.classList.contains("card-mode")){
          const btn_cliked = target;
          const idPoll = target.closest(".scrutin-container").id;
          if(btn_cliked.id == "access-demand"){
            // call to the api to ask the demande of card
            this.#openDialog(this.formCardDemande(idPoll, "cardmode"));
          }

          if(btn_cliked.id == "access-demand-usermode"){
            // call to the api to ask the demande of user-link-mode
            this.#openDialog(this.formCardDemande(idPoll, "user-link-cardmode"));
          }

          if(btn_cliked.id == "card-print"){
            // call the api to the print the cards
            pdfPrint.imprimerPDF(idPoll);
          }

          if(btn_cliked.id == "card-download"){
            // call the api to download the card
            pdfPrint.telechargerPDF(idPoll);
          }
        }

      });
    }

    formCardDemande(idPoll, mode){
      const formContainer = document.createElement("div");

      formContainer.classList.add("election-form-section");
      formContainer.innerHTML = `<h2 class="text-title">Créer un nouveau scrutin</h2>`;

      const form = document.createElement("form"); 
      form.classList.add("election-form");
      form.id = idPoll;

      form.innerHTML = `<input type="number" id="card_number" class="input" name="card_number" placeholder="Nombre des cards" required />
                        <button type="submit" class="btn btn-primary">Generer les Cards</button>
                        `;

      form.addEventListener("submit", async (e)=>{
        e.preventDefault();
          const idPoll = form.id; 
          const Formdata = new FormData(form); 
          Formdata.append("id_poll", idPoll);
          Formdata.append("mode", mode);

          form.insertAdjacentHTML("beforeend", `<p class="text-info">La demande est en cours de traitement...</p>`);

          await api.post("poll/card-mode/accessdemand",Formdata).then((data)=>{
            console.log(data);
            if(data.status == "success"){
              form.querySelector("p").textContent = "La demande a été bien prise en compte";
              form.reset();
            }
            if(data.status == "fail"){
              form.querySelector("p").textContent = "Erreur: "+data.message;
            }
          });

          // pdfPrint.imprimerPDF(idPoll);
          // Méthode simple (recommandée)
          // Si vous avez des problèmes, utilisez le debug d'abord

          async function imprimerAvecDebug(idPoll) {
              const result = await pdfPrint.debugPDF(idPoll);
              if (result.error) {
                  pdfPrint.showError(result.error);
              } else {
                  await pdfPrint.imprimerPDFManuel(idPoll);
              }
          }

          await imprimerAvecDebug(idPoll);

          // refresh the html
          this.#dialog.close()
          this.#container.innerHTML = await see.render(idPoll);

      });

      formContainer.appendChild(form);

      return formContainer;
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

                        <div class="error-container" display="none"> 
                          <span class="text-error" id="error-message" ></span>
                        </div>
                        `; 

      // submit listener

      form.addEventListener("submit", async(e)=>{
        e.preventDefault(); 
        const sendData =new FormData(form);
        sendData.append("user_id", 1); // to be replaced by the connected user id
        
        await api.post("poll", sendData).then((data)=>{
          console.log(data);
          if(!data || !data.status) return;


          console.log(data.status)

          if(data.status == "success"){
            this.#dialog.close();
            modal_ops.showSuccesMessage("Creation Scrutin","Scrutin Creer Avec Success");

            form.reset();
            this.render();
            console.log(data)
            // this.#dialog.close();
          }
          if(data.status == "fail"){
            form.querySelector("#error-message").textContent = data.message;
            form.querySelector("#error-message").style.display = "block";
          }
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
        // console.log(data);
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
