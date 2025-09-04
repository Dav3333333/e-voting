import { api } from "../libs/api.js";

class Login {

    #container;

    constructor() {
        this.#container = document.createElement("div");

    }

    #handleClick(){
        this.#container.addEventListener("click", (e)=> {
            console.log(e);
        });
    }

    async login(email, password){
        const rep = await fetch("../../api/"); 
    }

    async getPolls() {
        // try {
        //     const res = await fetch('../api/polls');
        //     const data = await res.json();
        //     console.log(data);
        // } catch (err) {
        //     console.error('Erreur:', err);
        // }

        const a = await api.get("polls");
        console.log(a);

    }

    render(){
        const model = `<section class="login-box">

      <!-- login form -->
      <h2>Connexion Admin</h2>
      <form>
        <label for="email">Email</label>
        <input type="email" id="email" placeholder="Email admin" required>

        <label for="password">Mot de passe</label>
        <input type="password" id="password" placeholder="Mot de passe" required>

        <button type="submit">Se connecter</button>
      </form>
    </section>`;

    this.#container.innerHTML = model;

    return this.#container;

    }
}


export const login = new Login();



{/* <section class="login-box">

    <!-- login form -->
    <h2>Connexion Admin</h2>
    <form>
    <label for="email">Email</label>
    <input type="email" id="email" placeholder="Email admin" required>

    <label for="password">Mot de passe</label>
    <input type="password" id="password" placeholder="Mot de passe" required>

    <button type="submit">Se connecter</button>
    </form>
</section> */}