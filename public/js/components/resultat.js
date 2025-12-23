import { api } from "../../../admin/js/libs/api.js";

    const urlParams = new URLSearchParams(window.location.search);
    const voteId = urlParams.get("id");

document.addEventListener("DOMContentLoaded", async () => {
    setInterval(async() => {
        try {
            const resultats = await api.get(`poll/results/${voteId}`).then(data => data);
            const container = document.getElementById("resultsContainer");
    
    
            // if (!resultats || !resultats.message || !resultats.message.posts) {
            //     container.innerHTML = "<p>Aucun résultat disponible.</p>";
            //     return;
            // }
    
            if(resultats.status == 'fail'){
                container.innerHTML = `<p>${resultats.message}.</p>`;
                return;
            }
    
            document.getElementById("post-title").textContent = resultats.poll_title || "";
    
            if (!resultats || !resultats.message || resultats.message.length === 0) {
                container.innerHTML = "<p>Aucun résultat disponible.</p>";
                return;
            }
            
            // empty container
            container.innerHTML = "";

            resultats.message.forEach(poste => {
                const div = document.createElement("div");
                div.className = "card";
                div.innerHTML = `<h3>${poste.post_name}</h3>`;
    
                if (poste.candidates && poste.candidates.length > 0) {
                let table = `
                    <table class="result-table">
                    <thead>
                        <tr>
                        <th>Candidat</th>
                        <th>Votes</th>
                        <th>%</th>
                        </tr>
                    </thead>
                    <tbody>
                `;
    
                const totalVotes = poste.candidates.reduce((sum, c) => sum + c.vote_count, 0);
    
                poste.candidates.forEach(c => {
                    const pourcentage = totalVotes > 0 ? ((c.vote_count / totalVotes) * 100).toFixed(1) : 0;
                    table += `
                    <tr>
                        <td>${c.candidate_name}</td>
                        <td>${c.vote_count}</td>
                        <td>${pourcentage}%</td>
                    </tr>
                    `;
                });
    
                table += "</tbody></table>";
                div.insertAdjacentHTML("beforeend",table);
                } else {
                div.innerHTML += "<p>Aucun candidat pour ce poste.</p>";
                }
    
                container.appendChild(div);
            });
        } catch (err) {
            console.error("Erreur lors du chargement des résultats :", err);
            document.getElementById("resultsContainer").innerHTML = "<p>Erreur lors du chargement.</p>";
        }
    }, 5000);
});