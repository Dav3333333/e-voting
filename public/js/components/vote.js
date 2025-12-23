import { api } from "../../../admin/js/libs/api.js";

class VoteController {
    constructor() {
        this.urlParams = new URLSearchParams(window.location.search);
        this.voteId = this.urlParams.get("id");
        this.mode = this.urlParams.get("c") === 'true'; // Convertit en booléen
        this.scrutin = null;
        this.container = document.getElementById("candidatsContainer");
        this.currentCardCode = null;
    }

    async init() {
        try {
            this.scrutin = await api.get(`poll/${this.voteId}`).then((data) => data);

            document.querySelector('#title').innerText = this.scrutin.message.title
            
            if (!this.scrutin || !this.scrutin.message || !this.scrutin.message.posts) {
                this.container.innerHTML = "<p>Impossible de charger ce scrutin.</p>";
                return;
            }

            if (this.mode) {
                this.renderCardForm();
            } else {
                this.renderVoteForm();
            }

        } catch (err) {
            console.error("Erreur API :", err);
            this.container.innerHTML = "<p>Erreur de chargement des candidats.</p>";
        }
    }

    renderCardForm() {
        this.container.innerHTML = `
            <div class="card-form">
                <h3>Vote par Carte</h3>
                <p>Veuillez entrer le code de votre carte pour accéder au vote</p>
                <form id="cardForm">
                    <div class="error-message hidden">message error</div>
                    <input type="text" id="cardCode" placeholder="Code de la carte" required>
                    <button type="submit">Valider</button>
                </form>
            </div>
        `;

        if(this.container.classList.contains("card-list")){
            this.container.classList.remove("card-list")
        }

        document.getElementById("cardForm").addEventListener("submit", (e) => {
            e.preventDefault();
            this.validateCard();
        });
    }

    async validateCard() {
        const cardCode = document.getElementById("cardCode").value;
        
        const message_error = document.querySelector("#cardForm .error-message")

        try {
            // Ici, vous devriez valider le code carte avec votre API
            const formData = new FormData();
            formData.append("card_code",cardCode );
            formData.append("poll_id", this.voteId);

            const validation = await api.post("vote/validate/card", formData);

            if (validation.status === "success") {
                this.currentCardCode = cardCode
                this.renderVoteForm();
            } else {
                message_error.innerText = "Code Carte Invalide"
                message_error.classList.remove("hidden")
                // alert("Code carte invalide");
                console.error("Validation carte échouée:", validation);
            }
        } catch (err) {
            message_error.innerText = "Erreur lors de la validation"
            message_error.classList.remove("hidden")
            console.error("Erreur validation carte:", err);
            alert("Erreur lors de la validation de la carte");
        }
    }

    async renderVoteForm() {
        this.container.innerHTML = '';
        document.getElementById("title").innerText = `Scrutin: ${this.scrutin.message.title}`;

        const post = await api.get(`post/getavailablepostcard/poll/${this.voteId}/card/${this.currentCardCode}`);

        console.log(this.voteId, this.currentCardCode);

        if(post.status != "success"){
            return;
        }

        const voteContainer = document.createElement("div");
        // post id
        voteContainer.id = post.post.id
        voteContainer.classList.add("card-list");

        post.post.candidateList.forEach(candidate => {
            let endpoint = `../api/user/image/${candidate.id}`;

            console.log(candidate);

            const div = document.createElement("div");
            // user id
            div.id = candidate.candId
            div.className = "card";
            div.innerHTML = `
                            <img src='${endpoint}' alt="User-image" class="card-image">
                            <h3 class="card-name">${candidate.name}</h3>
                            `;
            voteContainer.appendChild(div);

            this.container.innerHTML = `<h1>${post.post.postName}</h1>`;
            this.container.appendChild(voteContainer);
        });

        this.setupVoteHandler();
    }

    setupVoteHandler() {
        const candListCont = document.querySelector(".card-list");
        candListCont.addEventListener("click", async (e)=>{
            e.preventDefault();
            const target = e.target;

            const idPost = candListCont.id;
            let idCandidate = null; 
            if(target.classList.contains("card-image") || target.classList.contains("card-name")){
                idCandidate = target.closest(".card").id;
            }

            if(target.classList.contains("card")){
                idCandidate = target.id;
            }

            console.log(idCandidate, idPost);

            try {
                let response;
                if(this.mode){
                    if(this.currentCardCode != null){
                        // check the mode and pass to the vote
                        const formData = new FormData();
                        formData.append("card_code", this.currentCardCode); 
                        formData.append("poll_id", parseInt(this.voteId));
                        formData.append("post_id", parseInt(idPost));
                        formData.append("candidate_id", parseInt(idCandidate));
                        response = await api.post("vote/cardmode", formData);

                        this.goNextPoll();
                    }else{
                        console.log("le cardcode n'a pas ete definie");
                    }
                }else{
                    // check the mode and pass to the vote
                    const formData = new FormData();
                    formData.append("user_id", 2); 
                    formData.append("poll_id", parseInt(this.voteId));
                    formData.append("post_id", parseInt(idPost));
                    formData.append("candidate_id", parseInt(idCandidate));
                    response = await api.post("vote", formData);
                }

                if (response && response.errors) {
                    alert("Erreur(s) : " + response.errors.join(", "));
                    return;
                }

                if (response && response.status === "success") {
                    alert(`${response.message}`);
                    await this.goNextPoll();
                    console.log(response)
                } else {
                    console.log(response)
                    alert(`${response.message} error here`);
                }
            } catch (err) {
                console.error(err);
                alert("Erreur lors du vote");
            }
        });
    }

    async goNextPoll(){
        const rep = await api.get(`post/getavailablepostcard/poll/${this.voteId}/card/${this.currentCardCode}`);
        if(rep.status == "success"){
            this.renderVoteForm();
        }else{
            this.renderCardForm();
        }
    }

}

// Utilisation
document.addEventListener("DOMContentLoaded", () => {
    const voteController = new VoteController();
    voteController.init();
});