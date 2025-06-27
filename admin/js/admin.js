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
        this.handleClickEvents();
        login
    }

    handleClickEvents() {
        this.#aside.addEventListener("click", (e)=>{
            if(e.target.classList.contains("menu-link")){

                const link = e.target;

                this.#content.innerHTML = " ";
                
                if(link.id == "statistics"){
                    this.#content.appendChild(statistics.render())
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

