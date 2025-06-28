import { api } from "../libs/api.js";

class Statistics{

    #container;

    constructor() {
        this.#container = document.createElement("div")
        this.#container.classList.add("cards")
        
        this.#getstats();

        this.#handleClickEvents();
    }

    async #getstats(){
      try {
            const data = await api.get("statisctis"); // Attention à l'orthographe ici !
            return data;
        } catch (err) {
            console.error("Erreur lors de la récupération des statistiques :", err);
            return null;
        }
    }

    #handleClickEvents(){
        this.#container.addEventListener("click", (e)=>{
            console.log(e);
        })
    }

    async render(){
      const data = await this.#getstats();
        console.log(data);

        if (!data) {
            this.#container.innerHTML = "<p>Erreur lors du chargement des statistiques.</p>";
            return this.#container;
        }

        const stats = [
            { label: "Scrutins totaux", value: data.message.poll.totalPoll },
            { label: "Scrutins passés", value: data.message.poll.passedPoll },
            { label: "Scrutins actifs", value: data.message.poll.activePoll },
            { label: "Scrutins futurs", value: data.message.poll.futurePoll },
            { label: "Postes", value: data.message.posts.posts },
            { label: "Utilisateurs", value: data.message.users.users },
            { label: "Utilisateurs actifs", value: data.message.users.activeUsers },
            { label: "Utilisateurs inactifs", value: data.message.users.inactiveusers }
        ];

        this.#container.innerHTML = ""; 

        stats.forEach(stat => {
            const model = `
              <div class="card">
                <h3 class="text-title">${stat.label}</h3>
                <p class="text-bold">${stat.value}</p>
              </div>`;
            this.#container.insertAdjacentHTML("beforeend", model); 
        });

        return this.#container;
    }
}

export const statistics = new Statistics();


// html model composition 

// <!-- cards               -->
//     <div class="cards">
//       <div class="card">
//         <h3 class="text-title">Nombre de votants</h3>
//         <p class="text-bold">324</p>
//       </div>
//       <div class="card">
//         <h3 class="text-title">Candidats</h3>
//         <p class="text-bold">8</p>
//       </div>
//       <div class="card">
//         <h3 class="text-title">Taux de participation</h3>
//         <p class="text-bold">78%</p>
//       </div>
//     </div>