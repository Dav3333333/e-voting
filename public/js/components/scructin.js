import { api } from "../../../admin/js/libs/api.js"; 

function ouvrirVote(voteId, mode) {
    // c pour cardMode
    // ulc pour user-link-cardmode
    
    window.location.href = `vote.php?id=${voteId}${`&c=${mode=="cardmode"?true:false}`}${`&ulc=${mode == "user-link-cardmode"?true:false}`}`;
}

document.addEventListener("DOMContentLoaded", async () => {
    const votes = await api.get("polls/ordered").then((data)=>{return data});
    const container = document.getElementById("votesContainer");

    console.log(votes);

    if (!votes || votes.length === 0) {
        container.innerHTML = "<p>Aucun scrutin disponible.</p>";
        return;
    }

    votes.message.forEach(vote => {
        const div = document.createElement("div");
        const status = vote.status == "inactif" ? "en attente" 
                     : vote.status == "passed" ? "cloture" 
                     : vote.status == "in_progress" ? "ouvert" 
                     : vote.status;

        const mode = vote.mode;
        
        div.className = "card";
        div.innerHTML = `
            <h3>${vote.title}</h3>
            <p>État : <span class="etat ${status}">${status}</span></p>
            ${status === "ouvert" ? `<button class="btn-ouvrir" data-id="${vote.id}" mode="${mode}">Accéder</button> 
                                    <button class="btn-result" data-id="${vote.id}">Voir resultat</button>
            ` : ""}
        `;
        container.appendChild(div);
    });

    document.querySelectorAll(".btn-ouvrir").forEach(btn => {
        btn.addEventListener("click", (e) => {
            const id = e.target.getAttribute("data-id");
            const m = e.target.getAttribute("mode");
            ouvrirVote(id,m);
        });
    });

    document.querySelectorAll(".btn-result").forEach(btn => {
        btn.addEventListener("click", (e) => {
            const id = e.target.getAttribute("data-id");
            window.location.href = `resultat.php?id=${id}`;
        });
    });
});
