import { api } from "../libs/api.js";
import { func } from "./commonFunc.js";


class See{

    #container;

    constructor(){
    }

    #handleClickEvents(){
        this.#container.addEventListener("click", async (e)=>{
            const target = e.target;

            if(target.classList.contains("back-btn")){
                window.location.hash = "poll"
            }

            if(target.id == "refresh-see-pool"){
                const idPoll = target.closest(".scrutin-container").id;
                this.#container.innerHTML = await this.render(idPoll);
            }
        });
    }

    #renderCandidates(candidates){
        const cont = document.createElement("ul"); 
        cont.classList.add("candidate-list");
        
        if(candidates){
            candidates.forEach(cand => {
                const model = `<li class="candidate-component" id="${cand.user_id}">
                ${cand.name}
                <div class="candidate-actions">
                  <button class="confirm-btn">✅ Confirmer</button>
                  <button class="reject-btn">❌ Rejeter</button>
                </div>
              </li>`;

              cont.insertAdjacentHTML("beforeend", model);
            });


        }
        return cont.innerHTML
    }

    #renderPostPollSection(post_cand_info){

        const container = document.createElement("div");
        post_cand_info.forEach(post => {
            const model = `
                        <div class="post-card" id="${post.id}">
                            <div class="post-title">
                                <strong>${post.postName}</strong>
                                <div class="post-actions">
                                <button class="edit-btn">✏️</button>
                                <button class="delete-btn">❌</button>
                                </div>
                            </div>
                        
                            <div class="candidates-section">
                                <h4>Candidats</h4>

                                <!-- data for candidates -->
                                ${this.#renderCandidates(post.candidateList)}

                            <button class="add-candidate-btn">+ Ajouter un candidat</button>
                            </div>
                        </div>
            
            `;
            container.insertAdjacentHTML("beforeend", model);
        });
      return container.innerHTML;
    }

    async #renderPoll(idPoll){
        const poll = await api.get(`poll/${idPoll}`).then((data)=>{
            return data
        });

        const title = poll.message.title; 
        const descrip = poll.message.description;
        const dateStart = func.reformatPhpDate(poll.message.dateStart.date).trim(" ");
        const dateEnd = func.reformatPhpDate(poll.message.dateEnd.date).trim(" ");
        const status = (poll.message.status == "passed")? "Temps Expirer" : "Jour J - "+poll.message.dayBefore.days;

        let postData = (poll.message.posts.length > 0)? this.#renderPostPollSection(poll.message.posts) : `<div class="text-center">Pas de postes pour ce scrutin</div>` ;

        const model = `<div class="scrutin-container" id=${idPoll}>
        <h2 class="scrutin-title">
            <button class="back-btn">← Retour</button>
            <span>${title}</span>
            <button id="refresh-see-pool">🔄️</button>
        </h2>
        <p class="scrutin-description">${descrip}</p>
      
        <div class="scrutin-info">
          <span class="text-thin text-medium--size"><strong>Date debut :</strong><span class="text-small"> ${dateStart} </span></span>
          <span class="text-thin text-medium--size"><strong>Date fin :</strong><span class="text-small"> ${dateEnd} </span></span>
          <span class="text-thin text-medium--size">${status}</span>
        </div>

        <div class="posts-section">
            <div class="post-header">
            <h3>Postes à pourvoir</h3>
            <button class="add-post-btn">+ Ajouter un poste</button>
        </div>
        
        <!-- data for post -->
        ${postData}

        </div>
        <div class="structin-actions">
            <button class="btn btn-success" id="start-poll">Démarrer le scrutin</button>
            <button class="btn btn-danger" id="end-poll">Clore le scrutin</button>
        </div>
      </div>`;
      return model;
    }

    #renderUnfoundPoll(){
        const model = `<div>Post not found</div>`;
    }

    async #isPollExist(idPoll){
        if (func.isNumber(idPoll)){
            const rep = await api.get(`poll/${idPoll}`).then((data)=>{
                if(data.message != null){
                    return true;
                }else{
                    return false;
                }
            }); 
            return rep;
        }else{
            return "bad number"
        }
    }

    async render(idPoll){
        const r = await this.#isPollExist(idPoll);
        if(r){
            return this.#renderPoll(idPoll);
        }else{
            return this.#renderUnfoundPoll();
        }
    }
}

export const see = new See();

