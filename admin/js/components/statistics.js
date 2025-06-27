import { api } from "../libs/api.js";

class Statistics{

    #container;

    constructor() {
        this.#container = document.createElement("div")
        this.#container.classList.add("cards")
        
        this.getSome();

        this.#handleClickEvents();


    }

    getSome(){
      const a = api.get("users/all");
      a.then((data)=>{
        console.log(data)
      })
    }

    #handleClickEvents(){
        this.#container.addEventListener("click", (e)=>{
            console.log(e);
        })
    }

    render(){
        const model = `<div class="card">
        <h3 class="text-title">Nombre de votants</h3>
        <p class="text-bold">324</p>
      </div>
      <div class="card">
        <h3 class="text-title">Candidats</h3>
        <p class="text-bold">8</p>
      </div>
      <div class="card">
        <h3 class="text-title">Taux de participation</h3>
        <p class="text-bold">78%</p>
      </div>`

      this.#container.innerHTML = model;

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