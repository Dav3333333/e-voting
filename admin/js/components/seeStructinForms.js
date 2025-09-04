import { api } from "../libs/api.js";
import { func } from "./commonFunc.js";


class SeeForms{

    constructor(){

    }

    renderFormAddPost(idPost){
        const formContainer = document.createElement("div");

        formContainer.classList.add("election-form-section");
        formContainer.innerHTML = `<h2 class="text-title text-thin">Créer un nouveau Poste</h2>`;

        const form = document.createElement("form"); 

        form.classList.add("election-form");

        form.innerHTML = `<input type="text" class="input title" name="title" placeholder="Titre du Poste" required />
                        <input type="hidden" name="idPoll" value="${idPost}"/>
                        <span class="hidden btn-success show-log text-center"></span>
                        <button type="submit" class="btn btn-primary">Créer le poste</button>
                        `; 

        // submit listener

        form.addEventListener("submit", async(e)=>{
        e.preventDefault(); 
        
        let formData = new FormData(form); 
        api.post("post/add", formData).then((data)=>{
            if(data.status && data.status == "succes"){
                form.querySelector(".show-log").innerHTML = `Ajout de ${form.querySelector(".title").value} reussi`
                form.querySelector(".show-log").classList.remove("hidden");
                form.querySelector(`.title`).value = ""
            }
        });

        });
                        
        formContainer.appendChild(form); 
        return formContainer;
    }

    async #displayedCand(data){
        const c = await ((data)=>{
                const div = document.createElement("div");
                div.classList.add("users");

                console.log(data);

                if(data.status && data.status == "fail"){
                    div.innerHTML = `<div class="text-center">Erreur</div>`;
                }else{
                    data.message.forEach(user => {
                        const model = `
                            <div class="user-item" id="${user.id}">
                                <!--<img src="https://picsum.photos/200" alt="User Image" class="user-avatar">-->
                                <span class="user-name">${user.name}</span>
                                <button class="add-btn">Ajouter</button>
                            </div>
                        `; 
        
                        div.insertAdjacentHTML("beforeend", model);
                    });
                }
                return div.innerHTML;
            });
        return c(data);
    }

    async renderFormAddCandidate(idPoll, idPost){
        const serverData = await api.get(`poll/avaible/candidate/${idPoll}`).then((data)=>{
            return data;
        });

        let candData = serverData;

        const formContainer = document.createElement("div");

        formContainer.classList.add("election-form-section");
        formContainer.innerHTML =`
                        <div class="user-list-container" idPost="${idPost}" idPoll="${idPoll}">
                            <h2 class="title">Ajouter un candidat</h2>
                            <input type="search" id="search-users" class="user-search" placeholder="Rechercher un utilisateur..." />
                            <div id="list-users">
                                ${await this.#displayedCand(candData)}
                            </div>
                        </div>`;
        
        const searchBtn =  formContainer.querySelector("input");
        searchBtn.addEventListener("keydown", async (e)=>{
            const lowerSearch = searchBtn.value.toLowerCase();

            console.log(lowerSearch, lowerSearch.trim(" ").length);

            let rep = candData.message.filter(user =>
                user.name.toLowerCase().includes(lowerSearch)
            );

            candData = {"message":rep}

            formContainer.querySelector("#list-users").innerHTML = await this.#displayedCand(candData);
        });

        formContainer.addEventListener("click", async(e)=>{
            if(e.target.classList.contains("add-btn")){
                const idUser = e.target.closest(".user-item").id;
                const idPoll = e.target.closest(".user-list-container").getAttribute("idPoll");
                const idPost = e.target.closest(".user-list-container").getAttribute("idPost");

                // check is all is number 
                if(!func.isNumber(idUser) || !func.isNumber(idPoll) || !func.isNumber(idPost)){
                    console.log(idUser, idPoll, idPost);
                    console.log("error id not valid");
                    return;
                }

                let formData = new FormData();
                formData.append("user_id", parseInt(idUser));
                formData.append("post_id", parseInt(idPost));
                formData.append("poll_id", parseInt(idPoll));

                await api.post("candidate/add", formData).then(async(data)=>{
                    if(data.status && data.status == "success"){
                        e.target.closest(".user-item").remove();
                        candData.message = candData.message.filter(user => user.id != idUser);

                    }else{
                        console.log("error", data);
                    }
                });

            }
        });

        return formContainer;
    
    }

}

export const seeForm = new SeeForms();

/**
 * 
 * for the form add poll we gonna have these entries: 
 *  - title
 *  - date start 
 *  - date end 
 *  - description
 */
