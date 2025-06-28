import { login } from "./components/login.js";
import { statistics } from "./components/statistics.js";
import { users } from "./components/users.js";
import { scrutin } from "./components/scrutin.js";

class Admin {

    #aside
    #content

    constructor() {
        this.#aside = document.querySelector("#sidebar");
        this.#content = document.querySelector("main")
      const model = `<section class="election-container"> </section>`;

        // charging stats data into main
        
        // handling clic events
        this.handleClickEvents();
        // login
    }
    
    async handleClickEvents() {
        const page = await statistics.render()
        this.#content.appendChild(page);
        this.#aside.addEventListener("click", async (e)=>{
            if(e.target.classList.contains("menu-link")){

                const link = e.target;

                this.#content.innerHTML = " ";

                const prev = link.closest("nav").querySelector(".active")

                prev.classList.remove("active")

                link.classList.add("active")

                console.log(link.classList)
                
                if(link.id == "statistics"){
                    const page = await statistics.render();
                    this.#content.appendChild(page)
                }

                if(link.id == "poll"){
                    this.#content.appendChild(scrutin.render())
                }

                if(link.id == "setting"){
                    this.#content.innerHTML = "settings"
                }

                if(link.id == "users"){
                    this.#content.appendChild(users.render())
                }

                if(link.id == "logout"){
                    this.#content.innerHTML = "logout"
                }
            }
            // console.log(e.target.classList.contain);
        })
    }
}

new Admin();

