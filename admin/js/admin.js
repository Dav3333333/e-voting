import { login } from "./components/login.js";
import { statistics } from "./components/statistics.js";
import { users } from "./components/users.js";
import { scrutin } from "./components/scrutin.js";

class Admin {

    #aside
    #content

    constructor() {
        this.#aside = document.querySelector("#sidebar");
        this.#content = document.querySelector("main div")
      const model = `<section class="election-container"> </section>`;

        // charging stats data into main
        
        // handling clic events
        this.handleClickEvents();
        
        this.handleDialogClickEvents();
    }
    
    async handleClickEvents() {
        const page = await statistics.render()
        this.#content.appendChild(page);
        this.#aside.addEventListener("click", async (e)=>{
            if(e.target.classList.contains("menu-link")){

                let page = null;

                const link = e.target;

                this.#content.innerHTML = " ";

                const prev = link.closest("nav").querySelector(".active")

                prev.classList.remove("active")

                link.classList.add("active")

                
                if(link.id == "statistics"){
                    page = await statistics.render();
                }
                
                if(link.id == "poll"){
                    page = await scrutin.render()
                }
                
                if(link.id == "setting"){
                    page =  this.#content.innerHTML = "settings"
                }
                
                if(link.id == "users"){
                    page = await users.render()
                }
                
                if(link.id == "logout"){
                    page = awaitthis.#content.innerHTML = "logout"
                }

                this.#content.appendChild(page)
            }
            // console.log(e.target.classList.contain);
        })
    }

    // for all the dialog handle the close fonction
    handleDialogClickEvents(){
        const dialog = document.querySelector("dialog"); 
        dialog.addEventListener("click", (e)=>{
            if(e.target.id == "close-dialog" || e.target.id == "cancel"){
                dialog.close();
            }
        })
    }
}

new Admin();

